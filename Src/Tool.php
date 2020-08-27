<?php


namespace Ghf;


class Tool
{
    /**
     * 释放是邮箱
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/27 11:18
     * @param $val
     * @return bool
     */
    static function IsEmail($val){
        return !filter_var($val,FILTER_VALIDATE_EMAIL);
    }

    /**
     * 是否是手机号
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/27 11:18
     * @param $val
     * @return false|int
     */
    static function IsMobile($val){
        return preg_match("/^1(3|5|7|8)\d{9}$/",$val);
    }

    /**
     * 密码加密
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/27 11:24
     * @param $val
     * @param $sign
     * @return string
     */
    static function Pass($val,$sign){
        return md5($sign.$val);
    }
}