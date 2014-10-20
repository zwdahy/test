<?php
/**
 * 
 * 电台页面各个区域的对应数据管理
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
 * 'result'  => array(id,type,block_name,block_pic,block_text,upuid,is_del,uptime)
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
class dRadioPage extends dRadio{
	public $table_field = '`id`,`type`,`block_name`,`rid`,`block_pic`,`block_uid`,`block_text`,`upuid`,`is_del`,`start_time`,`end_time`,`extra`,`visable`,`uptime`';
	public $table_name = 'radio_page';
	
	/**
	 * 添加
	 * @param array $arr 二维数组一维索引 id,type,block_name,block_pic,block_text,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRadioPage($arr) {
		if(empty($arr) || !is_array($arr)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$sqlArgs = $this->_makeInsert($this->table_name, $arr);//此函数生成预处理sql
		//print_r($sqlArgs);exit;
		$db_res = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
		$key = sprintf(MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME,$arr['type'],$arr['block_name']);
		$this->setCacheData($key, $arr, 1);
		if($db_res == false){
			return $this->returnFormat('RADIO_D_DB_00001');
		}else{
			return $this->returnFormat(1,$db_res);
		}
	}
	
	/**
	 * 获取 
	 * 查询数据，主要操作辅库
	 * @param	string	$_sql 查询sql语句
	 * @param	array	$_param = array($type,$block_name) 页面id 区域名字
	 * @return	正确 查询结果,
	 * 错误	false；
	 */
	//此处 暂时没用上
	public function getRadioPage($type,$visable=0,$fromdb = false){
		if(!is_numeric($type)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$key = sprintf(MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME,$type);
		$res = $this->getCacheData($key);
		if( empty($res) ||$res==false || $fromdb == true ){
			//从数据库取数据
			$sql="SELECT {$this->table_field} FROM {$this->table_name} WHERE `type`={$type} AND `is_del`=0 AND `visable`={$visable}";
			$res =$this->_dbReadBySql($sql);
			if($res == FALSE){
				return $this->returnFormat('RADIO_0003');
			}
			$this->setCacheData($key, $res, 86400);
		}
		return $this->returnFormat(1,$res);
	}

	//前台使用
	public function getRadioPageInfoByBlockName($type,$block_name,$visable=0,$fromdb = false){
		if(empty($type) || empty($block_name)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		//$fromdb = true;
		$key = sprintf(MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME,$type,$block_name);
		$res = $this->getCacheData($key);
		if( empty($res) ||$res==false || $fromdb == true ){
			//从数据库取数据
			$sql="SELECT {$this->table_field} FROM {$this->table_name} WHERE `type`={$type} AND `block_name`='{$block_name}' AND `is_del`=0 AND `visable`={$visable}";
			$res =$this->_dbReadBySql($sql);
			if($res == FALSE){
				return $this->returnFormat('RADIO_0003');
			}
			$this->setCacheData($key, $res, 86400);
		}
		return $this->returnFormat(1,$res);
	}

	//后台使用
	public function getRadioPageInfoByBlockName2($type,$block_name,$fromdb = false){
		$sql="SELECT {$this->table_field} FROM {$this->table_name} WHERE `type`=? AND `block_name`=? AND `is_del`=0";
		$data[] = $type;
		$data[] = $block_name;
		$res = $this->queryData($sql,$data);
		if($res === FALSE){
			return $this->returnFormat('RADIO_0003');
		}
		return $this->returnFormat(1,$res);
	}

	public function getRadioPageById($id){
		$sql="SELECT {$this->table_field} FROM {$this->table_name} WHERE `id`=?";
		$res = $this->queryData($sql,array($id));
		if($res === FALSE){
			return $this->returnFormat('RADIO_0003');
		}
		return $this->returnFormat(1,$res);
	}

	/**
	 * 删除
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delRadioPage($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$data = array();
		$data[] = $whereArgs['upuid'];
		$data[] = $whereArgs['id'];
		$sql_tmp = "SELECT `type`,`block_name` FROM $this->table_name WHERE `id` = {$whereArgs['id']}";
		$temp = $this->_dbReadBySql($sql_tmp);
		$temp = $temp[0];
		$key = sprintf(MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME,$temp['type'],$temp['block_name']);
		$this->setCacheData($key, $data, 1);
		$sql = "UPDATE $this->table_name SET `is_del` = 1,`upuid` = ? WHERE `id`=?";
		$db_res = $this->operateData($sql,$data);
		if($db_res === false){
			return $this->returnFormat('RADIO_D_DB_00001');
		}else{
			return $this->returnFormat(1,$db_res);
		}
	}
	
	/**
	 * 编辑
	 * @param array $args 
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function updateRadioPage($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$data = array();
		$data[] = $whereArgs['type'];
		$data[] = $whereArgs['rid'];
		$data[] = $whereArgs['block_name'];
		$data[] = $whereArgs['block_pic'];
		$data[] = $whereArgs['block_uid'];
		$data[] = $whereArgs['block_text'];
		$data[] = $whereArgs['upuid'];
		$data[] = $whereArgs['start_time'];
		$data[] = $whereArgs['end_time'];
		$data[] = $whereArgs['extra'];
		$data[] = $whereArgs['visable'];
		$data[] = $whereArgs['id'];
		$sql = "UPDATE {$this->table_name} SET `type`=?,`rid`=?,`block_name`=?,`block_pic`=?,`block_uid`=?,`block_text`=?,`upuid`=?,`start_time`=?,`end_time`=?,`extra`=?,`visable`=? WHERE `id` =?";
		$db_res = $this->operateData($sql,$data);
		//echo $sql;exit;
		$key = sprintf(MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME,$whereArgs['type'],$whereArgs['block_name']);
		$this->setCacheData($key, $data, 1);
		if($db_res === false){
			return $this->returnFormat('RADIO_D_DB_00001');
		}else{
			return $this->returnFormat(1,$db_res);
		}
	}
}
?>
