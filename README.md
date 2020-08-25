# gf-swoole
 针对swoole封装的一个httpApi框架，仅支持post提交，返回json结构，没有鉴权等功能，只可以基础服务使用

## 项目目录
> Action 程序中主要接口文件都在这里
>
> Model 数据看目录
>
> App.php 程序运行文件
>
> default.env   配置文件

## 引导文件

App.php


```php
include "../Src/BootStrap.php";
\Ghf\BootStrap::Web();
```

## action描述

## 数据库使用

## Cache使用
```php
$redis = \Ghf\Redis::getCon();
$data = \Ghf\Redis::GetCache("key",function(){
    return 123;
},30);
$redis->set($k,$v,$timeout)
$redis->get($key)
...
```

## 项目配置
```ini
# 数据库配置
db.default.host=127.0.0.1
db.default.prot=3306
db.default.user=root
db.default.passwd=usbw
db.default.dbname=pets
db.default.pretable=t_

# 缓存配置
redis.default.host=
redis.default.port=
redis.default.auth=
redis.default.db=0
```

## 日志系统

```php
\Ghf\Log::Debug("debug ");
\Ghf\Log::Debug("debug %s","测试");

\Ghf\Log::Error("Error ");
\Ghf\Log::Error("Error %s","测试");

\Ghf\Log::Info("Info ");
\Ghf\Log::Info("Info %s","测试");

\Ghf\Log::Sql("Sql ");
\Ghf\Log::Sql("Sql %s","测试");
```
