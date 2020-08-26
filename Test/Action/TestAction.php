<?php


namespace App\Action;


use Ghf\Action;
use Ghf\Db;

class TestAction extends Action
{

    function handle()
    {
        $this->task('test',"abc");
        $this->setSession(['a' => 1]);
        return 123;
    }
}