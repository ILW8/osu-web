{{--
    Copyright (c) ppy Pty Ltd <contact@ppy.sh>. Licensed under the GNU Affero General Public License v3.0.
    See the LICENCE file in the repository root for full licence text.
--}}

<div class="rankings-beatmapsets">
    <div class="osu-layout__col-container osu-layout__col-container--with-gutter">
{{--        @foreach ($beatmapsets as $beatmapset)--}}
{{--        @foreach($beatmapsets as $beatmapset)--}}
{{--            @php--}}
{{--//                $beatmapset = $beatmapsets->filter(function ($val, $key) {--}}
{{--//                    return val['']--}}
{{--//                });--}}
{{--//            $a = array_column($beatmapsets->toArray(), 'beatmaps', 'id');--}}
{{--//            $yes = array_column($beatmapsets->toArray()[1]['beatmaps'], 'beatmap_id', 'id');--}}
{{--//            var_dump($a);--}}
{{--//            echo "\n";--}}
{{--//            echo implode(":727:", $yes);--}}


{{--//            $eha = [];--}}
{{--//            foreach ($beatmapsets as $beatmapset) {--}}
{{--//                $eha[] = array_column($beatmapset['beatmaps']->toArray(), 'beatmap_id', 'id');--}}
{{--//            }--}}
{{--//            var_dump($eha);--}}
{{--//            $the_beatmapset--}}

{{--            @endphp--}}
{{--            <div class="osu-layout__col osu-layout__col--sm-6">--}}
{{--                <div--}}
{{--                    class="js-react--beatmapset-panel"--}}
{{--                    data-beatmapset-panel="{{ json_encode(['beatmapset' => json_item($beatmapset, 'Beatmapset', ['beatmaps'])]) }}"--}}
{{--                ></div>--}}
{{--                <div>--}}
{{--                    @php--}}
{{--                        $aaaa = $playlistitems->filter(function ($value, $key) {--}}
{{--                                        return $value['beatmap']['beatmap_id'] == $b;--}}
{{--                                    });--}}
{{--                        $filtered_scores = $scores;--}}
{{--                        echo $aaaa;--}}
{{--                    @endphp--}}
{{--                    @include('multiplayer.rooms._games', compact('filtered_scores'))--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endforeach--}}
        @foreach($playlistitems as $playlistitem)
{{--            <pre>{{ print_r($playlistitem->toArray(), true) }}</pre>--}}
            @php
                $the_beatmapset = null;
                foreach ($beatmapsets as $beatmapset) {
                    foreach ($beatmapset['beatmaps'] as $beatmap) {
                        if ($beatmap['beatmap_id'] == $playlistitem['beatmap_id']) {
                            $the_beatmapset = $beatmapset;
                            break;
                        }
                    }
                    if ($the_beatmapset != null) {
                        break;
                    }
                }
//                echo "<h1>hiiiiii</hi>\n";
//                echo "<pre>".print_r($the_beatmapset->toArray(), true)."</pre>\n";
            @endphp
                        <div class="osu-layout__col osu-layout__col--sm-6">
                            <div
                                class="js-react--beatmapset-panel"
                                data-beatmapset-panel="{{ json_encode(['beatmapset' => json_item($the_beatmapset, 'Beatmapset', ['beatmaps'])]) }}"
                            ></div>
                            <div>
                                @php
//                                    $aaaa = $playlistitems->filter(function ($value, $key) {
//                                                    return $value['beatmap']['beatmap_id'] == $b;
//                                                });
                                    $filtered_scores = $scores;
//                                    echo $aaaa;
                                @endphp
                                @include('multiplayer.rooms._games', compact('filtered_scores', 'playlistitem'))
                            </div>
                        </div>

        @endforeach
    </div>
</div>
