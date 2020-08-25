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
1. 命名空间 App\Action
2. 继承 \Ghf\Action
3. 获取参数  $this->getParam('key','')
4. 参数使用前可以校验 
```php
function paramsRule(){
        return [
        'uid' => 'required:int',
        'start' => 'required|date',
        'end' => 'required|time',
        'datetime' => 'datetime',
        'tag' => 'required|array'
        ];
    }
5. 错误处理 return $this->fail(123,"系统错误")
6. 正常直接 return []
```
## 数据库使用
### 使用事务
```php
\Ghf\Db::getCon()->begin(function(){
    正常业务逻辑，回滚的话直接抛出异常即可
});

Model 中方法同上

```
### 直接使用sql
```php

$db = \Ghf\Db::getCon();
$list = $db->fetchAll("select * from test wehre a=:a and b=:b",[':a' => 1,':b' => 2]);
$list1 = $db->fetchAll("select * from test wehre a=? and b=?",[1,2]);
$list2 = $db->fetchAll("select * from test wehre a=%d and b=%d",1,2);

$row = $db->fetchRow("select * from test wehre a=:a and b=:b",[':a' => 1,':b' => 2]);
$row1 = $db->fetchRow("select * from test wehre a=? and b=?",[1,2]);
$row2 = $db->fetchRow("select * from test wehre a=%d and b=%d",1,2);

$inserId = $db->insert("insert into test(a,b,c) VALUES(:a,:b,:c)",[':a' => 1,':b' => 2,":c" => 3]);
$inserId1 = $db->insert("insert into test(a,b,c) VALUES(?,?,?)",[1,2,3]);
$inserId2 = $db->insert("insert into test(a,b,c) VALUES(%d,%d,%d)",1,2,3);

$upCount = $db->update("UPDATE test set a=:a where b=:b",[':a' => 1,':b' => 2]);
$upCount1 = $db->update("UPDATE test set a=? where b=?",[1,2]);
$upCount2 = $db->update("UPDATE test set a=%d where b=%d",1,2);


```
### 使用Model

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
