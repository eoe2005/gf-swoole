<?php


namespace Ghf;


use Swoole\Http\Response;

abstract class Action
{
    protected $_server;
    protected $_req;
    protected $_resp;
    protected $_session = [];

    protected $_sid = '';

    protected $_rawSession = false;
    function __construct($server,\Swoole\Http\Request $req,Response $rep)
    {
        $this->_server = $server;
        $this->_req = $req;
        $this->_resp = $rep;
    }

    protected function session($k,$v = ''){
        if($this->_rawSession === false){
            $this->_sid = $this->_req->cookie['sid'] ?? md5(microtime(true).rand(1,99999));

            $this->_rawSession = Redis::getCon('session')->hGetAll('sess:'.$this->_sid);

            if(!isset($this->_req->cookie['sid'])){
                $this->setCookie('sid',$this->_sid);
            }
        }
        if($v){
            $this->_rawSession[$k] = $v;
        }else{
            return $this->_rawSession[$k] ?? '';
        }
    }

    function __destruct()
    {
        Redis::getCon('session')->hMSet($this->_sid,$this->_rawSession);
    }

    /**
     * 设置COOKIE
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/9/4 18:34
     * @param $k
     * @param $v
     * @param string $path
     * @param int $timeout
     */
    protected function setCookie($k,$v,$path = '/',$timeout = 1800){
        $this->_resp->cookie($k,$v,$timeout,$path);
    }

    protected function setSession($data){
        $this->_session = $data;
    }

    protected function task($cmd,$data){
        $this->_server->task(json_encode(['cmd' => $cmd,'data'=>$data]));
    }

    protected $_params = [];
    abstract function handle();
    public function execute($params = []){
        $this->_params = $params;
        $msg = $this->checkoutParams();
        if($msg){
            Error::errorMsg('2001',$msg);
        }
        $data = $this->handle();
        if(isset($data['code'])){
            if($this->_session){
                $data['session'] = $this->_session;
            }
            return $data;
        }
        $ret =  [
            'code' => 0,
            'msg' => '',
            'data' => $data
        ];
        if($this->_session){
            $ret['session'] = $this->_session;
        }
        return $ret;
    }


    function paramsRule(){
        return [];
    }

    private function checkoutParams(){
        $ruler = $this->paramsRule();
        foreach ($ruler as $k => $v){
            $msg= '参数 '.$k . ' ';
            if(is_array($v)){

            }else{
                $rNames = explode('|',strtolower($v));
                foreach ($rNames as $r){
                    switch ($r){
                        case 'required':
                            if(!isset($this->_params[$k]) || $this->_params[$k]){
                                return $msg.'必填';
                            }
                            break;
                        case 'int':
                            $val = $this->_params[$k] ?? '';
                            if($val && !preg_match("/^\d+$/",$val)){
                                return $msg.'必填是数字';
                            }
                            break;
                        case 'array':
                            $val = $this->_params[$k] ?? '';
                            if($val && !is_array($val)){
                                return $msg.'必填是数组';
                            }
                            break;
                        case 'date':
                            $val = $this->_params[$k] ?? '';
                            if($val && !preg_match("/^\d{4}-\d{2}-\d{2}$/",$val)){
                                return $msg.'必填是日期';
                            }
                            break;
                        case 'time':
                            $val = $this->_params[$k] ?? '';
                            if($val && !preg_match("/^\d{2}:\d{2}:\d{2}$/",$val)){
                                return $msg.'必填是时间';
                            }
                            break;
                        case 'datetime':
                            $val = $this->_params[$k] ?? '';
                            if($val && !preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/",$val)){
                                return $msg.'必填是日期和时间';
                            }
                            break;
                        case 'email':
                            $val = $this->_params[$k] ?? '';
                            if($val && filter_var($val,FILTER_VALIDATE_EMAIL)){
                                return $msg.'必填是邮箱';
                            }
                            break;
                        case 'mobile':
                            $val = $this->_params[$k] ?? '';
                            if($val && !preg_match("/^1(3|5|7|8)\d{9}$/",$val)){
                                return $msg.'必填是手机号';
                            }
                            break;
                    }
                }
            }
        }
    }

    protected function getParam($k,$def = ''){
        return $this->_params[$k] ?? $def;
    }

    protected function getSession($k,$v = ''){
        $ret = $this->_params['session'][$k] ?? $v;
        return $ret;
    }
    protected function fail($code,$msg){
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => null
        ];
    }


}