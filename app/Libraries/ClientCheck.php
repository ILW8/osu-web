<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

declare(strict_types=1);

namespace App\Libraries;

use App\Exceptions\ClientCheckParseTokenException;
use App\Models\Build;
use Illuminate\Http\Request;

class ClientCheck
{
    public static function parseToken(Request $request): array
    {
        $token = $request->header('x-token');
        $assertValid = $GLOBALS['cfg']['osu']['client']['check_version'];
        $ret = [
            'buildId' => $GLOBALS['cfg']['osu']['client']['default_build_id'],
            'token' => null,
        ];

        try {
            if ($token === null) {
                throw new ClientCheckParseTokenException('missing token header');
            }

            $input = static::splitToken($token);

            $build = Build::firstWhere([
                'hash' => $input['clientHash'],
                'allow_ranking' => true,
            ]);

            if ($build === null) {
                throw new ClientCheckParseTokenException('invalid client hash');
            }

            $ret['buildId'] = $build->getKey();

            $computed = hash_hmac(
                'sha1',
                $input['clientData'],
                static::getKey($build),
                true,
            );

            if (!hash_equals($computed, $input['expected'])) {
                throw new ClientCheckParseTokenException('invalid verification hash');
            }

            $now = time();
            static $maxTime = 15 * 60;
            if (abs($now - $input['clientTime']) > $maxTime) {
                throw new ClientCheckParseTokenException('expired token');
            }

            $ret['token'] = $token;
        } catch (ClientCheckParseTokenException $e) {
            abort_if($assertValid, 422, $e->getMessage());
        }

        return $ret;
    }

    public static function queueToken(?array $tokenData, int $scoreId): void
    {
        if ($tokenData['token'] === null) {
            return;
        }

        \LaravelRedis::lpush($GLOBALS['cfg']['osu']['client']['token_queue'], json_encode([
            'id' => $scoreId,
            'token' => $tokenData['token'],
        ]));
    }

    private static function getKey(Build $build): string
    {
        return $GLOBALS['cfg']['osu']['client']['token_keys'][$build->platform()]
            ?? $GLOBALS['cfg']['osu']['client']['token_keys']['default']
            ?? '';
    }

    private static function splitToken(string $token): array
    {
        $data = substr($token, -82);

        return [
            'clientData' => substr($data, 0, 40),
            'clientHash' => hex2bin(substr($data, 0, 32)),
            'clientTime' => unpack("V", hex2bin(substr($data, 32, 8)))[1],
            'expected' => hex2bin(substr($data, 40, 40)),
            'version' => substr($data, 80, 2),
        ];
    }
}
