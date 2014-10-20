<?php
/**
 *
 * 前台管理员的data层
 *
 * @package
 * @author 张旭<zhangxu5@staff.sina.com.cn>
 * @copyright(c) 2012, 新浪网 MiniBlog All rights reserved.
 *
 * 返回的结果数组结构如下
 *
 * 返回正确的结果数组
 * array(
 * 'errorno'   => 1,
 * 'result'  => array()
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
class dRadioPower extends dRadio{
	public $table_field = "`id`,`uid`,`url`,`power`,`upuid`,`uptime`";
	public $table_name = "radio_user_power";

	/**
	 * 管理员列表
	 * @param array $where
	 * @param string $postfixArgs 排序以及分页
	 * @return array
	 */
	public function getPower($where = array(), $postfixArgs = array()) {
		if(!is_array($where) && !is_array($postfixArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$sql = "SELECT ".$this->table_field." FROM ".$this->table_name." WHERE 1";
		if(!empty($where)){
			foreach($where as $key => $val){
				if(is_array($val)){
					if(count($val) > 1){
						$sql .= " AND `".$key."` IN (".mysql_escape_string(implode(',',$val)).")";
					}
					else{
						$sql .= " AND `".$key."` = '".$val."'";
					}
				}
				else{
					$sql .= " AND `".$key."` = '".$val."'";
				}
			}
		}
		if(!empty($postfixArgs)){
			if(!empty($postfixArgs['field'])){
				$sql .= " ORDER BY ".$postfixArgs['field'];
				if(!empty($postfixArgs['order'])){
					$sql .= " ".$postfixArgs['order'];
				}
			}
			if(!empty($postfixArgs['pagesize'])){
				$offset = !empty($postfixArgs['page']) ? ($postfixArgs['page']-1)*$postfixArgs['pagesize'] : 0;
				$sql .= " LIMIT ".$offset.",".$postfixArgs['pagesize'];
			}
			// 计算总数
			$sql_count = "SELECT COUNT(*) AS count FROM ".$this->table_name;						
			$db_count = $this->_dbReadBySql($sql_count);			
		}
		$db_res = $this->_dbReadBySql($sql);		
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		
		if(is_numeric($where['uid'])) {
			return $this->returnFormat(1, $db_res);
		} else {			
			return $this->returnFormat(1, array('count' => $db_count[0]['count'], 'content' => $db_res));
		}
		
	}
	
	
	/**
	 * 添加权限名单
	 * @param array $args uid,url,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addPower($data) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbInsert($data);
		
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		//更新权限名单列表缓存
		$this->getAllPowerList(true);	
		return $this->returnFormat(1, array('uid' => $data['uid']));
	}
	
	/**
	 * 删除权限名单
	 * @param array $where 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delPower($where) {
		if(empty($where) || !is_array($where)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}		
		$sql .= "DELETE FROM ".$this->table_name." WHERE 1";
		foreach($where as $key => $value){
			if(!empty($value) && is_array($value)){
				if(count($value) > 1){
					$sql .= " AND `".$key."` IN (".mysql_escape_string(implode(',',$value)).")";
				}
				else{
					$sql .= " AND `".$key."` = '".$value."'";
				}
			}
			else{
				$sql .= " AND `".$key."` = '".$value."'";
			}			
		}
		$db_res = $this->_dbWriteBySql($sql);
		
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		//更新权限名单列表缓存
		$this->getAllPowerList(true);
		return $this->returnFormat(1);
	}

	/**
	 * 数据库SELECT数据库操作
	 * @param $where
	 * @param $order
	 */
	public function dbRead($where = array(), $order = array()){
		return $this->_dbRead($this->table_name,$this->table_field,$where,$order);
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
	
	/**
	 * 获取所有前台权限用户列表
	 * @return array
	 */
	public function getAllPowerList($fromdb = false){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_POWER_FRONT;
		$uids = $this->getCacheData($mc_key);
		if($uids == false || $fromdb == true){
			$uids = array();
			$args = array(
				'power' => 0
			);
			$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$result = $obj->getPower($args);
			if($result){
				foreach($result['result']['content'] as $v){
					$uids[] = $v['uid'];
				}
				$this->setCacheData($mc_key, $uids, MC_TIME_RADIO_POWER_FRONT);
			}
		}
		return $this->returnFormat(1,$uids);
	}
	
}
?>