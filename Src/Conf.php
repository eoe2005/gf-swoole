<?php


namespace Ghf;


class Conf
{


    public static function Get($key,$def = '',$confName = ''){
        $data = self::getConfFile($confName);
        return $data[$key] ?? $def;
    }
    public static function GetInt($key,$def = 0,$confName = ''){
        $ret = self::Get($key,$def,$confName);
        return intval($ret);
    }
    public static function GetArr($pre,$keyDef = [],$confName = ''){
        $ret = [];
        foreach ($keyDef as $k => $v){
            $ret[$k] = self::Get($pre.'.'.$k,$v,$confName);
        }
        return $ret;
    }

    private static function getConfFile($confName = ''){
        static $data = [];
        $confName = $confName ?: "default";
        if(!isset($data[$confName])){
            $filePath = APP_ROOT.DS.$confName.'.env';
            if(file_exists($filePath)){
                $data[$confName] = parse_ini_file($filePath);
            }else{
                $data[$confName] = [];
            }
        }
        return $data[$confName];
    }
}