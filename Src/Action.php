<?php


namespace Ghf;


abstract class Action
{
    private $_params = [];
    abstract function handle();
    public function execute($params = []){

    }

    function paramsRule(){
        return [];
    }

    private function checkourParams(){}

    protected function getParam($k,$def = ''){

    }

}