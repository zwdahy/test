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
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioCollection extends dRadio{
	public $table_field = '`uid`,`rids`';
	public $table_name = 'radio_collection';
	/**
	 * 添加用户收藏电台信息
	 * @param array $args
	 * @return array
	 */
	public function addRadioCollection($args){
		$uid = $args['uid'];
		$rids = $args['rids'];
		if(empty($uid) || empty($rids)){
			return $this->returnFormat(-4);
		}
		$collection = $this->dbRead(array('uid' => $uid));
		
		//存在则执行update操作，不存在执行insert操作
		if(!empty($collection)){
			return $this->updateRadioCollection(array('rids' => $rids),array('uid' => $uid));
		}
		else{			
			return $this->insertRadioCollection($args);
		}
	}
	
	/**
	 * 根据用户id获取用户收藏电台id(支持批量)
	 * @param array $uids		用户id
	 * @param int $flag		是否从数据库提取
	 */
	public function getRadioCollection($uids,$fromdb = false){
		if(empty($uids) || !is_array($uids)){
			return false;
		}
		$collection = array();
		if($fromdb == false){
			foreach($uids as $key => $val){
				$mc_key = sprintf(MC_KEY_RADIO_COLLECTION_RIDS,$val);
				$collection[$val] = $this->getCacheData($mc_key);
				if(!empty($collection[$val])){
					unset($uids[$key]);
				}
			}
		}
		if(!empty($uids)){
			$db_res = $this->dbRead(array('uid' => $uids));
			if($db_res === false){
				return false;
			}
			foreach($db_res as $value){
				$value['rids'] = unserialize($value['rids']);
				$value['rids'] = $value['rids'] == false ? array() : $value['rids'];
				$collection[$value['uid']] = $value;
			}
		}
		
		return $collection;
	}
	
	/**
	 * 更新用户收藏电台信息
	 * @param int $uid
	 * @param string $rids
	 */
	public function updateRadioCollection($set,$where){
		$db_res = $this->dbUpdate($set,$where);		
		if(false == $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}				
		
		// 更新MC
		return $this->returnFormat(1, $this->updateCollectionMc($where['uid'],unserialize($set['rids'])));
	}
	
	/**
	 * 插入用户收藏电台信息
	 * @param array $args
	 */
	public function insertRadioCollection($data){
		$db_res = $this->dbInsert($data);
		if(false == $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
						
		// 更新MC
		return $this->returnFormat(1, $this->updateCollectionMc($data['uid'],unserialize($data['rids'])));
	}
	
	/**
	 * 更新用户收藏电台id缓存
	 */
	public function updateCollectionMc($uid,$rids){
		$key = sprintf(MC_KEY_RADIO_COLLECTION_RIDS,$uid);
		$data = array('uid' => $uid
					,'rids' => $rids);
		return $this->setCacheData($key, $data, MC_TIME_RADIO_COLLECTION_RIDS);
	}
	
	/**
	 * 根据用户id获取收藏列表信息
	 * @param unknown_type $uid
	 */
	public function getCollectionList($uid){
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$collection = $this->getRadioCollection(array($uid));
		$collection_list = array();
		if(!empty($collection)){
			$collection = $collection[$uid];
			$rids = $collection['rids'];
			foreach($rids as $v){
				$radioinfo = $mRadio->getRadioInfoByRid(array($v['rid']));
				if(!empty($radioinfo['result'])){
					$collection_list[$v['rid']] = $radioinfo['result'][$v['rid']];
					//补充dj信息
					$dj_uids=$mRadio->getDjInfoByRid(array($v['rid']));
					$dj_uids=explode(',',$dj_uids['result'][$v['rid']]['uids']);
					$dj_uids=array_slice($dj_uids,0,4);
					$dj_info=array();
					foreach($dj_uids as $v2){
						$dj_info[]=$mRadio->getRadioCardByUid($v2);
					}
					$collection_list[$v['rid']]['dj_info']=$dj_info;
				}
			}
		}
		return $collection_list;
	}
	
	/**
	 * 根据用户id获取用户是否收藏过电台
	 * @param int $uid
	 * @return bool
	 */
	public function hasCollected($uid,$rid){
		if(empty($uid) || empty($rid)){
			return $this->returnFormat(-4);
		}
		$collection = $this->getCollectionList($uid);
		foreach($collection as $v){
			if($v['rid']==$rid){
				return 1;
			}
		}
		return 0;
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
