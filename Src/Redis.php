<?php


namespace Ghf;




class Redis
{
    /**
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:05
     * @param string $name
     * @return \Redis
     * @throws \Exception
     */
    public static function getCon($name = 'default'){
        static $cons = [];
        if(!isset($cons[$name])){
            $conf = Conf::GetArr('redis.'.$name,[
                'host' => '127.0.0.1',
                'port' => 6379,
                'auth' => '',
                'db' => 0
            ]);
            $redis = new \Redis();
            if(!$redis->connect($conf['host'],$conf['port'])){
                Error::errorMsg(1201,'%s -> %s:%d 链接失败',$name,$conf['host'],$conf['port']);
            }
            if($conf['auth'] && !$redis->auth($conf['auth'])){
                Error::errorMsg(1202,'%s -> %s:%d 授权失败',$name,$conf['host'],$conf['port']);
            }
            if($conf['db'] && !$redis->select($conf['db'])){
                Error::errorMsg(1203,'%s -> %s:%d 选择db失败',$name,$conf['host'],$conf['port']);
            }
            $cons[$name] = $redis;
        }

        return $cons[$name];
    }

    /**
     * 查询缓存
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:11
     * @param $key
     * @param $func
     * @param int $timeout
     * @param string $conName
     * @return mixed
     * @throws \Exception
     */
    public static function GetCache($key,$func,$timeout = 0,$conName = 'default'){
        $redis = self::getCon($conName);
        $ret = $redis->get($key);
        if($ret === false){
            $ret = $func();
            if($timeout > 0){
                $redis->set($key,json_encode($ret),$timeout);
            }else{
                $redis->set($key,json_encode($ret));
            }
            return $ret;
        }
        return json_decode($ret);
    }


    /**
     * 发布消息
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:23
     * @param $key
     * @param $data
     * @param string $conName
     * @return bool|int
     * @throws \Exception
     */
    public static function PublishMsg($key,$data,$conName = 'defalut'){
        $val = [
            't' => 0,
            'times' => [time()],
            'data' => $data,
        ];
        return self::getCon($conName)->rPush($key,json_encode($val));
    }

    /**
     * 消息队列消费者
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:23
     * @param $key
     * @param $func
     * @param string $conName
     * @throws \Exception
     */
    public static function ConsumerMsg($key,$func,$conName = 'default'){
        $redis = self::getCon($conName);
        while(true){
            $ret = $redis->blPop($key);
            $data = json_decode($ret,true);
            try{
                if(!func($data['data'])){
                    $data['t'] += 1;
                    $data['times'][] = time();
                    if($data['t'] > 3){
                        $redis->rPush($key.'.bak',json_encode($data));
                    }else{
                        $redis->rPush($key,json_encode($data));
                    }
                }
            }catch (\Exception $e){
                $data['t'] += 1;
                $data['times'][] = time();
                if($data['t'] > 3){
                    $redis->rPush($key.'.bak',json_encode($data));
                }else{
                    $redis->rPush($key,json_encode($data));
                }
            }
        }
    }

    public static function __callStatic($name, $arguments)
    {
        return call_user_func_array([\Ghf\Redis::getCon(),$name],$arguments);
    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([\Ghf\Redis::getCon(),$name],$arguments);
    }
}