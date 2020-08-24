<?php


namespace Ghf;


class Db
{
    protected $pdo;
    public function __construct($conName = 'default')
    {
        $conf = Conf::GetArr('db.'.$conName,[
            'host' => '127.0.0.1',
            'port' => 3306,
            'user' => 'root',
            'passwd' => '',
            'dbname' => '',
            'charset' => 'utf8'
        ]);
        try{
            $pdo = new \PDO(sprintf('mysql:dbname=%s;host=%s:%s'
                    ,$conf['dbname'],$conf['host'],$conf['port'])
                ,$conf['user'],$conf['passwd'],[\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$conf['charset']]);
            $this->pdo = $pdo;
        }catch (\Exception $e){
            Error::errorMsg(10201,"链接数据库失败",$e);
        }

    }


    static function getCon($conName){
        static $selfs = [];
        if(!isset($selfs[$conName])){
            $selfs[$conName] = new self($conName);
        }
        return $selfs[$conName];
    }
    /**
     * 使用事务
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:54
     * @param $func
     * @return bool
     */
    public function begin($func){
        if(!$this->pdo->inTransaction()){
            $this->pdo->beginTransaction();
        }
        try{
            $ret = $func();
            $this->pdo->commit();
            return $ret;
        }catch (\Exception $e){
            $this->pdo->rollBack();
            return false;
        }
    }

    public function getQuery($tableName){
        return new DbQuery($this->pdo,$tableName);
    }
}

