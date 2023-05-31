<?php
/*
* @Descripttion: mysql 操作类
* @Author: jiosen <4631458@qq.com>
* @Date: 2023-05-26 17:18:01
*/
namespace Jiosen\Lib;

use PDO;
use PDOException;
use Exception;

class Db
{
    //pdo
    private $pdo;

    //PDOStatement
    private $PDOStatement;

    //表
    private $table;

    //表信息
    private $tableInfo = [];

    //成功数量
    private $numRows = 0;

    //条件
    private $where = [];

    //排序
    private $list_order = '';

    //数量
    private $limit = '';

    //group
    private $group = '';

    //字段
    private $fields = '*';

    //最后的语句
    private $lastSql = '';
    

    //配置
    private $config = [
        'host'=>'127.0.0.1',
        'dbname'=>'test',
        'user'=>'root',
        'password'=>'root',
        'prefix' => ''
    ];

    /**
     * 构造函数
     * @param array $config 配置
     */
    public function __construct($config = [])
    {
        $this->config = array_merge($this->config,$config);

        try {

            $dsn = "mysql:host={$this->config['host']};dbname={$this->config['dbname']};";
            $params = [
                PDO::ATTR_CASE              => PDO::CASE_NATURAL,
                PDO::ATTR_ERRMODE           => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_ORACLE_NULLS      => PDO::NULL_NATURAL,
                PDO::ATTR_STRINGIFY_FETCHES => false,
                PDO::ATTR_EMULATE_PREPARES  => false,
            ];
            $this->pdo = new PDO($dsn, $this->config['user'], $this->config['password'], $params);

        } catch (PDOException $e) {
            throw $e;
        }
    }

    /**
     * 设置表
     * @param string $name 表名
     * @return Db
     */
    public function table($name)
    {
        $this->table = $name;

        $this->clear();

        return $this;
    }

    /**
     * 设置表 带前缀
     * @param string $name 表名
     * @return Db
     */
    public function name($name)
    {
        $this->table = $this->config['prefix'] . $name;

        $this->clear();

        return $this;
    }

    /**
     * 查询一条记录
     * @param string $id 主键
     * @return array
     */
    public function find($id='')
    {

        if($id){
            $pk = $this->getPk();
            if(is_string($pk)){
                $this->where[] = [$pk,'=',$id];
            }
        }

        $this->limit = '0,1';

        $res = $this->select();
        return $res?$res[0]:[];

    }

    /**
     * 查询
     * @param bool $showSql 是否打印语句
     * @return array
     */
    public function select($showSql=false)
    {
        $sql =  "select {$this->fields} from {$this->table} {$this->getWhereSql()}";

        if($this->list_order){
            $sql .= " order by {$this->list_order}";
        }

        if($this->limit){
            $sql .= " limit {$this->limit}";
        }

        if($this->group){
            $sql .= " group by {$this->group}";
        }

        if($showSql){
            return "({$sql})";
        }

        return $this->query($sql);

    }

    /**
     * 组装查询
     * @return array
     */
    public function getWhereSql()
    {

        $where = [];

        foreach ($this->where as $value)
        {
            $where[] = implode(" ",$value);
        }

        if(count($where)==0)
        {
            return '';
        }

        if($where){
            $where = " where " . implode(" and ",$where);
        }

        return $where;
    }

    /**
     * 最后查询语句
     * @return string
     */
    public function getLastSql()
    {
        return $this->lastSql;
    }

    /**
     * 查询字段
     * @param string $fields 字段名
     * @return Db
     */
    public function field($fields='*')
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * 排序
     * @param string $order 排序方式
     * @return Db
     */
    public function order($order)
    {
        $this->list_order = $order;
        return $this;
    }

    /**
     * limit
     * @param int $limit 开始数
     * @param int $limit 查询数量
     * @return Db
     */
    public function limit($limit,$num=0)
    {
        if($num){
            $limit .= ",{$num}";
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * group by
     * @param string $group group
     * @return Db
     */
    public function group($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * 查询数量
     * @return int
     */
    public function count()
    {
        $sql =  "select count(*) as count from {$this->table} {$this->getWhereSql()} ";

        if($this->group){
            $sql .= " group by {$this->group}";
        }

        $sql .= " limit 1";

        $res = $this->query($sql);

        return $res?$res[0]['count']:0;
    }

    /**
     * 插入数据
     * @param array $data 插入数据
     * @param bool $getLastInsID 是否打印最后主键
     * @param bool $replace 是否覆盖
     * @return int|bool
     */
    public function insert($data,$getLastInsID=false,$replace=false)
    {
        if(empty($data))
        {
            return false;
        }

        $insert = $replace ? 'REPLACE' : 'INSERT';
        $keys = implode(",",array_keys($data));
        $values = "'" . implode("','",array_values($data)) . "'" ;
        $sql = "{$insert} INTO {$this->table} ({$keys}) VALUES ($values)";

        $res = $this->pdo->exec($sql);

        if($getLastInsID){
            return $this->pdo->lastInsertId();
        }

        return $res;
    }

    /**
     * 批量插入数据
     * @param array $data 插入数据
     * @param bool $getLastInsID 是否打印最后主键
     * @param bool $replace 是否覆盖
     * @return int|bool
     */
    public function insertAll($data,$getLastInsID=false,$replace=false)
    {
        if(empty($data))
        {
            return false;
        }

        $insert = $replace ? 'REPLACE' : 'INSERT';
        $keys = implode(",",array_keys($data[0]));
        $values = [];

        foreach ($data as $v)
        {
            $values[] = "('" . implode("','",array_values($v)) . "')";
        }

        $sql = "{$insert} INTO {$this->table} ({$keys}) VALUES " . implode(",",array_values($values));

        $res = $this->pdo->exec($sql);

        if($getLastInsID){
            return $this->pdo->lastInsertId();
        }

        return $res;
    }

    /**
     * 更新数据
     * @param array $data 更新数据
     * @param array $where 条件
     * @return bool
     */
    public function update($data,$where=[])
    {
        if(empty($data))
        {
            return false;
        }

        if(empty($where) && empty($this->where)) //预防全部数据修改了
        {
            return false;
        }

        if($where)
        {
            $this->where($where);
        }

        $set = [];

        foreach ($data as $key => $val)
        {
            $set[] = "{$key} = '{$val}'";
        }

        $set = implode(",",$set);

        $sql = "UPDATE {$this->table} SET {$set} {$this->getWhereSql()}";

        try {
            return $this->pdo->exec($sql);
        }catch (PDOException $e) {
            return false;
        }

    }

    /**
     * 删除数据
     * @return int|bool
     */
    public function delete()
    {
        if(empty($this->where))
        {
            return false;
        }

        $sql = "DELETE FROM {$this->table} {$this->getWhereSql()}";

        try {
            return $this->pdo->exec($sql);
        }catch (PDOException $e) {
            return false;
        }
    }

    /**
     * 组装where
     * @param array $arr 条件
     * @return Db
     */
    public function where($arr)
    {
        if(is_array($arr)){
            foreach ($arr as $key => $value)
            {
                if(!is_array($value)){
                    $this->where[] = [$key,'=',"'{$value}'"];
                }elseif (is_array($value) && count($value)==2){
                    switch ($value[0])
                    {
                        case 'in':
                            if(is_array($value[1])){
                                $value[1] = "'" .  implode("','",$value[1]) . "'";
                            }
                            $this->where[] = [$key,$value[0],"({$value[1]})"];
                            break;
                        case 'between':
                            if(is_array($value[1])){
                                $value[1] = implode(" and ",$value[1]);
                            }
                            $this->where[] = [$key,$value[0],$value[1]];
                            break;
                        default :
                            $this->where[] = [$key,$value[0],$value[1]];
                            break;
                    }

                }
            }
        }

        return $this;

    }

    /**
     * 获取主键
     * @param string $table 表名
     * @return string
     */
    public function getPk($table = '')
    {
        $table = $table?$table:$this->table;
        if('' == $table){
            throw new PDOException('缺少table');
        }

        $pk = $this->getTableInfo($table,'pk');

        return $pk;
    }

    /**
     * 获取表信息
     * @param string $tableName 表名
     * @param string $fetch fields type bind pk
     * @return string
     */
    public function getTableInfo($tableName, $fetch = '')
    {

        if (!isset($this->tableInfo[$tableName])) {

            $info = $this->getFields($tableName);
            $fields = array_keys($info);

            $bind   = $type   = [];

            foreach ($info as $key => $val) {
                // 记录字段类型
                $type[$key] = $val['type'];
                if (!empty($val['primary'])) {
                    $pk[] = $key;
                }
            }

            if (isset($pk)) {
                // 设置主键
                $pk = count($pk) > 1 ? $pk : $pk[0];
            } else {
                $pk = null;
            }

            $this->tableInfo[$tableName] = ['fields' => $fields, 'type' => $type, 'pk' => $pk];
        }

        return $fetch ? $this->tableInfo[$tableName][$fetch] : $this->tableInfo[$tableName];
    }

    /**
     * 取得数据表的字段信息
     * @param string $tableName 表名
     * @return array
     */
    public function getFields($tableName)
    {
        $sql    = 'SHOW COLUMNS FROM ' . $tableName;
        $result    = $this->query($sql);

        $info   = [];

        if ($result) {
            foreach ($result as $key => $val) {
                $val                 = array_change_key_case($val);
                $info[$val['field']] = [
                    'name'    => $val['field'],
                    'type'    => $val['type'],
                    'notnull' => 'NO' == $val['null'],
                    'default' => $val['default'],
                    'primary' => strtolower($val['key']) == 'pri',
                    'autoinc' => strtolower($val['extra']) == 'auto_increment',
                ];
            }
        }

        return $info;
    }


    /**
     * 查询
     * @param string $sql 语句
     * @param array $vars 绑定参数
     * @return array
     */
    public function query($sql,$vars=[])
    {
        //echo $sql;
        $this->lastSql = $sql;

        $this->PDOStatement = $this->pdo->prepare($sql);

        if($vars){
            $this->bindValue($vars);
        }

        $this->PDOStatement->execute();

        $result = $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);

        $this->numRows = count($result);

        // $this->clear();

        return $result;

    }

    /**
     * 清空条件
     */
    public function clear()
    {
        //清空条件
        $this->where = [];

        //清空排序
        $this->list_order = '';

        //清空limit
        $this->limit = '';
        //清空字段
        $this->fields = '*';
    }

    /**
     * 绑定参数
     */
    protected function bindValue($bind = [])
    {
        foreach ($bind as $key => $val) {
            // 占位符
            $param = is_int($key) ? $key + 1 : ':' . $key;

            if(!$this->PDOStatement->bindValue($param, $val)){
                throw new Exception("Error occurred  when binding parameters '{$param}'");
            }
        }
    }
}