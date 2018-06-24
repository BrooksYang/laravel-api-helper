<?php

namespace BrooksYang\LaravelApiHelper\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;

trait DocHelper
{
    /**
     * api文档namespace
     *
     * @var string
     */
    private $docNamespace = 'BrooksYang\LaravelApiHelper\Controllers\Doc';

    /**
     * 获取路由
     *
     * @return mixed
     */
    protected function getRoutes()
    {
        return Cache::tags(config('api-helper.cache_tag_prefix') . '_routes')->remember('api_doc', config('session.lifetime'), function () {

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
     * 获取所有模块
     *
     * @param $routes
     * @return array
     */
    protected function getModules($routes)
    {
        return Cache::tags(config('api-helper.cache_tag_prefix') . '_modules')->remember('api_doc', config('session.lifetime'), function () use ($routes) {

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

            return $modules;
        });
    }

    /**
     * 获取模块
     *
     * @param $controller
     * @return mixed
     */
    protected function getModule($controller)
    {
        // 获取待生成文档的命名空间
        $namespaces = config('api-helper.namespaces', ['App\Http\Controllers']);

        $module = '';

        // 筛选命名空间
        foreach ($namespaces as $namespace) {
            $namespace = rtrim($namespace, '\\') . '\\';

            // 若不在namespace中，跳过
            if (strpos($controller, $namespace) === false) {
                continue;
            }

            // 以该namespace下第一级文件夹名称作为模块名
            $controller = str_replace($namespace, '', $controller);
            $module = Arr::first(explode('\\', $controller));

            // 若模块下没有文件夹，则以namespace最后一位作为模块名
            if (strpos($module, 'Controller@') !== false) {
                $module = Arr::last(explode('\\', rtrim($namespace, '\\')));
            }

            break;
        }

        return $module;
    }

    /**
     * 获取指定模块下的api
     *
     * @param        $routes
     * @param string $module
     * @return array
     */
    protected function getApiByModule($routes, $module = '')
    {
        return Cache::tags(config('api-helper.cache_tag_prefix') . '_api_doc')->remember("doc_for_$module", config('session.lifetime'), function () use ($routes, $module) {

            // 筛选有模块的控制器
            $routes = $this->routesFilter($routes);

            // 筛选指定模块下的控制器
            if ($module) {
                $routes = array_filter($routes, function ($item) use ($module) {
                    return in_array($module, explode('\\', $item));
                });
            }

            // 全部模块（namespaces中）
            $namespaces = config('api-helper.namespaces', ['App\Http\Controllers']);
            if (empty($module)) {
                $routes = array_filter($routes, function ($item) use ($namespaces) {
                    $flag = false;

                    foreach ($namespaces as $namespace) {
                        if (strpos($item, $namespace) !== false) {
                            $flag = true;
                            break;
                        }
                    }

                    return $flag;
                });
            }

            // 获取api信息
            $data = [];
            foreach ($routes as $route) {
                $data[] = $this->getApiInfo($route);
            }

            return $data;
        });
    }

    /**
     * 获取api信息
     *
     * @param $route
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
            'module'     => $module,
        ];

        return $api;
    }

    /**
     * 获取api名称
     *
     * @param $controller
     * @param $action
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
     * 获取api参数
     *
     * @param $controller
     * @param $action
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
     * 筛选有模块的控制器
     *
     * @param $routes
     * @return array
     */
    private function routesFilter($routes)
    {
        $routes = array_filter($routes, function ($item) {
            return substr_count($item, '\\') > 3;
        });

        return $routes;
    }
}
