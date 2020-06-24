<?php

// Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
// See the LICENCE file in the repository root for full licence text.

namespace Tests;

use App\Http\Middleware\RequireScopes;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Route;
use Laravel\Passport\Exceptions\MissingScopeException;
use Request;

class RequireScopesTest extends TestCase
{
    protected $next;
    protected $request;
    protected $user;

    /**
     * @dataProvider clientCredentialsDataProvider
     */
    public function testClientCredentials($scopes, $expectedException)
    {
        $this->setRequest(['public']);
        $this->setUser(null, $scopes);

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        app(RequireScopes::class)->handle($this->request, $this->next);
        $this->assertTrue(oauth_token()->isClientCredentials());
    }

    /**
     * @dataProvider clientCredentialsWhenAllScopeRequiredDataProvider
     */
    public function testClientCredentialsWhenAllScopeRequired($scopes, $expectedException)
    {
        $this->setRequest();
        $this->setUser(null, $scopes);

        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }

        app(RequireScopes::class)->handle($this->request, $this->next);
        $this->assertTrue(oauth_token()->isClientCredentials());
    }

    public function testNoScopes()
    {
        $userScopes = [];

        $this->setRequest();
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, $this->next);
    }

    public function testAllScopes()
    {
        $userScopes = ['*'];

        $this->setRequest();
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, $this->next);
        $this->assertTrue(true);
    }

    public function testHasTheRequiredScope()
    {
        $userScopes = ['identify'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        $this->assertTrue(true);
    }

    public function testDoesNotHaveTheRequiredScope()
    {
        $userScopes = ['somethingelse'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
    }

    public function testRequiresSpecificScopeAndAllScopeGiven()
    {
        $userScopes = ['*'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        $this->assertTrue(true);
    }

    public function testRequiresSpecificScopeAndNoScopeGiven()
    {
        $userScopes = [];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
    }

    public function testRequiresSpecificScopeAndMultipleNonMatchingScopesGiven()
    {
        $userScopes = ['somethingelse', 'alsonotright', 'nope'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
    }

    public function testRequiresSpecificScopeAndMultipleScopesGiven()
    {
        $userScopes = ['somethingelse', 'identify', 'nope'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        $this->assertTrue(true);
    }

    public function testBlankRequireShouldDenyRegularScopes()
    {
        $userScopes = ['identify'];

        $this->setRequest();
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, $this->next);
    }

    public function testRequireScopesLayered()
    {
        $userScopes = ['identify'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, function () use ($requireScopes) {
            app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        });

        $this->assertTrue(true);
    }

    public function testRequireScopesLayeredNoPermission()
    {
        $userScopes = ['somethingelse'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes);
        $this->setUser($this->user, $userScopes);

        $this->expectException(MissingScopeException::class);
        app(RequireScopes::class)->handle($this->request, function () use ($requireScopes) {
            app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        });
    }

    public function testRequireScopesSkipped()
    {
        $userScopes = ['somethingelse'];
        $requireScopes = ['identify'];

        $this->setRequest($requireScopes, Request::create('/api/v2/changelog', 'GET'));
        $this->setUser($this->user, $userScopes);

        app(RequireScopes::class)->handle($this->request, $this->next, ...$requireScopes);
        $this->assertTrue(true);
    }

    public function clientCredentialsDataProvider()
    {
        return [
            'null is not a valid scope' => [null, MissingScopeException::class],
            'empty scope should fail' => [[], MissingScopeException::class],
            'public' => [['public'], null],
            'all scope is not allowed' => [['*'], AuthenticationException::class],
        ];
    }

    public function clientCredentialsWhenAllScopeRequiredDataProvider()
    {
        return [
            'null is not a valid scope' => [null, MissingScopeException::class],
            'empty scope should fail' => [[], MissingScopeException::class],
            'public' => [['public'], MissingScopeException::class],
            'all scope is not allowed' => [['*'], AuthenticationException::class],
        ];
    }

    protected function setRequest(?array $scopes = null, $request = null)
    {
        $this->request = $request ?? Request::create('/api/_fake', 'GET');

        $this->next = static function () {
            // just an empty closure.
        };

        // so request() works
        $this->app->instance('request', $this->request);

        // set a fake route resolver
        $this->request->setRouteResolver(function () use ($scopes) {
            $route = new Route(['GET'], '/api/_fake', null);
            $route->middleware('require-scopes');

            if ($scopes !== null) {
                $route->middleware('require-scopes:'.implode(',', $scopes));
            }

            return $route;
        });
    }

    protected function setUp(): void
    {
        parent::setUp();

        // nearly all the tests in the class need a user, so might as well set it up here.
        $this->user = factory(User::class)->create();
    }

    protected function setUser(?User $user, ?array $scopes = null)
    {
        $this->request->setUserResolver(function () use ($user) {
            return $user;
        });

        // TODO: should there be a test against null scopes?
        $this->actAsScopedUser($user, $scopes);
    }
}
