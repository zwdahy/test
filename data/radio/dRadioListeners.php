<?php
include_once SERVER_ROOT."data/radio/dRadio.php";

class dRadioListeners extends dRadio{
	/**
	 * 
	 * 获取正在收听list
	 * @param unknown_type $rid
	 */
	public function getCurrentListeners($rid, $uid){
		//从缓存中获取
		$dPerson = clsFactory::create(CLASS_PATH.'data','dPerson','service');		
		$key = sprintf(MC_KEY_RADIO_LISTENERS, $rid);
		$aLisnersList = $this->getValueByKey(array($key));
		if($aLisnersList === false){
			return $this->returnFormat('RADIO_00002');
		}
		
		//获取用户信息，获取关注关系
		$aUid = array();
		if(empty($aLisnersList)){
			return $this->returnFormat(1, $aLisnersList);
		}		
		foreach($aLisnersList[$key] as $value){
			array_push($aUid, $value['uid']);
		}
		
		$userInfo = $this->getUserInfoByUid($aUid);
		//var_dump($aUid, $userInfo);
		if(empty($userInfo)){
			return $this->returnFormat('RADIO_00003');
		}
		
		$portrait = array();
		$noportrait = array();
		foreach($userInfo as $key => $value){
			$tmp = explode('/',$value['profile_image_url']);
			$exist_portrait = $tmp[count($tmp)-2];
			if($exist_portrait > 0){
				$portrait[$key] = $value;
			}
			else{
				$noportrait[$key] = $value;
			}
		}
		
		$userInfo = $portrait;
		if(!empty($noportrait)){
			foreach($noportrait as $key => $val){
				$userInfo[$key] = $val;
			}
		}
//		echo '<pre>';
//		print_r($userInfo);
//		exit;
		
		if(empty($uid)){
			foreach($userInfo as $key => $value){
				//拼装数据
				$userInfo[$key]['relation'] = 0;
			}
			return $this->returnFormat(1, array_chunk($userInfo,6));
		}
		//获取关注关系
		foreach($userInfo as &$v){
			$res = $dPerson->getRelation2($uid,array($v['id']));
			if(empty($res['result'])){
				$v['relation'] = 1;
			}else{
				$v['relation'] = 0;
			}
		}
		unset($v);

//		$args = array(
//			'uid'  => $uid,
//			'fuids' => implode(',', $aUid)
//		);
//		//接口安全调用参数
//		$args['appid'] = isset($args['appid'])?$args['appid']:RADIO_SOURCE_APP_ID;
//		$aRelation = $dPerson->newGetUserRelation($args);
//		if($aRelation !== false && $aRelation['one2many'] !== false){
//			//拼装数据
//			foreach($userInfo as $key=>$value){
//				$userInfo[$key]['relation'] = $aRelation['one2many'][$key];
//			}
//		}else{
//			$aError = array(
//				'errmsg' => 'get Listeners failed, get data from newGetUserRelation interface failed',
//				'param'  => implode('|', $args)
//			);
//			$this->writeRadioErrLog($aError, 'RADIO_ERR');
//		}

		return $this->returnFormat(1, array_chunk($userInfo,6));
	}
	

	
	
	/**
	 * 
	 * 更新正在收听list的落地缓存
	 */
	public function updateCurrentListeners(){
		//获取电台list
		$objdRadioInfo = clsFactory::create(CLASS_PATH.'/data/radio', 'dRadioInfo', 'service');
		$aList = $objdRadioInfo->getRadioList();
		if($aList['errorno'] != 1){
			$aErr = array(
				'errno' => $aList['errorno'],
				'errmsg' => 'get radio list failed when update listeners'
			);
			$this->writeRadioErrLog($aErr,'cron_listeners');
			return $this->returnFormat('RADIO_00003');
		}
		$aUidList = array();
		$aKey = array();
		$aValue = array();
		if(!empty($aList['result'])){
			foreach($aList['result'] as $val){
				if(!empty($val)){
					foreach ($val as $value){
						$rid = $value['rid'];
						$url = sprintf(RADIO_RADIO_LISTENERS_LIST, $rid, 30, 1);					
						$aResult = $this->curlGetData($url, 1);
						$aUidList = json_decode($aResult,true);
						if($aUidList['errno'] != 1){
							$aErr = array(
								'errno' => $aUidList['errno'],
								'errmsg' => 'get radio listensers failed from interface! rid ='.$rid
							);
							$this->writeRadioErrLog($aErr,'cron_listeners');
							continue;
						}
						$aListeners = $aUidList['result'];
						//更新缓存
						$key = sprintf(MC_KEY_RADIO_LISTENERS, $rid);
						$aValue[$key] = $aListeners;
					}	
				}
			}	
			//更新落地缓存表,更新缓存
			$result = $this->updateKeyValue($aValue, MC_TIME_RADIO_LISTENERS);			
		}

		return $this->returnFormat(1);
		
		
	}
	
	
	/**
	 * 
	 * 从落地缓存中获取正在收听的所有用户List
	 * @param unknown_type $rid
	 */
	public function getAllListenersByMc($rid){
		//从缓存中获取
		$key = sprintf(MC_KEY_RADIO_LISTENERS, $rid);
		$aLisnersList = $this->getValueByKey(array($key));
		if($aLisnersList === false){
			return $this->returnFormat('RADIO_00002');
		}
		return $this->returnFormat(1, $aLisnersList[$key]);
	}	
	
}
?>
