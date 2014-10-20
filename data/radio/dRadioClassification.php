<?php
/**
 *
 * 电台信息的data层
 *
 * @package
 * @author 张旭<zhangxu5@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
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
class dRadioClassification extends dRadio{
	public $table_field = "`classification_id`,`classification_name`,`sort`";
	public $table_name = "radio_classification";
	/**
	 * 添加电台分类
	 * @param array $args uid,url,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addClassification($data) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		if($this->getMaxSort()>0){
			$data['sort'] = $this->getMaxSort()+1;
		}else{
			$data['sort'] = 1;
		}
		
		$db_res = $this->dbInsert($data);
		
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		//更新权限名单列表缓存
		$this->getClassificationList(true);	
		return $this->returnFormat(1,array('classification_name' => $data['classification_name']));
	}
 
	/**
	 * 根据province_id判断电台信息表中是否不存在该地区分类的电台，如果存在则不删除，如果不存在则删除
	 * @param int $pid
	 * @return array
	 */
	public function delRadioClassification($cid){
	
			if(empty($cid)) {
				return $this->returnFormat('RADIO_D_CK_00001');
			}
			$sql = "DELETE FROM ".$this->table_name." WHERE `classification_id` = ".$cid;
			$db_res = $this->_dbWriteBySql($sql);

			if(false == $db_res) {
				return $this->returnFormat('RADIO_D_DB_00001');
			}
		
			//更新电台列表缓存，防止地区顺序改变
			$this->getClassificationList(true);	
			return $this->returnFormat(1);
		
	}

	/**
	 * 获取地区分类中最大排位值
	 * @return int
	 */
	private function getMaxSort(){
		$sql = "SELECT MAX(`sort`) as maxsort FROM ".$this->table_name;
		$areaInfo = $this->_dbReadBySql($sql);

		if(false === $areaInfo) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}

		$maxsort = !empty($areaInfo[0]['maxsort']) ? $areaInfo[0]['maxsort']: 0;
		return $maxsort;
	}

	/**
	 * 获取地区列表
	 * @return array
	 */
	public function getClassificationList($fromdb = false){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_CLASSIFICATION;
		$ClassificationList = $this->getCacheData($mc_key);
		if($ClassificationList == false || $fromdb == true){
			$ClassificationList = $this->dbRead(array(),array("`sort` ASC"));
			if($ClassificationList === false){
				return $this->returnFormat('RADIO_00003');
			}

			$mc_key = MC_KEY_RADIO_CLASSIFICATION;
			$mc_res = $this->setCacheData($mc_key, $ClassificationList, MC_TIME_RADIO_CLASSIFICATION);
		}

		return $this->returnFormat(1,$ClassificationList);
	}

	/**
	 * 编辑电台信息
	 * @param array $set
	 * @param array $where 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setClassification($set, $where = array(),$setcache = true) {
		if(empty($set) || !is_array($set)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbUpdate($set,$where);

		if(false == $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$this->getClassificationList(true);	
/*
		if($setcache == true){
			//更新电台列表缓存，防止地区顺序改变
			$this->getAreaList(true);
			$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
			$dRadioInfo->getRadioList(true);
		}
		*/
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
}
