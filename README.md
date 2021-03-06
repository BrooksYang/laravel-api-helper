[![Build Status](https://travis-ci.org/BrooksYang/laravel-api-helper.svg?branch=master)](https://travis-ci.org/BrooksYang/laravel-api-helper)
[![Software License][ico-license]](LICENSE)

[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat

## 介绍
该项目是基于 Laravel 5.5 的API文档生成工具，根据 laravel 路由及注释文件生成可视化 api 文档，内置接口调试工具。

## Demo
[点击访问](http://api-helper.brooksyang.cn/api/docs)

## 项目依赖
|   依赖包    |   版本  |
|   :---     | :----: |
|    PHP     |  >=7.0 |
|   Laravel  |  >=5.5 |
|   predis   |  >=1.1 |
| Guzzlehttp（2.0版本已废弃） |  >=6.3 |

## 更新日志
>v2.0.0 2019-03-29
>- `重构` 移除 GuzzleHttp 依赖，接口测试改用 Laravel 内置的 Request 类请求
>- `重构` 移除 ResponseHelper Trait

>v1.6.0 2019-03-19
>- `新增` 支持资源路由（参数接收方式见「使用示例」）
>- `新增` 内置接口访问统计（需启用 request.counter 中间件）

>v1.5.4 2019-03-19
>- `新增` 请求 api 设置 Accept 为 json

>v1.5.3 2019-03-01
>- `优化` 样式调整

>v1.5.2 2019-03-01
>- `新增` 可选是否开启文档功能，默认开启
>- `优化` 移除js公共资源

>v1.5.1 2019-03-01
>- `优化` 文件结构调整

>v1.5.0 2019-03-01
>- `新增` 测试工具提供 Pretty、Raw、Preview 三种响应结果视图

>v1.4.7 2019-02-28
>- `新增` 压力测试调整为可选功能，默认关闭

>v1.4.6 2019-02-28
>- `优化` 压缩css文件

>v1.4.5 2019-02-28
>- `新增` 测试工具允许使用 dd、dump、echo 等方法直接打印数据
>- `优化` 测试工具返回完整异常信息

>v1.4.4 2018-07-20
>- `优化` 左侧菜单样式优化

>v1.4.3 2018-06-28
>- `优化` 模块分组优化

>v1.4.2 2018-06-28
>- `新增` 新增模块分组功能，以区分不同命名空间下的重名文件夹

>v1.4.1 2018-06-24
>- `修复` 修复api-helper配置文件的bug

>v1.4.0 2018-06-24
>- `新增` 可指定文档生成的命名空间，向下兼容v1.3，默认命名空间为App\Http\Controllers

>v1.3.2 2018-03-31
>- `优化` 压力测试安全机制优化

>v1.3.0 2018-02-13
>- `新增`表单支持文件上传

>v1.2.0 2018-01-22
>- `新增` 新增ResponseHelper Trait，封装常用api返回方法

>v1.1.1 2018-01-22
>- `新增` Guzzle 请求异常处理

>v1.1.0 2017-12-18
>- `新增` 支持在线ApacheBench服务器压力测试，并缓存最近一次压测结果，在接口列表及详情页展示

## 安装
```php
composer require brooksyang/laravel-api-helper
```

## 配置
打印配置文件，及静态资源，该命令将生成 config/api-helper.php，及 /public/vendor/api_helper 文件夹
```php
php artisan vendor:publish --tag=api-helper
```

api-helper.php 配置项说明
```php
cache_tag_prefix // 缓存前缀，默认为api_helper，建议不同项目设置不同前缀以防止冲突

cache_ttl // 缓存时长，默认120分钟，可根据项目需求自行设置

api_base_url // 接口请求基础地址，默认为当前<host_name>，一般情况下不需要配置，若存在内外网不通的情况，可设置为相应内网地址

api_doc // v1.5.2 新增，是否开启Api文档及测试工具，默认 true

api_pressure_test // v1.4.7 新增，是否开启压力测试功能，默认 false

namespaces // 指定生成Api文档命名空间，数组，key为group，value为namespace，请确保namspace之间没有交集，否则小集合将被忽略
```
注意：请确保所配置的命名空间下存在次级命名空间，否则将被忽略，次级命名空间将作为模块名，更深层次命名空间与次级命名空间同级，例：

namespaces 配置示例
```php
namespaces => [
    'Helper' => 'BrooksYang\LaravelApiHelper\Controllers\BuiltIn', // v1.6.0 新增，内置接口访问统计
    'App'  => 'App\Http\Controllers', // 生效
    'Test' => 'App\Http\Controllers\Test', // 无效
    'Api'  => 'Api\Controllers' // 生效
];
```
该配置中，「App」包含「Test」，「App」与「Api」同级，所以「Test」被忽略

代码目录结构
```php
|--app
    |--Http
        |--Controllers
            |--TestController // 无效
            |--Demo
                |--DemoController // 生效
                |--Deep
                    |--DeepController // 生效，与DemoController 同级
|--Api
    |--Controllers
        |--Xxx
            |--XxxController // 生效
```
以上配置，TestController被忽略，DemoController 生效，DeepController与DemoController同级生效，XxxController生效

## 启用接口访问统计（可选）
vim app/Http/Kernel，在 $middlewareGroups 中添加 'request.counter' 中间件
```php
/**
 * The application's route middleware groups.
 *
 * @var array
 */
protected $middlewareGroups = [
    // ***
    
    'api' => [
        'throttle:60,1',
        'bindings',
        'request.counter', // 启用「接口访问统计」中间件
    ],
];
```

## 设置缓存驱动
修改 .env 文件，将缓存驱动设置为redis（推荐）
```php
CACHE_DRIVER=redis
```

若本机没有安装redis，可暂时设置为array
```php
CACHE_DRIVER=array
```

## 使用示例
```php
use BrooksYang\LaravelApiHelper\Traits\ResponseHelper;

/**
 * 这里是api标题
 *
 * @return \Illuminate\Http\Response
 */
public function index(Request $request)
{
    // 支持以下三种方式接收表单参数
    $paramA = $request->input('param_a'); // 参数一说明
    $paramB = $request->get('param_b'); // 参数二说明
    $paramC = Input::get('param_c'); // 参数三说明
    
    // 针对资源路由（v1.6.0 新增）
    $param = $request->input('{param}'); // 资源路由参数接收
    
    // 支持接收表单文件（v1.3.0新增）
    $file = $request->file('upload_file');
    
    // 以下是返回内容，ResponseHelper封装了三种返回方法，不强制使用，可自定义返回数据结构
    return $this->jsonResponse(['test' => 'blablabla'], '操作成功'); // 方式一，操作成功，返回数据及提示信息
    return $this->errorResponse(['code' => 'xxx', 'msg' => '操作失败']); // 方式二，操作失败，返回错误码及错误消息
    return $this->msgResponse('操作成功'); // 方式三，操作成功，仅返回成功提示消息
}
```

## 注意
若设置了redis驱动，添加了新的api之后，需执行清除缓存操作（若缓存驱动为array，则跳过该步骤）
```php
php artisan cache:clear
```

## 访问地址
```php
http://<HOST_NAME>/api/docs
```

## TODO LIST
- [x] 支持资源路由
- [x] 接口访问统计
- [ ] 记录接口访问日志
- [ ] 返回参数自动生成文档
