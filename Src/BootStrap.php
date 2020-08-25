<?php


namespace Ghf;



define('DS',DIRECTORY_SEPARATOR);
define('GF_ROOT',realpath(__DIR__));
define('APP_ROOT',realpath($_SERVER['DOCUMENT_ROOT']));

class BootStrap
{
    public function Web(){
        self::init();
        $http = new \Swoole\Http\Server('0.0.0.0', Conf::GetInt('server.port',8080));
        $http->on('request',function (\Swoole\Http\Request $req,\Swoole\Http\Response $resp){
            $data = self::runAction($req->server['path_info'],array_merge($req->post ?? [],$req->get ?? []));
            $resp->header('Content-Type','application/json');
            $resp->write($data);
        });
        self::runServer($http);
    }
    public function Tcp(){
        self::init();
    }

    private static function runAction($url,$data){
        $names = explode('/',$url);
        $names = array_map(function ($v){
            return ucfirst($v);
        },$names);
        $className = '\\App\\Action';
        foreach ($names as $name){
            if(!$name){
                continue;
            }
            $className .= '\\'.$name;
        }
        $ret = [];
        $className .= 'Action';
        if(class_exists($className)){
            try{
                $ret = (new $className)->execute($data);
            }catch (\Exception $e){
                $ret = [
                    'code' => 1002,
                    'msg' => '接口异常'
                ];
            }catch (\Error $e){
                $ret = [
                    'code' => 1001,
                    'msg' => '系统错误'
                ];
            }
        }else{
            $ret = [
                'code' => 1003,
                'msg' => '接口不存在'
            ];
        }
        return json_encode($ret);
    }

    private static function runServer($server){
        $server->on('WorkerStart',function ($server, $worker_id){
            global $argv;
            if($worker_id >= $server->setting['worker_num']) {
                swoole_set_process_name("php {$argv[0]} task worker");
            } else {
                swoole_set_process_name("php {$argv[0]} event worker");
            }
        });
        $server->on('WorkerExit',function ($s,$w){});
        $server->start();
    }



    private static function init(){
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