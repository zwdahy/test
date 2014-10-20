<?php
/**
 *
 * 电台信息的data层
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
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioArea extends dRadio{
	public $table_field = "`province_id`,`province_name`,`sort`";
	public $table_name = "radio_area";
	/**
	 * 根据province_id添加地区信息
	 * @param int $pid
	 * @return array
	 */
	public function addRadioArea($pid){
		$areainfo = $this->dbRead(array("province_id" => $pid),array("`sort` ASC"));
		if(!empty($areainfo) || $pid == 0){
			return $this->returnFormat(1);
		}
		else{
			global $CONF_PROVINCE;
			$data = array('province_id' => $pid,'province_name' => $CONF_PROVINCE[$pid],'sort'=>$this->getMaxSort()+1);
			if($pid == 1){
				$data['province_name'] = '中国';
			}
			if($pid == 2){
				$data['province_name'] = '网络';
			}
			if(empty($data) || !is_array($data)) {
				return $this->returnFormat('RADIO_D_CK_00001');
			}
			$db_res = $this->dbInsert($data);

			if(false == $db_res) {
				return $this->returnFormat('RADIO_D_DB_00001');
			}


			//更新电台列表缓存，防止地区顺序改变
			$this->getAreaList(true);
			$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
			$dRadioInfo->getRadioList(true);
			return $this->returnFormat(1);
		}
	}

	/**
	 * 根据province_id判断电台信息表中是否不存在该地区分类的电台，如果存在则不删除，如果不存在则删除
	 * @param int $pid
	 * @return array
	 */
	public function delRadioArea($pid){
		$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio','dRadioInfo','service');
		$count = $dRadioInfo->getProvinceCounts($pid);
		if($count > 0){
			return $this->returnFormat(1);
		}
		else{
			if(empty($pid)) {
				return $this->returnFormat('RADIO_D_CK_00001');
			}
			$sql = "DELETE FROM ".$this->table_name." WHERE `province_id` = ".$pid;
			$db_res = $this->_dbWriteBySql($sql);

			if(false == $db_res) {
				return $this->returnFormat('RADIO_D_DB_00001');
			}
			//更新电台列表缓存，防止地区顺序改变
			$this->getAreaList(true);
			$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
			$dRadioInfo->getRadioList(true);
			return $this->returnFormat(1);
		}
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
	public function getAreaList($fromdb = false){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_AREA;
		$AreaList = $this->getCacheData($mc_key);
		if($AreaList == false || $fromdb == true){
			$AreaList = $this->dbRead(array(),array("`sort` ASC"));
			if($AreaList === false){
				return $this->returnFormat('RADIO_00003');
			}

			$mc_key = MC_KEY_RADIO_AREA;
			$mc_res = $this->setCacheData($mc_key, $AreaList, MC_TIME_RADIO_AREA);
		}

		return $this->returnFormat(1,$AreaList);
	}

	/**
	 * 编辑电台信息
	 * @param array $set
	 * @param array $where 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setArea($set, $where = array(),$setcache = true) {
		if(empty($set) || !is_array($set)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbUpdate($set,$where);

		if(false == $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}

		if($setcache == true){
			//更新电台列表缓存，防止地区顺序改变
			$this->getAreaList(true);
			$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
			$dRadioInfo->getRadioList(true);
		}
		return $this->returnFormat(1);
	}

	/**
	 * 获取存在上线电台的地区列表
	 */
	public function getAreaHasOnline($fromdb = false){
		$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$areaList = array();
		//获取全部地区列表
		$tmp_areaList = $this->getAreaList($fromdb);
		$tmp_areaList = $tmp_areaList['result'];
		if(!empty($tmp_areaList)){
			foreach($tmp_areaList as $key => $value){
				$tmp_radiolist = $dRadioInfo->getRadioInfoByPid(array($value['province_id']));
				$tmp_radiolist = $tmp_radiolist['result'][$value['province_id']];
				if(!empty($tmp_radiolist)){
					foreach($tmp_radiolist as $val){
						if($val['online'] == '1'){
							$value['province_name'] = $value['province_name'] == '中国' ? '全国' : $value['province_name'];
							$areaList[$value['province_id']] = $value;
							break;
						}
					}
				}
			}
		}
		return $areaList;
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
