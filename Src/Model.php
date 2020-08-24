<?php


namespace Ghf;


abstract class Model
{
    abstract function getTable();
    protected $db;
    function __construct()
    {
        $this->db = Db::getCon($this->getConName());
    }

    protected function getConName(){
        return 'default';
    }
    protected function getPK(){
        return 'id';
    }


    /**
     * 查询记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:30
     * @param $id
     * @param string $key
     * @return mixed
     */
    public function find($id,$key = ''){
        return $this->createQuery()->where($key ?: $this->getPK(),$id)->get();
    }

    /**
     * 查询列表
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:41
     * @param $ids
     * @param string $key
     * @return array
     */
    public function findMapByPk($ids,$key = ''){
        $list = $this->createQuery()->where($key ?: $this->getPK(),'in',$ids)->getAll();
        if(!$list){
            return [];
        }
        return array_column($list,null,$key ?: $this->getPK());
    }

    /**
     * 删除记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:29
     * @param $id
     * @param string $key
     * @return int
     */
    public function delete($id,$key = ''){
        return $this->createQuery()->where($key ?: $this->getPK(),$id)->delete();
    }


    /**
     * 更新记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:29
     * @param $id
     * @param $data
     * @param string $key
     * @return int
     */
    public function updateByPk($id,$data,$key = ''){
        return $this->createQuery()->where($key ?: $this->getPK(),$id)->update($data);
    }

    /**
     * 出埃及查询
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:41
     * @return DbQuery
     */
    protected function createQuery(){
        return $this->db->getQuery($this->getTable());
    }
}