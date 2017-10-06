##### 说明
该扩展包根据路由及注释文件自动生成可视化api文档，并采用guzzlehttp进行调试

## 配置

##### 安装
```php
composer require brooksyang/laravel-api-helper
```

##### 打印css资源
```php
php artisan vendor:publish --tag=api-doc
```

##### 配置env缓存驱动
```php
CACHE_DRIVER=redis
```

##### 取消 csrf 验证
```php
// 注释 app/Http/Kernel.php 中的 VerifyCsrfToken::class,
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

##### 访问地址：
```php
http://localhost/api/docs
```
