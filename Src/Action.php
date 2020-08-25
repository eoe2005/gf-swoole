<?php


namespace Ghf;


abstract class Action
{
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
            return $data;
        }
        return [
            'code' => 0,
            'msg' => '',
            'data' => $data
        ];
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
                            if(!isset($this->_params[$k])){
                                return $msg.'必填';
                            }
                            break;
                        case 'int':
                            $val = $this->_params[$k] ?? '';
                            if(!preg_match("/^\d+$/",$val)){
                                return $msg.'必填是数字';
                            }
                            break;
                        case 'array':
                            $val = $this->_params[$k] ?? '';
                            if(!is_array($val)){
                                return $msg.'必填是数组';
                            }
                            break;
                        case 'date':
                            $val = $this->_params[$k] ?? '';
                            if(!preg_match("/^\d{4}-\d{2}-\d{2}$/",$val)){
                                return $msg.'必填是日期';
                            }
                            break;
                        case 'time':
                            $val = $this->_params[$k] ?? '';
                            if(!preg_match("/^\d{2}:\d{2}:\d{2}$/",$val)){
                                return $msg.'必填是时间';
                            }
                            break;
                        case 'datetime':
                            $val = $this->_params[$k] ?? '';
                            if(!preg_match("/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/",$val)){
                                return $msg.'必填是日期和时间';
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

    protected function fail($code,$msg){
        return [
            'code' => $code,
            'msg' => $msg,
            'data' => null
        ];
    }


}