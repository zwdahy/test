<?php
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioBlack extends dRadio{
	public $table_field = '`uid`,`url`,`upuid`,`uptime`';
	public $table_name = 'radio_blacklist';	
	/**
	 * 黑名单列表
	 * @param array $where
	 * @param string $postfixArgs 排序以及分页
	 * @return array
	 */
	public function getBlack($where = array(), $postfixArgs = array()) {
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
	 * 添加黑名单
	 * @param array $args uid,url,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addBlack($data) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbInsert($data);
		
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		//更新黑名单列表缓存
		$this->getAllBlackList(true);	
		return $this->returnFormat(1, array('uid' => $data['uid']));
	}
	
	/**
	 * 删除黑名单
	 * @param array $where 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delBlack($where) {
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
		//更新黑名单列表缓存
		$this->getAllBlackList(true);
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
	 * 获取所有黑名单用户列表
	 * @return array
	 */
	public function getAllBlackList($fromdb = false){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_ALL_BLACK_LIST;
		$blackList = $this->getCacheData($mc_key);
		if($blackList == false || $fromdb == true){
			$blackList = $this->dbRead(array(),array());
			if($blackList === false){
				return $this->returnFormat('RADIO_00003');
			}
			//把所有的uid放到一个数组
			if(count($blackList) >0){
				foreach($blackList as $key=>$val){
					$tem_list[] = $val['uid'];
				}
				$blackList = $tem_list;
			}
			$this->setCacheData($mc_key, $blackList, MC_TIME_RADIO_ALL_BLACK_LIST);
		}
		return $this->returnFormat(1,$blackList);
	}
	
	
}
?>