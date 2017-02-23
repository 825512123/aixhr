<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016-11-1 0001
 * Time: 15:13:47
 */

namespace app\common\model;


use think\Model;

class Base extends Model
{

    /*private static $_instance;

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            $class = get_called_class();
            self::$_instance = new $class();
        }
        return self::$_instance;

    }*/

    /**
     * 比较表字段返回 准确表数据
     * @param $table
     * @param $data
     * @return array
     */
    public function getTableData($table, $data)
    {
        $tableFields = $this->getTableFields($table);
        $res = [];
        foreach($data as $key => $value) {
            if(in_array($key, $tableFields)) {
                $res[$key] = $value;
            }
        }
        return $res;
    }

    /**
     * 获取表字段数组
     * @param $table
     * @return mixed
     */
    public function getTableFields($table = '')
    {
        $tableName = $this->db()->getTable($table);
        $res = $this->db()->getTableInfo($tableName,'fields');
        return $res;
    }

    public function getTableList($where = [], $limit = 6)
    {
        $class = get_called_class();
        $classArr = explode('model\\', $class);
        $table = $classArr[1];
        $res = db($table)->where($where)->limit($limit)->select();
        return $res;
    }
}