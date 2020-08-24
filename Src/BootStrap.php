<?php


namespace Ghf;

define('DS',DIRECTORY_SEPARATOR);
define('GF_ROOT',realpath(__DIR__));
define('APP_ROOT',realpath($_SERVER['DOCUMENT_ROOT']));

class BootStrap
{
    public function Web(){}
    static function Run(){
        spl_autoload_register('\Ghf\BootStrap::LoadClass');
    }

    static function LoadClass($cls){
        $pre = strstr($cls,'\\',true);
        $beforPath = '';
        switch ($pre){
            case 'Ghf':
                $beforPath = GF_ROOT;
                break;
            case 'App':
                $beforPath = APP_ROOT;
                break;
        }
        if($beforPath){
            $file = str_replace('\\',DS,strstr($cls,'\\'));
            $filePath = $beforPath.$file.'.php';
            if(file_exists($filePath)){
                include_once $filePath;
            }
        }
    }
}