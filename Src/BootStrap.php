<?php


namespace Ghf;


define('DS',DIRECTORY_SEPARATOR);
define('GF_ROOT',realpath(__DIR__));
define('APP_ROOT',realpath($_SERVER['DOCUMENT_ROOT']));

class BootStrap
{
    /**
     * 接口服务
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/26 18:33
     */
    public static function Web(){
        self::init();
        $http = new \Swoole\Http\Server('0.0.0.0', Conf::GetInt('server.port',8080));
        $http->on('request',function (\Swoole\Http\Request $req,\Swoole\Http\Response $resp)use($http){
            $params = array_merge($req->post ?? [],$req->get ?? []);
            if(!isset($params['ip'])){
                $params['ip'] = $req->server['remote_addr'];
            }
            $data = self::runAction($http,$req,$resp,$req->server['path_info'],$params);
            $resp->header('Content-Type','application/json');
            $resp->write($data);
        });
        self::runServer($http);
    }
    public function Tcp(){
        self::init();
    }

    /**
     * 网关服务
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/26 18:32
     */
    public static function Gw(){
        self::init();
        $http = new \Swoole\Http\Server('0.0.0.0', Conf::GetInt('server.port',8080));
//        $http->on('WorkerStart',function ($server, $worker_id){
//            $appNames = Conf::Get('apps','');
//            $apps = explode(',',$appNames);
//
//            $conf = [];
//            foreach ($apps as $app){
//                $conf[$app];
//            }
//            //$server->confs = $conf;
//        });
        $http->on('request',function (\Swoole\Http\Request $req,\Swoole\Http\Response $resp){
            $skey = 'sid';
            $sid = $req->cookie[$skey] ?? "";
            if(!$sid){
                $sid = md5(time().$req->server['remote_addr']);
                $resp->cookie($skey,$sid,time() + 86400*365,'/');
            }
            $params = array_merge($req->post ?? [],$req->get ?? []);
            $params['session'] = Redis::getCon()->hGetAll('session:'.$sid);
            $params['ip'] = $req->server['remote_addr'];
            if($req->files){
                $files = [];
                foreach ($req->files as $f){
                    $files[$f['name']] = base64_encode($f['tmp_name']);
                }
                $params['files'] = $files;
            }
            $url = $req->server['path_info'];
            while(strpos($url,'/') === 0){
                $url = substr($url,1);
            }
            $app = strstr($url,'/',true);
            $name = substr(strstr($url,'/'),1);
            $ret = Client::Rpc($app,$name,$params);
            if(isset($ret['session'])){
                Redis::getCon()->hMSet('session:'.$sid,$ret['session']);
                unset($ret['session']);
            }
            $resp->header('Content-Type','application/json');
            $resp->write(json_encode($ret));
        });
        $http->start();
    }

    private static function runAction($server,$req,$resp,$url,$data){
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
                $ret = (new $className($server,$req,$resp))->execute($data);
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
        $server->set(array('task_worker_num' => 4));
        $server->on('task', function ($serv, $task_id, $from_id, $data) {
            $data = json_decode($data,true);
            $cmd = '\\App\\Task\\'.$data['cmd'].'Task';
            if(class_exists($cmd)){
                $ret = (new $cmd)->execute($data['data']);
            }
            $serv->finish("$cmd -> $ret");
        });

        //处理异步任务的结果(此回调函数在worker进程中执行)
        $server->on('finish', function ($serv, $task_id, $data) {
            echo "AsyncTask[$task_id] Finish: $data".PHP_EOL;
        });
        $server->on("Start",function ($s){
            $name = ucfirst(basename(APP_ROOT));
            swoole_set_process_name($name);
        });
        $server->on('WorkerStart',function ($server, $worker_id){
            $name = ucfirst(basename(APP_ROOT));
            if($worker_id >= $server->setting['worker_num']) {
                swoole_set_process_name("$name Task");
            } else {
                swoole_set_process_name("$name Event");
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