<?php
/**
 * 
 * 电台seek7天换流id的data层
 * 
 * @package 
 * @author 高超6928<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * 
 * 返回的结果数组结构如下
 * 
 * 返回正确的结果数组
 * array(
 * 'errorno'   => 1,
 * 'result'  => array('rid','start_time','end_time','epgid','')
 * )	
 * 
 * 返回错误的结果数组
 * array(
 * 'errorno'   => 错误代码,
 * 'result' => 
 * )
 * 
 */

include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioSeek extends dRadio{
	public $table_field = 'id,start_time,rid,end_time,epgid';
	public $table_name = 'radio_seek';
	
	/**
	 * 添加
	 * @param array $arr 二维数组一维索引 id,start_time,rid,end_time,epgid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addSeek($arr) {
		if(empty($arr) || !is_array($arr)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		foreach($arr as $v){
			$db_res = $this->dbInsert($v);
			}	
		if($db_res == false){
			return $this->returnFormat('RADIO_D_DB_00001');
		}else{
			return $this->returnFormat(1);
		}
	}
	
	/**
	 * 获取
	 * 查询数据，主要操作辅库
	 * @param	string	$_sql 查询sql语句
	 * @param	array	$_param = array("SQL字段1" => "变量值1","SQL字段2" => "变量值2")
	 * @return	正确 查询结果,
	 * 错误	false；
	 */

	public function getSeek($arr,$fromdb = false){
		if(empty($arr) || !is_array($arr)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$day=date("Ymd",$arr['start_time']);
		$today = mktime(0,0,0,date("m"),date("d"),date("Y"));//得到对应的日期
		$tmp = strtotime($day);
    	if($tmp>$today||$tmp<($today-7*86400)){
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$rid=$arr['rid'];
		//从缓存中获取
		$key = sprintf(MC_KEY_RADIO_SEEK,$day,$rid);
		$seekInfo = $this->getCacheData($key);
		if($seekInfo == false || $fromdb == true || empty($seekInfo)){
			//从数据库取数据
			$start_time=$arr['start_time'];
			$sql="SELECT `id`,`start_time`,`rid`,`end_time`,`epgid` FROM {$this->table_name} WHERE `start_time` < '{$start_time}' AND `rid`='{$rid}' ORDER BY `start_time` DESC LIMIT 1";
			$seekInfo =$this->_dbReadBySql($sql);
			if($seekInfo===FALSE){
				return $this->returnFormat('RADIO_0003');
			}
			$seekInfo=$seekInfo[0];
			$this->setCacheData($key, $seekInfo, MC_TIME_RADIO_SEEK);
		}
		return $this->returnFormat(1,$seekInfo);
	}

	/**
	 * 删除
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delSeek($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeDelete($this->_radioSeek, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		return $this->returnFormat(1);
	}
	
	/**
	 * 编辑
	 * @param array $args 
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function updateSeek($data, $where = array()) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat(-4);
		}
		$db_res = $this->dbUpdate($data,$where);
		if($db_res == false){
			return $this->returnFormat(-1);
		}
		return $this->returnFormat(1);
	}

		
	/**
	 * 数据库INSERT操作
	 * @param $data
	 * @return bool
	 */
	public function dbInsert($data){		
		return $this->_dbInsert($this->table_name,$data);
	}
	
	/**
	 * 数据库UPDATE操作
	 * @param array $set
	 * @param array $where
	 */
	public function dbUpdate($set,$where = array()){
		return $this->_dbUpdate($this->table_name,$set,$where);
	}
	
	
	
}
?>
