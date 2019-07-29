<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

trait DocHelper
{
    /**
     * api文档namespace.
     *
     * @var string
     */
    private $docNamespace = 'BrooksYang\LaravelApiHelper\Controllers\Doc';

    /**
     * 获取路由.
     *
     * @return mixed
     */
    protected function getRoutes()
    {
        return Cache::tags(config('api-helper.cache_tag_prefix').'_routes')->remember('api_doc', config('api-helper.cache_ttl'), function () {
            $path = base_path();

            // 获取api路由
            exec("php $path/artisan route:list|grep -E '@'|awk '{print $3\":\"$5\":\"$8\":\"$9}'", $routes);

            // 排除api文档namespace
            $routes = array_filter($routes, function ($item) {
                return strpos($item, $this->docNamespace) === false;
            });

            // 处理数据
            $routes = array_map(function ($item) {
                return str_replace(':|', '', $item);
            }, $routes);

            return $routes;
        });
    }

    /**
     * 获取所有模块.
     *
     * @param $routes
     *
     * @return array
     */
    protected function getModules($routes)
    {
        return Cache::tags(config('api-helper.cache_tag_prefix').'_modules')->remember('api_doc', config('api-helper.cache_ttl'), function () use ($routes) {

            // 筛选有模块的控制器
            $routes = $this->routesFilter($routes);

            $modules = [];

            foreach ($routes as $route) {
                $attr = explode(':', $route);

                $module = $this->getModule($attr[2]);

                if (!empty($module) && !in_array($module, $modules)) {
                    array_unshift($modules, $module);
                }
            }

            // 分组
            $modules = collect($modules)->groupBy('group')->toArray();

            return $modules;
        });
    }

    /**
     * 获取模块.
     *
     * @param $controller
     *
     * @return mixed
     */
    protected function getModule($controller)
    {
        // 获取待生成文档的命名空间
        $namespaces = config('api-helper.namespaces', ['App\Http\Controllers']);

        $module = ['group' => '', 'module' => ''];

        // 筛选命名空间
        foreach ($namespaces as $group => $namespace) {
            $namespace = rtrim($namespace, '\\').'\\';

            // 若不在namespace中，跳过
            if (strpos($controller, $namespace) === false) {
                continue;
            }

            // 获取控制器
            $controller = str_replace($namespace, '', $controller);

            // 获取module信息
            $module['group'] = $group;
            $module['module'] = Arr::first(explode('\\', $controller));

            break;
        }

        return $module;
    }

    /**
     * 获取指定命名空间下的api.
     *
     * @param        $routes
     * @param string $group
     * @param string $module
     *
     * @return array
     */
    protected function getApiByModule($routes, $group = '', $module = '')
    {
        return Cache::tags(config('api-helper.cache_tag_prefix').'_api_doc')->remember("doc_for_{$group}_{$module}", config('api-helper.cache_ttl'), function () use ($routes, $group, $module) {

            // 筛选路由
            $routes = $this->routesFilter($routes, $group, $module);

            // 获取api信息
            $data = [];
            foreach ($routes as $route) {
                $data[] = $this->getApiInfo($route);
            }

            return $data;
        });
    }

    /**
     * 获取api信息.
     *
     * @param $route
     *
     * @return array
     */
    protected function getApiInfo($route)
    {
        $attr = explode(':', $route);

        $module = $this->getModule($attr[2]);
        $route = explode('@', $attr[2]);

        // 处理api信息
        $api = [
            'name'       => $this->getApiName($route[0], $route[1]),
            'method'     => Arr::first(explode('|', $attr[0])),
            'uri'        => $attr[1],
            'controller' => $route[0],
            'action'     => $route[1],
            'group'      => $module['group'],
            'module'     => $module['module'],
        ];

        return $api;
    }

    /**
     * 获取api名称.
     *
     * @param $controller
     * @param $action
     *
     * @return mixed
     */
    protected function getApiName($controller, $action)
    {
        $reflection = new ReflectionClass($controller);

        $method = $reflection->getMethod($action);
        $docComment = $method->getDocComment();

        preg_match('/\s+\*\s+(.+)/', $docComment, $matches);
        $name = @$matches[1];

        return $name;
    }

    /**
     * 获取api参数.
     *
     * @param $controller
     * @param $action
     *
     * @return array
     */
    public function getApiParams($controller, $action)
    {
        $reflection = new ReflectionClass($controller);

        $method = $reflection->getMethod($action);

        $code = file_get_contents($reflection->getFileName());
        $codeArr = explode("\n", $code);

        $start = $method->getStartLine();
        $end = $method->getEndLine();
        $methodCode = array_slice($codeArr, $start, $end - $start);

        $params = [];
        foreach ($methodCode as $line) {
            //注释提取
            $comment = explode('//', $line);
            $commentStr = trim(@$comment[1]);

            $reg = '/\$request->input\(([\'\"])([^\'\"]+)(\\1).*\)/';
            if (preg_match($reg, $line, $matches)) {
                $params[] = [
                    'param'   => @$matches[2],
                    'comment' => $commentStr,
                ];
            }

            $reg = '/\$request->get\(([\'\"])([^\'\"]+)(\\1).*\)/';
            if (preg_match($reg, $line, $matches)) {
                $params[] = [
                    'param'   => @$matches[2],
                    'comment' => $commentStr,
                ];
            }

            $reg = '/Input::get\(([\'\"])([^\'\"]+)(\\1).*\)/';
            if (preg_match($reg, $line, $matches)) {
                $params[] = [
                    'param'   => @$matches[2],
                    'comment' => $commentStr,
                ];
            }

            $reg = '/\$request->file\(([\'\"])([^\'\"]+)(\\1).*\)/';
            if (preg_match($reg, $line, $matches)) {
                $params[] = [
                    'param'   => @$matches[2],
                    'comment' => $commentStr,
                    'is_file' => 1,
                ];
            }
        }

        return $params;
    }

    /**
     * 筛选指定命名空间下的控制器.
     *
     * @param $routes
     * @param $group
     * @param $module
     *
     * @return array
     */
    private function routesFilter($routes, $group = '', $module = '')
    {
        // 获取指定命名空间
        $namespaces = config('api-helper.namespaces', ['App\Http\Controllers']);

        // 筛选Group（Namespace）
        if ($group) {
            $namespaces = @$namespaces[$group] ? [$group => $namespaces[$group]] : [];
        }

        // 筛选路由
        $routes = array_filter($routes, function ($item) use ($namespaces, $group, $module) {
            $flag = false;

            foreach ($namespaces as $namespace) {
                $namespace = rtrim($namespace, '\\').'\\';

                // 若不在指定命名空间下，跳过
                if (strpos($item, $namespace) === false) {
                    continue;
                }

                // 获取控制器
                $controller = Arr::last(explode($namespace, $item));

                // 若不包含次级命名空间，跳过
                if (strpos($controller, '\\') === false) {
                    continue;
                }

                // 筛选 module
                if ($module && strpos($controller, $module.'\\') === false) {
                    continue;
                }

                $flag = true;
                break;
            }

            return $flag;
        });

        return $routes;
    }
}
