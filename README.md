#####说明
该扩展包根据路由及注释文件自动生成可视化api文档，并采用guzzlehttp进行调试

#####安装
```php
composer require brooksyang/laravel-api-helper
```

#####打印css资源
```php
php artisan vendor:publish --tag=api-doc
```

#####配置env缓存驱动
```php
CACHE_DRIVER=redis
```

#####访问地址：
localhost/api/docs
