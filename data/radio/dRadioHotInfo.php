<?php
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioHotInfo extends dRadio{
	public $table_field = '`rid`,`sort`,`upuid`,`uptime`';
	public $table_name = 'radio_hot_info';

	/**
	 * 获得推荐的热门电台信息
	 * @param bool $fromdb	是否从数据库取数据
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getHotRadio($fromdb = false) {
		$mc_key = sprintf(MC_KEY_RADIO_HOT_RADIO);
		$hotRadio = $this->getCacheData($mc_key);
		if($hotRadio == false || $fromdb == true){
			$db_res = $this->dbRead(array(),array('`sort` ASC'));
			if(false === $db_res) {
				return $this->returnFormat('RADIO_D_DB_00001');
			}
			$hotRadio = array();
			foreach($db_res as $value){
				$hotRadio[$value['rid']] = $value;
			}
			$this->setCacheData($mc_key,$hotRadio,MC_TIME_RADIO_HOT_RADIO);
		}
		$rids = array_keys($hotRadio);
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioInfoByRid($rids,$fromdb);		
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
	
}
?>