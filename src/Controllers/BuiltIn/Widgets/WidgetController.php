<?php

namespace BrooksYang\LaravelApiHelper\Controllers\BuiltIn\Widgets;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redis;

class WidgetController extends Controller
{
    /**
     * 接口请求次数统计
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function counter()
    {
        $prefix = config('api-helper.cache_tag_prefix');

        $urls = Redis::keys("{$prefix}.request_counter.*");

        $counts = [];

        foreach ($urls as $url) {
            if ($url == "{$prefix}.request_counter.api/request/counter") {
                continue;
            }

            $count = Redis::get($url);

            // 移除前缀
            $url = substr($url, 27);
            $counts[$url] = intval($count);
        }

        ksort($counts);

        return response()->json($counts);
    }
}
