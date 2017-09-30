
##### 注意:
该项目采用了缓存机制，请确保您的缓存驱动可用，推荐使用redis驱动
```php
composer require predis/predis
```

配置env缓存驱动
```php
CACHE_DRIVER=redis
```

安装
```php
composer require brooksyang/laravel-api-helper
```

打印css资源
```php
php artisan vendor:publish --tag=api-doc
```

访问：<host>/api/docs 即可