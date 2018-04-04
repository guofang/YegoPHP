<?php
namespace YegoPHP\core;

use YegoPHP\driver\Pdo;

/**
 * 模型基类
 * @see 封装了PDO的数据库增删改查简单模型类
 * @see 暂不支持join语句接口，请自写sql处理
 */
class Model
{
    protected $_table; //表名
    protected $_primary_key = 'id'; //主键
    protected $_filter = ''; //过滤器
    public function __construct(){
        if (!$this->_table){
            $this->_table = strtolower(substr(get_class($this), 0, -5)); //根据模型名推出表名，testModel->test
        }
    }
    /**
     * $where条件数组或条件字符串转换为标准查询条件字符串
     * @param mixed $where 仅支持字符串或一二维数组，不支持更多维数组格式
     * $where = array(
	 * 				'and'=>array(
	 * 					'picture_id = ?'=>1,
	 * 					'between 1 and 2 '=>null,
	 * 					'like %abs%'	=>null
	 * 				),
	 * 				'or'=>array(
	 * 					'user_id    <  ?' =>1,
	 * 					'picture_id = ?'=>3
	 * 				)
	 * 		);
	 * 或
	 * $where = array(
     * 				'picture_id = ?'=>1,
     * 				'between 1 and 2 '=>null,
     * 				'like %abs%'	=>null
	 * 		);
	 * 默认为and条件
	 * 或
	 * $where = 'picture_id = 1'
     * @return string
     */
    protected function _toWhere($where){
        if (empty($where))
            return '';
        $whereStr = ' WHERE ';
        $andWhereStr = $orWhereStr = ''; //初始化
        if (is_string($where))
            return $whereStr . $where;
        if (count($where) === count($where, 1))
        {
            //一维条件数组转换为标准二维条件
            $where = array(
                    'and' => $where
            );
        }
        //针对标准二维条件数组字符串化
        
        if (!empty($where ['and']))
        {
            $andWhereArr = $where ['and'];
            foreach ($andWhereArr as $where_key => $where_val){
                if (NULL === $where_val){
                    //无占位符，不替换
                    array_push($andWhereArr, $where_key);
                }else{
                    array_push($andWhereArr, str_replace('?', $where_val, $where_key));
                }
            }
            $andWhereStr = trim(implode(' AND ', $andWhereArr));
        }
        if (!empty($where ['or']))
        {
            $orWhereArr = $where ['or'];
            foreach ($orWhereArr as $where_key => $where_val){
                if (NULL === $where_val){
                    //无占位符，不替换
                    array_push($orWhereArr, $where_key);
                }else{
                    array_push($orWhereArr, str_replace('?', $where_val, $where_key));
                }
            }
            $orWhereStr = trim(implode(' OR ', $orWhereArr));
        }
        if ($andWhereStr && $orWhereStr)
            $whereStr .= $andWhereStr . ' AND ' . $orWhereStr;
        else
           $whereStr .= $andWhereStr . $orWhereStr;
        return $whereStr;
    }
    /**
     * $order排序依据数组或字符串转换为标准排序字符串
     * @param unknown $order
     * @return string
     */
    protected function _toOrder($order){
        if(empty($order))
            return '';
        $orderStr = ' ORDER BY ';
        if (is_string($order))
            return $orderStr . $order;
        if (!empty($order)){
            $orderStrSuffix = '';
            foreach ($order as $order_key => $order_val){
                $orderStrSuffix .= trim($order_key) . ' ' . strtoupper(trim($order_val)) . ',';
            }
            return $orderStr . trim($orderStrSuffix, ',');
        }
    }
    /**
     * 分页字符串
     * @param unknown $count
     * @param unknown $offset
     * @return string|Ambigous <string, number>
     */
    protected function _toLimit($count, $offset){
        if (is_null($count))
            return '';
        $limitStr = ' LIMIT ';
        if (is_null($offset))
            $limitStr .= intval($count);
        else 
           $limitStr .= intval($offset) . ',' . intval($count);
        return $limitStr;
    }
    /**
     * 将单条数据数组转换为insert字符串
     * @param unknown $data
     * @return string
     */
    protected function _toInsert($data){
        if (empty($data))
            return '';
        $insertFieldStr = ' (' . implode(',', array_keys($data)) . ') ';
        $insertValStr = ' VALUES (' . implode(',', array_values($data)) . ') ';
        return $insertFieldStr . $insertValStr;
    }
    protected function _toUpdate($data){
        if (empty($data))
            return '';
        $updateStr = ' SET ';
        $updateStrSuffix = '';
        foreach ($data as $data_key => $data_val){
            $updateStrSuffix .= $data_key . '=' . $data_val . ',';
        }
        return $updateStr . trim($updateStrSuffix, ',');
    }
    /**
     * 分页返回符合条件的所有查询记录
     * @param string $cols
     * @param string $where
     * @param string $order
     * @param string $count
     * @param string $offset
     */
    public function fetchAll($cols = "*", $where = null, $order = null, $count = null, $offset = null){
        $sql = sprintf("SELECT * FROM `%s` ", $this->_table);
        $sql .= $this->_toWhere($where);
        $sql .= $this->_toOrder($order);
        $sql .= $this->_toLimit($count, $offset);
        $sql = mysql_real_escape_string($sql); //该函数对于标准sql语句防注入通用
        $db = Pdo::getInstance()->prepare($sql);
        $db->execute();
        
        return $db->fetchAll();
    }
    /**
     * 返回符合条件的单条记录
     * @param string $cols
     * @param string $where
     * @param string $order
     */
    public function fetchRow($cols = "*", $where = null, $order = null){
        $sql = sprintf("SELECT * FROM `%s` ", $this->_table);
        $sql .= $this->_toWhere($where);
        $sql .= $this->_toOrder($order);
        $sql = mysql_real_escape_string($sql);
        $db = Pdo::getInstance()->prepare($sql);
        $db->execute();
        
        return $db->fetch();
    }
    /**
     * 插入单条数据记录
     * @param unknown $data
     * @return 受影响的行数
     */
    public function insert($data){
        if (empty($data))
            return FALSE;
        $sql = sprintf("INSERT INTO `%s` ", $this->_table);
        $sql .= $this->_toInsert($data);
        $sql = mysql_real_escape_string($sql);
        $db = Pdo::getInstance()->prepare($sql);
        $db->execute();
        
        return $db->rowCount();
    }
    /**
     * 删除符合条件的记录
     * @param unknown $where
     * @return 受影响的行数
     */
    public function delete($where){
        if (empty($where))
            return FALSE;
        $sql = sprintf("DELETE FROM `%s` ", $this->_table);
        $sql .= $this->_toWhere($where);
        $sql = mysql_real_escape_string($sql);
        return Pdo::getInstance()->exec($sql);
    }
    /**
     * 更新符合条件的记录
     * @param unknown $data
     * @param unknown $where
     * @return 受影响的行数
     * @see 注意如果更新失败返回false，如果未更新(影响行数为0)则返回0
     */
    public function update($data, $where){
        if (empty($data))
            return FALSE;
        $sql = sprintf("UPDATE `%s`", $this->_table);
        $sql .= $this->_toUpdate($data);
        $sql .= $this->_toWhere($where);
        $sql = mysql_real_escape_string($sql);
        return Pdo::getInstance()->exec($sql);
    }
    /**
     * 通用执行自由sql
     * @param unknown $sql
     * @return 返回PDOStatement对象，查询语句结果可遍历读取，增删改语句可rowCount()获取受影响行数
     */
    public function execsql($sql){
        $sql = mysql_real_escape_string($sql);
        return Pdo::getInstance()->query($sql);
    }
    /**
     * 获取最后一条插入记录自增键ID
     * @see 在没有使用AUTO_INCREMENT约束的表中，lastInsertId函数返回NULL或0
     */
    public function getLastInsertId(){
        return Pdo::getInstance()->lastInsertId();
    }
}