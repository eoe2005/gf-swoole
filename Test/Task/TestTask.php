<?php


namespace App\Task;


use Ghf\Task;

class TestTask extends Task
{

    function handle($data)
    {
        var_dump($data);
        return true;
    }
}