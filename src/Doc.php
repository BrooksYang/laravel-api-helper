<?php

namespace BrooksYang\LaravelApiHelper;

use BrooksYang\LaravelApiHelper\Traits\DocHelper;
use Illuminate\Support\Facades\Cache;

class Doc
{
    use DocHelper;

    /**
     * 获取路由.
     *
     * @return mixed
     */
    public function routes()
    {
        return $this->getRoutes();
    }

    /**
     * 获取模块.
     *
     * @return array
     */
    public function modules()
    {
        $routes = $this->getRoutes();
        $modules = $this->getModules($routes);

        return $modules;
    }

    /**
     * 获取指定模块下的api.
     *
     * @param string $group
     * @param string $module
     *
     * @return array
     */
    public function api($group = '', $module = '')
    {
        $routes = $this->getRoutes();

        return $this->getApiByModule($routes, $group, $module);
    }

    /**
     * 获取api总数.
     *
     * @return int
     */
    public function total()
    {
        $items = Cache::tags(config('api-helper.cache_tag_prefix').'_api_doc')->get('doc_for_') ?: $this->api();

        return count($items);
    }
}
