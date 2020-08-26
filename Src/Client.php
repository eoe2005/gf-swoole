<?php


namespace Ghf;




class Client
{
    public static function Rpc($app,$cmd,$args){
        $host = self::getHost($app);
        $ret = '';
        if(strpos($host,'http') === 0){
            return self::http($host,$cmd,$args);
        }else{
            return self::tcp($host,$cmd,$args);
        }
    }
    private static function tcp($host,$cmd,$args){
        try{
            $fp = stream_socket_client($host,$error,$errmsg,5);
            $sendData = [
                'cmd' => $cmd,
                'args' => $args
            ];
            fwrite($fp,json_encode($sendData)."\n");
            $ret = fgetss($fp);
            return json_decode($ret,true);
        }catch (\Exception $e){
            return ['code' => 1005,'msg' => $e->getTraceAsString()];
        }

    }
    private static function http($host,$cmd,$args){
        var_dump($host.$cmd);
        $ch = \curl_init($host.$cmd);
        \curl_setopt_array($ch,[
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $args,
            CURLOPT_TIMEOUT_MS => 5000
        ]);
        try{
            $ret = \curl_exec($ch);
            if(\curl_errno($ch)){
                return ['code' => 1005,'msg' => \json_encode(curl_getinfo($ch))];
            }
            return json_decode($ret,true);
        }catch (\Exception $e){
            return ['code' => 1005,'msg' => $e->getTraceAsString()];
        }


    }

    private static function getHost($app){
        return Conf::Get('server.'.$app.'.host');
    }
}