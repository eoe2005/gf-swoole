<?php


namespace Ghf;


class Log
{
    static function Debug($msg){
        $args = func_get_args();
        self::saveLog('DEBUG',$args);
    }
    static function Error($msg){
        $args = func_get_args();
        self::saveLog('ERROR',$args);
    }
    static function Info($msg){
        $args = func_get_args();
        self::saveLog('INFO',$args);
    }
    static function Waring($msg){
        $args = func_get_args();
        self::saveLog('WARING',$args);
    }

    static function __callStatic($name, $arguments)
    {
        self::saveLog(strtoupper($name),$arguments);
    }


    private static function saveLog($levelName,$msg){
        $msg = sprintf("[%s] %s %s\n",date('Y-m-d H:i:s'),$levelName,call_user_func_array('sprintf',$msg));
        echo $msg;
    }
}