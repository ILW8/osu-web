{{--
    Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
    See the LICENCE file in the repository root for full licence text.
--}}

<table class="ranking-page-table">
    <thead>
    <tr>
        <th class="ranking-page-table__heading"></th>
        <th class="ranking-page-table__heading ranking-page-table__heading--main">
            playlist item id: {{$playlistitem['id']}}
        </th>
        <th class="ranking-page-table__heading">
            {{ osu_trans('rankings.stat.accuracy') }}
        </th>
        <th class="ranking-page-table__heading ranking-page-table__heading--focused">
            {{ osu_trans('rankings.type.score') }}
        </th>
    </tr>
    </thead>
    <tbody>

    @foreach ($playlistitem->highScores as $index => $score)
        <tr class="ranking-page-table__row{{$score->user->isActive() ? '' : ' ranking-page-table__row--inactive'}}">
            <td class="ranking-page-table__column ranking-page-table__column--rank">
                #{{ $scores->firstItem() + $index }}
            </td>
            <td class="ranking-page-table__column">
                <div class="ranking-page-table__user-link">
                    @include('objects._flag_country', [
                        'countryName' => $score->user->country->name,
                        'countryCode' => $score->user->country->acronym,
                        'modifiers' => ['medium'],
                    ])
                    <a
                        href="{{ route('users.show', ['user' => $score->user_id, 'mode' => $mode ?? null]) }}"
                        class="ranking-page-table__user-link-text js-usercard"
                        data-user-id="{{ $score->user_id }}"
                        data-tooltip-position="right center"
                    >
                        {{ $score->user->username }}
                    </a>
                </div>
            </td>
            <td class="ranking-page-table__column ranking-page-table__column--dimmed">
                {{ format_percentage($score->accuracy * 100) }}
            </td>
            <td class="ranking-page-table__column ranking-page-table__column--focused">
                <span>{{i18n_number_format($score->total_score)}}</span>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
