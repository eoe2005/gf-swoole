<?php


namespace App\Action;


use Ghf\Action;
use Ghf\Db;

class Test extends Action
{

    function handle()
    {
       return Db::getCon()->fetchAll("SELECT * FROM t_adopt WHERE 1=1",[]);
        return $this->_params;
    }
}