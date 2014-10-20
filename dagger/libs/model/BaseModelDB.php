<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * @abstract        数据操作基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2012/7/6 22:14
 * @editer          shuoshi@ xuyan4@
 * @version         Id: 1.0
 */

class BaseModelDB
{
    /**
     * 指定数据库配
     * @var array
     */
    protected $DBConfig = array();

    /**
     * 数据库名
     * @var string
     */
    protected $DBName;

    /**
     * 数据库连接资源描述符
     * @var resource
     */
    protected $link = null;

    /**
     * 最后一次执行的查询语句
     * @var string
     */
    protected $sql;

    /**
     * 数据库返回数据集行数
     * @var int
     */
    protected $countNum;

    /**
     * 是否开启调试模式
     * @var bool
     */
    protected $debug = null;

    /**
     * 禁止更改字段
     * @var array
     */
    protected $disableField = array();

    /**
     * 可忽略的语句执行错误
     * @var array
     */
    protected $ignoreErrorArr = array();

    /**
     * 数据表表名
     * @var string
     */
    protected $tableName;

    /**
     * 翻页实例
     * @var object
     */
    public $pageModel;

    /**
     * mc缓存实例
     * @var object
     */
    public $memcache;    

    /**
     * 语句执行时间
     * @var int
     */
    private $runTime = 0;

    /**
     * 需要重连接的错误代码
     2006 MySQL server has gone away              mysql服务器主动断开
     2013 Lost connection to MySQL server during query  查询时连接中断
     1317 ER_QUERY_INTERRUPTED     查询被打断
     1046 ER_NO_DB_ERROR     无此数据库
     * @var array
     */
    static private $reConnectErrorArr = array(2006,1317,2013,1046);

    /**
     * @param int $db_id    数据库序号（默认为0）
     */
    public function __construct($DBName = '', $DBConfig = array()) {
        $this->DBName = $DBName;
        $this->DBConfig = $DBConfig;
    }

    /**
     * 设置表名
     * @param string $tableName 表名
     */
    public function setTableName($tableName) {
        $this->tableName = $tableName;
    }

    /**
     * 获取表名
     */
    public function getTableName() {
        return $this->tableName;
    }

    /**
     * 设置分页样式
     */
    public function setPageStyle($pageStyle) {
        is_object($this->pageModel) ? $this->pageModel->setStyle($pageStyle) : '';
    }

    /**
     * 设置禁止修改的字段
     */
    public function setDisableField(array $disable_field) {
        $this->disableField = $disable_field;
    }

    /**
     * 设置或略报错错误号
     */
    public function setIgnoreErrorArr(array $ignoreErrorArr) {
        $this->ignoreErrorArr = $ignoreErrorArr;
    }

    /**
     * 获取返回结果总数
     */
    public function getCountNum() {
        return $this->countNum;
    }

    /**
     * 获取分页器html片段
     */
    public function getPageStr() {
        return is_object($this->pageModel) ? $this->pageModel->getPageStr() : '';
    }

    /**
     *
     */
    public function getPageJump() {
        return is_object($this->pageModel) ? $this->pageModel->getPageJump() : '';
    }

    /**
     * 获取查询出错信息
     */
    public function getErrorInfo() {
        if ($this->link) {
            return $this->link->error;
        } else {
            return '';
        }
    }

    /**
     * 获取查询出错代号
     */
    public function getErrorCode() {
        if ($this->link) {
            return $this->link->errno;
        } else {
            return -1;
        }
    }

    /**
     * 执行查询语句 
     * @param string @sql 需要执行的查询语句
     * @param array $data 查询语句中以'?'替代的变量值
     * @param int $pageSize 每页结果数
     * @param string $master_or_slave 指定从主库还是从库查询
     * @return array
     */
    public function getData($sql, $data = '', $pageSize = '', $master_or_slave = 'slave') {
        if (!is_array($data) && !is_numeric($pageSize)) {
            $pageSize = $data;
            $data = '';
        }
        if (is_numeric($pageSize) && $pageSize > 0) {
            //获取读出记录数（用于翻页计算）
            $count_sql = "SELECT count(*) AS num " . substr($sql, stripos($sql, "from"));
            $count_sql = preg_replace("/\s*ORDER\s*BY.*/i", "", $count_sql);
            $query = $this->_sendQuery($count_sql, $data, $master_or_slave);
            $row = $query->fetch_row();
            defined("DAGGER_DEBUG") && $this->debugResult($row);
            $this->countNum = $row[0];
            $this->pageModel = new BaseModelPage($this->countNum, $pageSize);
            $sql .= $this->pageModel->getLimit();
        }

        $query = $this->_sendQuery($sql, $data, $master_or_slave);
        $arr = array();
        if(!is_object($query)) {
            return $this->_error('数据库返回非资源');
        }
        while($row = $query->fetch_assoc()) {
            empty($row) || $arr[] = $row;
        }
        defined("DAGGER_DEBUG") && $this->debugResult($arr);
        return $arr;
    }

    /**
     * 执行SQL 返回二维数组
     */
    public function getRow($sql, $data = '', $master_or_slave = 'slave') {
        $query = $this->_sendQuery($sql, $data, $master_or_slave);
        if(!is_object($query)) {
            return $this->_error('数据库返回非资源');
        }
        $row = $query->fetch_assoc();
        $row = is_NULL($row) ? array() : $row; 
        defined("DAGGER_DEBUG") && $this->debugResult($row);
        return $row;
    }

    /**
     * 执行SQL 返回二维数组
     */
    public function getFirst($sql, $data= '', $master_or_slave = "slave") {
        $query = $this->_sendQuery($sql, $data, $master_or_slave);
        if(!is_object($query)) {
            return $this->_error('数据库返回非资源');
        }
        $row = $query->fetch_row();
        $row[0] = is_NULL($row[0]) ? '' : $row[0]; 
        defined("DAGGER_DEBUG") && $this->debugResult($row[0]);
        return $row[0];
    }

    /**
     * 插入数据
     * @param array $insert_arr
     * $insert_arr(
     *  'key1' => $value1,
     *  'key2' => $value2,
     *     .........
     *     );
     * @param int delayed  false|true
     * @param array &$result
     * @param string $sqlType
     * @return the number of this->_affected rows
     */
    public function insert($insert_value, $affix = '', &$result = array(), $sqlType = 'INSERT') {
        $sqlType = strtoupper($sqlType) !== 'REPLACE' ? 'INSERT' : 'REPLACE';
        if(!is_array($insert_value) || empty($insert_value)) {
            return $this->_error($sqlType !== 'REPLACE' ? '插入数据有误' : '替换数据有误');
        }
        if (!in_array($affix, array("LOW_PRIORITY", "DELAYED", "HIGH_PRIORITY", "IGNORE"))) {
            $affix = '';
        }
        $inKeyArr = $inValArr = array();
        foreach($insert_value as $key => $value) {
            $this->checkField($key, $value);
            $inKeyArr[] = ' `' . $key . '` ';
            $inValArr[] = ' ? ';
        }
        if(empty($inKeyArr)) {
            return $this->_error($sqlType !== 'REPLACE' ? '插入数据有误' : '替换数据有误');
        }
        $sql = "{$sqlType} {$affix} INTO `" . $this->getTableName() . "` (" . implode(',', $inKeyArr) . ") VALUE (" . implode(',', $inValArr) . ")";
        $this->_sendQuery($sql, array_values($insert_value), 'master', $result);
        defined("DAGGER_DEBUG") && $this->debugResult($result, 'db_affected_num');
        return $result['affected_num'];
    }

    /**
     * 替换数据
     * @param array $replace_value
     * $replace_value(
     *  'key1' => $value1,
     *  'key2' => $value2,
     *     .........
     *     );
     * @param int delayed  false|true
     * @param array &$result
     * @param string $sqlType
     * @return the number of this->_affected rows
     */
    public function replace($replace_value, $affix = '', &$result = array())
    {
        return $this->insert($replace_value, $affix, $result, 'REPLACE');
    }

    /**
     * 更新数据
     * @param array 
     * $update_value(
     *  'key1' => $value1,
     *  'key2' => $value2,
     *     );
     * @param array||string $where
     *
     * @return the number of this->_affected rows
     */
    public function update($update_value, $where, &$result = array()) {
        if (!is_array($update_value)) {
            return $this->_error('更新数据有误');
        }
        if (is_string($where)) {
            $tmp_where = strtolower($where);
            if (!strpos ($tmp_where, "=") && !strpos ($tmp_where, 'in') && !strpos ($tmp_where, 'like')) {
                $this->_error('更新查询条件格式错误');
            }
        } elseif (is_array($where)) {
            $tmp = $whereArr = array();//条件，对应key=value
            foreach ($where as $key => $value) {
                $tmp[] = "`".$key."` = ? ";
                $whereArr[] = $value;
            }
            $whereStr = implode(' AND ', $tmp);
        } else {
            $this->_error('更新查询条件格式错误');
        }
        $upArr = array();
        foreach($update_value as $key => $value) {
            $this->checkField($key, $value);
            if ($key{0} === "#") {// 用于特殊操作。有注入漏洞
                $key = substr($key, 1);
            }
            $upArr[] = ' `' . $key . '` = ? ';
        }
        $sql = "UPDATE `".$this->getTableName()."` SET " . implode(',', $upArr) . " WHERE {$whereStr}";
        $this->_sendQuery($sql, array_merge(array_values($update_value), $whereArr), 'master', $result);
        defined("DAGGER_DEBUG") && $this->debugResult($result, 'db_affected_num');
        return $result['affected_num'];
    }

    /**
     * 删除指定的数据
     * @param string||array $where
     * @return the number of this->_affected rows
     */
    public function delete($where, &$result = array()) {
        if (is_array($where)) {
            $tmp = $whereArr = array();//条件，对应key=value
            foreach ($where as $key => $value) {
                $tmp[] = " `" . $key . "` = ? ";
                $whereArr[] = $value;
            }
            $whereStr = implode(' AND ', $tmp);
        } else {
            $tmp_where = strtolower($where);
            if (!strpos($tmp_where, "=") && !strpos($tmp_where, 'in') && !strpos($tmp_where, 'like')) {
                $this->_error('条件错误');
            }
        }
        $sql = "DELETE FROM `".$this->getTableName()."` WHERE {$whereStr}";
        $this->_sendQuery($sql, $whereArr, 'master', $result);
        defined("DAGGER_DEBUG") && $this->debugResult($result, 'db_affected_num');
        return $result['affected_num'];
    }

    /**
     * 执行给出的查询语句
     * @param string $sql               sql statement
     * @param array &$result            result data
     * @param string $master_or_slave   master db / slave db
     * @return the number of this->_affected rows
     */
    public function exec($sql, &$result = array(), $master_or_slave = 'master') {
        $this->_sendQuery($sql, '', $master_or_slave = 'master', $result);
        $this->debugResult($result, 'db_affected_num');
        return $result['affected_num'];
    }

    /**
     * 获取插入数据id
     */
    public function insertId() {
        $sql = 'SELECT last_insert_id()';
        return $this->getFirst($sql, array(), 'master');
    }

    /**
     * 确保数据库连接
     * @param string $master_or_slave   检查主库还是从库
     * @return void
     */
    protected function checkLink($master_or_slave = 'slave', $reConnect = false) {
        $this->link = BaseModelDBConnect::connectDB($this->DBName, $master_or_slave, $this->DBConfig, $reConnect);
    }

    /**
     * 设定是否对字段进行检测
     * @param string $key       要设定的key
     * @param string $validate  要设定的检测方法
        可能的值包括 (0_date, 1, 0, 1_date)等
     * @return void
     */
    public function setValidate($key, $validate) {
        $this->field_arr[$key]['validate'] = $validate;
    }

    /**
     * 获取查询字段名
     */
    public function getFields() {
        return array_keys($this->field_arr);
    }

    /**
     *
     */
    public function getFieldArr() {
        return $this->field_arr;
    }

    /**
     * 验证字段&&数据
     */
    protected function checkField($key, $value) {
        if (defined('EXTERN')) {
            return true;
        }
        if (substr($key, 0, 1) == '#') {
            $key = substr($key, 1);
        }
        if (empty($this->field_arr[$key])) {
            $this->_error("{$key}：字段不存在", array('field'=>$key, 'table'=>$this->tableName));
        }
        if (in_array($key, $this->disableField)) {
            $this->_error("{$key}：字段禁止修改", array('field'=>$key, 'table'=>$this->tableName));
        }
        $msg = BaseModelValidate::check($value, $this->field_arr[$key]['type'], $this->field_arr[$key]['validate'], $this->field_arr[$key]['max_length']);
        if ($msg !== true) {
            $this->_error("{$this->field_arr[$key]['name']}：" . $msg, array('field'=>$key, 'table'=>$this->tableName));
        }
        return true;
    }

    /**
     * 执行SQL语句
     * @param string $sql 需要执行的语句
     * @param array $data 执行的语句中以'?'替代的变量值
     * @param string $master_or_slave   主从选择master或者slave
     * @param array &$result            result data
     * @return mixed
     */
    private function _sendQuery($sql, $data = '', $master_or_slave = 'slave', &$result = array()) {
        $this->checkLink($master_or_slave);
        $this->setSql($sql, $data);
        if (defined("DAGGER_DEBUG")) {
            $this->runTime = microtime(true);
            BaseModelCommon::debug($this->sql, 'db_sql');
        }
        if(empty($this->sql)) {
            return $this->_error("sql不能为空");        
        }
        $retry = 0;
        do {
            if ($retry) {
                $this->checkLink($master_or_slave, true); 
            }
            $query = $this->link->query($this->sql);
            if(strtoupper(substr(ltrim($this->sql), 0, 6)) !== "SELECT") {
                $result['affected_num'] = $this->link->affected_rows;
            }
            if (in_array($this->link->errno, self::$reConnectErrorArr)) {
                $retry++;
            } elseif ($this->link->errno !== 0) {
                $this->_error();
            } elseif ($query === false && $this->link->errno === 0) {
                //TODO处理不可能的错误
                $retry++;
            } elseif ($retry) {
                $retry++;
            }
        } while ($retry === 1);
        return $query;
    }

    /**
     * 获取查询错误信息
     * @param string $msg
     * @return void
     * @author wangxin3
     **/
    private function _error($msg = null, $data = array()) {
        
        if (empty($msg)) {
            if(defined('QUEUE')) {
                BaseModelMessage::showError($this->link->error, '', $this->link->errno);
            } else {
                throw new BaseModelDBException($this->link->error, $this->link->errno);
            }
        } else {
            BaseModelMessage::showError($msg, $data);
        }
    }

    /**
     * 构造sql语句
     * @param string $sql
     * @param array $data
     * @return void
     */
    protected function setSql($sql, $data = '') {
        $this->sql = $sqlShow = '';
        if (is_array($data) && count($data) > 0) {
            $sqlArr = explode('?', $sql);
            $last = array_pop($sqlArr);
            foreach ($sqlArr as $k => $v) {
                if (!empty ($v)) {
                    if (isset($data[$k])) {
                        if (!is_array($data[$k])) {
                            $value = '"' . $this->link->real_escape_string($data[$k]) . '"';
                        } else {
                            $valueArr = array();
                            foreach ($data[$k] as $val) {
                                $valueArr[] = '"' . $this->link->real_escape_string($val) . '"';
                            }
                            $value = '(' . implode(', ', $valueArr) . ')';
                        }
                        $sqlShow .= $v . $value;
                    }
                } else {
                    $this->_error('sql拼接传参错误! [sql]' . $sql . ' [data]' . implode(',', $data));
                }
            }
            $sqlShow .= $last;
        } else {
            $sqlShow = $sql;
        }
        $this->sql = $sqlShow;
    }

    /**
     * 调试结果
     * @param string $sql
     * @param array $data
     * @return void
     */
    protected function debugResult($result, $type = '') {
        if (defined("DAGGER_DEBUG")) {
            $title = empty($type) ? '查询结果' : '影响条目';
            $this->runTime = microtime(true) - $this->runTime;
            $runTime = sprintf("%0.2f", $this->runTime * 1000) . " ms";
            $arr = array(array('运行时间', $title), array($runTime, $result));
            BaseModelCommon::debug($arr, 'db_sql_result');
        }
    }

    /**
     * 析构释放内存
     */
    public function __destruct() {
        unset($this->tableName);
        unset($this->master_or_slave);
        unset($this->sql);
    }
}
