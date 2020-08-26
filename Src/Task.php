<?php


namespace Ghf;


abstract class Task
{
    public function execute($data){
        return $this->handle($data) ? 'OK' : 'FAIL';
    }

    abstract function handle($data);
}