<?php


namespace Ghf;


class DbQuery
{
    private $tableName;
    private $where = '';
    private $limit = '';
    private $args = [];
    private $order = '';
    private $group = '';
    private $having = '';
    private $db;
    private $index = 0;
    public function __construct(Db $db,$tableName)
    {
        $this->tableName = $tableName;
        $this->db = $db;
    }

    public function where($k,$v){
        $args = func_get_args();
        if(!$this->where){
            $this->where = $this->buildWhere(...$args);
        }else{
            $this->where .= ' AND '.$this->buildWhere(...$args);
        }
        return $this;
    }
    public function orWhere($k,$v){
        $args = func_get_args();
        if(!$this->where){
            $this->where = $this->buildWhere(...$args);
        }else{
            $this->where .= sprintf("(%s) OR (%s)",$this->where,$this->buildWhere(...$args));
        }
        return $this;
    }
    public function order($key,$sort = 'ASC'){
        if(!$this->order){
            $this->order = sprintf('ORDER BY `%s` %s',$key,$sort);
        }else{
            $this->order .= sprintf(',`%s` %s',$key,$sort);
        }
        return $this;
    }

    public function limit($size,$offset = 0){
        $this->limit = sprintf('LIMIT %d,%d',$offset,$size);
        return $this;
    }
    public function get($select = '*'){
        $sql = sprintf('SELECT %s FROM `%s` WHERE 1=1 %s %s %s %s',
            is_string($select) ? $select : implode(',',$select),
            $this->tableName,
            $this->where,
            $this->group,
            $this->having,
            $this->order,
            'LIMIT 1');
        $st = $this->db->query($sql);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $row;
    }
    public function getAll($select = '*'){
        $sql = sprintf('SELECT %s FROM `%s` WHERE 1=1 %s %s %s %s',
            is_string($select) ? $select : implode(',',$select),
            $this->tableName,
            $this->where,
            $this->group,
            $this->having,
            $this->order,
            $this->limit);
        $st = $this->db->query($sql);
        $list = $st->fetchAll(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $list;
    }
    public function delete(){
        if(!$this->where){
            Error::errorMsg(1101,"没有WHERE，禁止删除");
        }
        $sql = sprintf('DELETE FROM `%s` WHERE %s',$this->tableName,$this->where);
        $st = $this->db->query($sql);
        return $st->rowCount();
    }
    public function update($data){
        if(!$this->where){
            Error::errorMsg(1102,"没有WHERE，禁止更新");
        }
        $sets = [];
        foreach ($data as $k => $v){
            $sets[] = sprintf('`%s`=>%s',$k,$this->buildKey($k,$v));
        }
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s %s %s',$this->tableName,implode(',',$sets),$this->where,$this->order,$this->limit);
        $st = $this->db->query($sql);
        return $st->rowCount();
    }



    private function buildKey($k,$v){
        $k = ':'.$k.$this->index;
        $this->index += 1;
        $this->args[$k] = $v;
        return $k;
    }

    private function buildWhere(){
        $args = func_get_args();
        $args[1] = strtolower($args[1]);
        $len = count($args);
        $ret = [];
        switch ($len){
            case 3:
                if($args[1] == 'in'){
                    $ret[] = sprintf("`%s` IN ('%s')",$args[0],implode("','",$args[2]));
                }elseif($args[1] == 'like'){
                    $ret[] = sprintf('`%s` LIKE %%%s%%',$args[0],$args[2]);
                }else{
                    $ret[] = sprintf('`%s`%s:%s',$args[0],$args[1],$this->buildKey($args[0],$args[2]));
                }
                break;
            case 2:
                $ret[] = sprintf('`%s`=:%s',$args[0],$this->buildKey($args[0],$args[1]));
                break;
            case 1:
                foreach($args as $k => $v){
                    $k1 = strtolower($k);
                    if($k1 == 'or'){
                        !is_array($v) && Error::errorMsg(1202,'WHERE 参数错误');
                        $ret[] = str_replace(' AND ',' OR ',$this->buildWhere(...$v));
                    }elseif($k1 == 'and'){
                        !is_array($v) && Error::errorMsg(1202,'WHERE 参数错误');
                        $ret[] = $this->buildWhere(...$v);
                    }elseif(!is_int($k1)){
                        $ret[] = sprintf("`%s`=%s",$k,$this->buildKey($k,$v));
                    }else{
                        !is_array($v) && Error::errorMsg(1202,'WHERE 参数错误');
                        $ret[] = $this->buildWhere(...$v);
                    }
                }
                break;
        }
        return implode(' AND ',$ret);
    }


}