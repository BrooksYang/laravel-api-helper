##### 说明
该扩展包根据路由及注释文件自动生成可视化api文档，并采用guzzlehttp进行调试

## Demo
[点击访问](http://api-helper.brooksyang.cn/api/docs)

## 更新说明
```php
v1.1.1 Guzzle 请求异常处理

v1.1.0 支持在线ApacheBench服务器压力测试，并缓存最近一次压测结果，在接口列表及详情页展示
```

## 配置

##### 安装
```php
composer require brooksyang/laravel-api-helper
```

##### 打印css资源
```php
php artisan vendor:publish --tag=api-doc
```

##### 配置env文件中缓存驱动为redis（开发阶段若不打算用缓存，则可以设置为array）
```php
CACHE_DRIVER=redis
```

##### 异常处理（可选）
在/app/Exceptions/Handler.php文件的render方法中添加以下方法进行异常处理
```php
    // 表单验证异常处理
    if ($exception instanceof \Illuminate\Validation\ValidationException) {
        return response()->json(['code' => 11, 'msg' => $exception->errors(), 'data' => null]);
    }
```

## 使用

##### 使用示例
```php
/**
 * 这里是api标题
 *
 * @return \Illuminate\Http\Response
 */
public function index(Request $request)
{
    // 支持以下三种方式接收参数
    $paramA = $request->input('param_a'); // 参数一说明
    $paramB = $request->get('param_b'); // 参数二说明
    $paramC = Input::get('param_c'); // 参数三说明
    
    // 以下是返回内容
    return response()->json([
        'code' => 1,
        'msg' => 'success',
        'data' => [
            'test' => 'blablabla'
        ]
    ]);
}
```

##### 注意
该扩展包会自动生成api列表缓存，若添加了新的api，请在项目根目录下执行以下操作
```php
php artisan cache:clear
```

##### 访问地址：
```php
http://localhost/api/docs
```
