<?php


namespace Ghf;


class Error
{

    static function errorMsg($code,$msg){
        $args = func_get_args();
        array_unshift($args);
        $msg = call_user_func_array('sprintf',$args);
        throw new \Exception($msg,$code);
    }


}