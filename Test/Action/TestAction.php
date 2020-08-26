<?php


namespace App\Action;


use Ghf\Action;
use Ghf\Db;

class TestAction extends Action
{

    function handle()
    {
        $this->task('test',"abc");
       return Db::getCon()->fetchAll("SELECT * FROM t_adopt WHERE 1=1",[]);
        return $this->_params;
    }
}