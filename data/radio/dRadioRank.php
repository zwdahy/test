<?php
/**
 *
 * 电台排行榜的data层
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
class dRadioRank extends dRadio{
	public $table_field = '`uid`,`url`,`upuid`,`type`,`uptime`';
	public $table_name = 'radio_rank_blacklist';

	/**
	 * 更新全部电台收听排行榜缓存
	 * @param bool $fromdb
	 */
	public function updateListenRank($fromdb = false){
		//取前一天的排行榜
		$page = 1;
		//@test 暂时没数据 30天前的替换
		//$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
		$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-60,date('Y')));
		$url = sprintf(RADIO_TOP10,$date,50,$page);
		$json_result = $this->curlGetData($url, 3);
		$aResult = json_decode($json_result,true);
		if(empty($aResult['rs'])){
			//如果前一天无数据再往前推一天
			$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-2,date('Y')));
			$url = sprintf(RADIO_TOP10,$date,50,$page);
			$json_result = $this->curlGetData($url, 3);
			$aResult = json_decode($json_result,true);
		}
		$data = array();
		if(!empty($aResult['rs'])){
			$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
			$maxpage = $aResult['pageCount'];
			for($page=1;$page <= $maxpage;$page++){
				$url = sprintf(RADIO_TOP10,$date,50,$page);
				$json_result = $this->curlGetData($url, 3);
				$aResult = json_decode($json_result,true);
				$aResult = $aResult['rs'];
				foreach($aResult as $value){
					$radioinfo = $objRadioInfo->getRadioInfoByRid(array($value['vradio_id']),$fromdb);
					if($radioinfo['errorno'] == 1 && count($radioinfo['result']) > 0){
						$radioinfo = $radioinfo['result'][$value['vradio_id']];
						$radioinfo['fm'] = strtoupper($radioinfo['fm']);
						$data[$value['vradio_id']] = array('info'=>$radioinfo,'orders' => $value['orders'],'order_change'=>$value['order_change'],'date'=>$date);
					}
				}
			}
		}
		$key = MC_KEY_RADIO_LISTEN_RANK;
		$mc_res = $this->setCacheData($key, $data, MC_TIME_RADIO_LISTEN_RANK);
		return $this->returnFormat(1,$mc_res);
	}

	/**
	 * 获取电台收听排行榜
	 * @param int $num	//获取数量
	 * @return array
	 */
	public function getListenRank($num = 10){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_LISTEN_RANK;
		$data = $this->getCacheData($mc_key);
		$common_mc_key = "data.dradiorank.getlistenrank";
		if ($data){
			$this->setCacheData($common_mc_key, $data,864000);
		}else{
			$data = $this->getCacheData($common_mc_key);
		}
		
		if($num > 0){
			return array_slice($data,0,$num);
		}
		else{
			return $data;
		}
	}

	/**
	 * 更行全部电台收听排行榜（按地区）
	 * @param bool $fromdb
	 */
	public function updateListenRankByProvince($fromdb = false){
		$listenRank = $this->getListenRank(0);
		if(empty($listenRank)){
			$this->updateListenRank($fromdb);
		}
		$listenRank = $this->getListenRank(0);
		if(!empty($listenRank)){
			$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
			$radioList = $objRadioInfo->getRadioList($fromdb);
			$radioList = $radioList['result'];
			if(!empty($radioList)){
				$tmp = array_pop($listenRank);
				$date = $tmp['date'];
				$old_date = date('Y-m-d',strtotime($date)-86400);
				foreach ($radioList as $key => $value){
					if(!empty($value)){
						foreach($value as $rid => $val){
							if(!empty($listenRank[$rid])){
								$tmp_mc_data[$rid] = $listenRank[$rid];
								$orders[$rid] = $listenRank[$rid]['orders'];							
							}
						}
					}
					if(empty($orders)){
						$orders = array();
					}
					if(empty($tmp_mc_data)){
						$tmp_mc_data = array();
					}
					array_multisort($orders,$tmp_mc_data);
					$old_mc_data = $this->getListenRankByPid($key,0,$old_date);
					if(!empty($tmp_mc_data)){
						foreach($tmp_mc_data as $k => $val){
							$mc_data[$val['info']['rid']] = $val;
							$mc_data[$val['info']['rid']]['order'] = $k+1;
							$old_order = !empty($old_mc_data[$val['info']['rid']]['order']) ? $old_mc_data[$val['info']['rid']]['order'] : $k+1;
							$mc_data[$val['info']['rid']]['province_order_change'] = $old_order - ($k+1);
						}
					}

					$mc_key = sprintf(MC_KEY_RADIO_LISTEN_RANK_PID,$key,$date);
					$mc_res = $this->setCacheData($mc_key,$mc_data,MC_TIME_RADIO_LISTEN_RANK_PID);
					if($mc_res === false){
						$tmp_result[] = $key;
					}
					unset($mc_data);
					unset($tmp_mc_data);
					unset($orders);
				}
			}
		}
		if(!empty($tmp_result)){
			$result = implode(',',$tmp_result);
		}
		return $this->returnFormat(1,$result);
	}

	/**
	 * 更行全部电台收听排行榜（按地区）wenda@
	 * @param bool $fromdb
	 */
	public function updateListenRankByProvince2($fromdb = false){
		$listenRank = $this->getListenRank(0);
		if(empty($listenRank)){
			$this->updateListenRank($fromdb);
		}
		$listenRank = $this->getListenRank(0);
		$radioInfo = array();//按省份存放电台信息
		$res = true;
		if(!empty($listenRank)){
			foreach($listenRank as &$v){
				$radioInfo[$v['info']['province_id']][] = $v;
			}
			unset($v);
			//按地区 放入mc
			foreach($radioInfo as $k=>&$v){
				$mc_key = sprintf(MC_KEY_RADIO_LISTEN_RANK_PID_V2,$k);
				$res = $this->setCacheData($mc_key, $v,86400);
			}
			unset($v);
		}else{
			$res = false;//没有抓到数据
		}
		return $this->returnFormat(1,$res);
	}

	/**
	 * 根据地区id获取电台收听排行榜
	 * @param int $num	//获取数量
	 * @param int $pid	//地区id
	 * @return array
	 */
	public function getListenRankByPid($pid,$num = 10,$date = 0){
		if($pid <= 0){
			return false;
		}
		//从缓存中获取
		$mc_key = sprintf(MC_KEY_RADIO_LISTEN_RANK_PID_V2,$pid);
		$common_mc_key = md5("data.dradiorank.getlistenrankbypid".$pid);//备用去掉$date时间限制key
		$data = $this->getCacheData($mc_key);
		if ($data){
			$this->setCacheData($common_mc_key, $data,864000);
		}else{
			$data = $this->getCacheData($common_mc_key);
		}
		if($num > 0){
			return array_slice($data,0,$num);
		}
		else{
			return $data;
		}
	}
//	public function getListenRankByPid($pid,$num = 10,$date = 0){
//		if($pid <= 0){
//			return false;
//		}
//		//从缓存中获取
//		if($date == 0){
//			$date = date('Y-m-d',time()-86400);
//		}
//		$mc_key = sprintf(MC_KEY_RADIO_LISTEN_RANK_PID,$pid,$date);
//		
//		$common_mc_key = md5("data.dradiorank.getlistenrankbypid".$pid);//备用去掉$date时间限制key
//		$data = $this->getCacheData($mc_key);
//		if ($data){
//			$this->setCacheData($common_mc_key, $data,259200);
//		}else{
//			$data_old = $data;
//			$data = $this->getCacheData($common_mc_key);
//		}
//		if($num > 0){
//			return array_slice($data,0,$num);
//		}
//		else{
//			return $data;
//		}
//	}

	/**
	 *
	 * 获取微电台活跃榜数据
	 * @param $day_key
	 * @param $type
	 */
	public function getActiveRank($args){
		//$day_key = '2012-1-4';
		$day_key = $args['day_key'];
		$type = $args['type'];
		$url = sprintf(RADIO_ACTIVE_RANK,$day_key,$type);
		$aResult = json_decode($this->curlGetData($url, 1),true);
		return $aResult;
	}

	/**
	 * 更新微电台用户活跃榜数据
	 * @param bool $fromdb
	 */
	public function updateActiveUserRank($fromdb = false){
		$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-1,date('Y')));
		$url = sprintf(RADIO_ACTIVE_RANK,$date,'user');
		$aResult = json_decode($this->curlGetData($url, 1),true);
		$data = array();
		if(empty($aResult['rs'])){
			$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-2,date('Y')));
			$url = sprintf(RADIO_ACTIVE_RANK,$date,'user');
			$aResult = json_decode($this->curlGetData($url, 1),true);
		}
		if(!empty($aResult['rs'])){
			foreach($aResult['rs'] as $value){
				$uids[] = $value['uid'];
				$rids[] = $value['vradio_id'];
			}
			$userInfo = $this->getUserInfoByUid($uids,$fromdb);
			$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
			$radioInfo = $objRadioInfo->getRadioInfoByRid($rids,$fromdb);
			$radioInfo = $radioInfo['result'];
			foreach($aResult['rs'] as $value){
				if(!empty($radioInfo[$value['vradio_id']]) && !empty($userInfo[$value['uid']])){
					$data[$value['uid']]['userinfo'] = $userInfo[$value['uid']];
					$data[$value['uid']]['radioinfo'] = $radioInfo[$value['vradio_id']];
				}				
			}
		}
		$mc_key = MC_KEY_RADIO_ACTIVE_USER_RANK;
//		print_r($data);exit;
		$mc_res = $this->setCacheData($mc_key,$data,MC_TIME_RADIO_ACTIVE_USER_RANK);
		return $mc_res;
	}

	/**
	 * 获取微电台的用户活跃榜数据
	 */
	public function getActiveUserRank($num = 10,$cuid = 0){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_ACTIVE_USER_RANK;
		$data = $this->getCacheData($mc_key);
//		print '<pre>';
//		print_r($data);
//		exit;
		$common_mc_key = 'data.dradiorank.getactiveuserrank';
		if ($data){
			$this->setCacheData($common_mc_key, $data,259200);//每次种缓存都是三天
		}else{
			$data = $this->getCacheData($common_mc_key);//备胎
		}
		//排行榜用户id
		$uids = array_keys($data);
//		print '<pre>';
//		print_r($data);
//		exit;
		//不能上排行榜的黑名单
		$blackList = $this->getRankBlackList();
		$blackList = $blackList['result'];
		foreach($blackList as $k=>&$v){
			$rank_black_list_uids[]=$v['uid'];
		}
		unset($blackList);
//		print '<pre>';
//		print_r($uids);
//		exit;
		if($num > 0){
			//新增过滤掉黑名单中的uid用户黑名单
			$objdRadioBlack = clsFactory::create(CLASS_PATH.'/data/radio', 'dRadioBlack', 'service');
			$aList = $objdRadioBlack->getAllBlackList();
			$radio_black_list_uids=$aList['result'];
		}
//		print '<pre>';
//		print_r($radio_black_list_uids);
//		exit;
		$uids=array_diff($uids,$rank_black_list_uids,$radio_black_list_uids);
//		$tmp1=array_intersect($uids,$rank_black_list_uids);
//		$tmp2=array_intersect($uids,$radio_black_list_uids);
//		$tmp=array_merge($tmp1,$tmp2);
//		print '<pre>';
//		print_r($uids);
//		exit;
		$uids = array_slice($uids,0,$num);
		$dPerson = clsFactory::create(CLASS_PATH . "data", "dPerson", "service" );
		//die($cuid);
		if($cuid > 0){
			$res=$dPerson->getRelation2($cuid,$uids);
			$res=$res['result'];
			if(!empty($res)){
				foreach($res as $v){
					$relation[]=$v['id'];
				}
			}
		}
		unset($res);
//		print '<pre>';
//		print_r($relation);
//		exit;
		foreach($uids as $v){
			$res[$v]=$data[$v];
			if(!empty($relation)&&in_array($v,$relation)){
				$res[$v]['userinfo']['relation']=1;
				continue;
			}
			$res[$v]['userinfo']['relation']=0;
		}
//		print '<pre>';
//		print_r($res);
//		exit;
		return $res;
	}
//public function getActiveUserRank($num = 10,$cuid = 0){
//		//从缓存中获取
//		$mc_key = MC_KEY_RADIO_ACTIVE_USER_RANK;
//		$data = $this->getCacheData($mc_key);
//		$common_mc_key = 'data.dradiorank.getactiveuserrank';
//		if ($data){
//			$this->setCacheData($common_mc_key, $data,259200);//每次种缓存都是三天
//		}else{
//			$data = $this->getCacheData($common_mc_key);//备胎
//		}
//		
//		$uids = array_keys($data);
//		
//		$blackList = $this->getRankBlackListByUid($uids);
//		$blackList = $blackList['result'];
//		if(!empty($blackList)){
//			$blackList_type = 1;
//			foreach($data as $key => $value){
//				if(!empty($blackList[$key][$blackList_type])){
//					unset($data[$key]);
//				}
//			}
//			if($cuid > 0){
//				$uids = array_keys($data);
//			}
//		}
//		if($num > 0){
//			//新增过滤掉黑名单中的uid
//			$objdRadioBlack = clsFactory::create(CLASS_PATH.'/data/radio', 'dRadioBlack', 'service');
//			$aList = $objdRadioBlack->getAllBlackList();
//			if(1 == $aList['errorno'] && count($aList['result']) > 0){
//				//去除uids中包含黑名单的id
//				$tem_black = $aList['result'];
//				foreach($data as $key=>$val){
//					if(in_array($key,$tem_black)){
//						unset($data[$key]);
//					}
//				}
//			}
//			
//			$data = array_slice($data,0,$num);
//		}
//		$dPerson = clsFactory::create(CLASS_PATH . "data", "dPerson", "service" );
//		if($cuid > 0){
//			//调用接口获取
//			$args = array(
//				'uid'  => $cuid,
//				'fuids' => implode(',',$uids)
//			);
//			//接口安全调用参数
//			$args['appid'] = RADIO_SOURCE_APP_ID;
//			$aRelation = $dPerson->newGetUserRelation($args);
//			if($aRelation!== false && $aRelation['one2many'] !== false){
//				//拼装关注数据
//				foreach($data as &$data_val){
//					$data_val['userinfo']['relation'] = $aRelation['one2many'][$data_val['userinfo']['uid']] == true ? true : false;
//				}
//			}
//		}
//		return $data;
//	}

	/**
	 * 更新dj活跃榜缓存
	 * @param bool $fromdb
	 */
	public function updateActiveDjRank($fromdb = false){
		$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-3,date('Y')));
		$url = sprintf(RADIO_ACTIVE_RANK,$date,'dj');
		$aResult = json_decode($this->curlGetData($url, 1),true);
		$data = array();
		if(empty($aResult['rs'])){
			$date = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-2,date('Y')));
			$url = sprintf(RADIO_ACTIVE_RANK,$date,'dj');
			echo $url;exit;
			$aResult = json_decode($this->curlGetData($url, 1),true);
		}
		if(!empty($aResult['rs'])){
			foreach($aResult['rs'] as $value){
				$uids[] = $value['uid'];
				$rids[] = $value['vradio_id'];
			}
			$userInfo = $this->getUserInfoByUid($uids,$fromdb);
			$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
			$radioInfo = $objRadioInfo->getRadioInfoByRid($rids,$fromdb);
			$radioInfo = $radioInfo['result'];
			foreach($aResult['rs'] as $value){
				$data[$value['uid']]['userinfo'] = $userInfo[$value['uid']];
				$data[$value['uid']]['radioinfo'] = $radioInfo[$value['vradio_id']];
			}
		}
		//print_r($data);exit;
		$mc_key = MC_KEY_RADIO_ACTIVE_DJ_RANK;
		$mc_res = $this->setCacheData($mc_key,$data,MC_TIME_RADIO_ACTIVE_DJ_RANK);
		return $mc_res;
	}

	/**
	 * 获取微电台的DJ活跃榜数据
	 */
	public function getActiveDjRank($num = 10,$cuid = 0){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_ACTIVE_DJ_RANK;
		$data = $this->getCacheData($mc_key);
		$common_mc_key = "data.dradiorank.getactivedjrank";
		if ($data){
			$this->setCacheData($common_mc_key, $data,259200);
		}else{
			$data = $this->getCacheData($common_mc_key);//备胎
		}
		if($num > 0){
			$data = array_slice($data,0,$num);
//			echo '<pre>';
//			print_r($data);
//			exit;
			for($i=0;$i<$num;$i++){
				$dj_info[$i]['dj_info']['uid']=$data[$i]['userinfo']['id'];
				$dj_info[$i]['dj_info']['name']=$data[$i]['userinfo']['name'];
				$dj_info[$i]['dj_info']['profile_image_url']=$data[$i]['userinfo']['profile_image_url'];
				$dj_info[$i]['dj_info']['link_url']=$data[$i]['userinfo']['link_url'];
				$dj_info[$i]['dj_info']['user_type']=$data[$i]['userinfo']['user_type'];
				//添加关注关系
				if($cuid > 0){
					$dPerson = clsFactory::create(CLASS_PATH . "data", "dPerson", "service" );
					$res=$dPerson->getRelation2($cuid,array($data[$i]['userinfo']['id']));
					if(!empty($res['result'])){
						$dj_info[$i]['dj_info']['relation'] = 1;
					}else{
						$dj_info[$i]['dj_info']['relation'] = 0;
					}
				}else{
					$dj_info[$i]['dj_info']['relation'] = 0;
				}
				$dj_info[$i]['radio_info']['rid']=$data[$i]['radioinfo']['rid'];
				$dj_info[$i]['radio_info']['domain']=$data[$i]['radioinfo']['domain'];
				$dj_info[$i]['radio_info']['province_spell']=$data[$i]['radioinfo']['province_spell'];
				$dj_info[$i]['radio_info']['img_path']=$data[$i]['radioinfo']['img_path'];
				$dj_info[$i]['radio_info']['name']=$data[$i]['radioinfo']['name'];
				$dj_info[$i]['radio_info']['fm']=$data[$i]['radioinfo']['fm'];
				$dj_info[$i]['radio_info']['radio_url']=$data[$i]['radioinfo']['radio_url'];
			}
		}
			/*
			$res=$dPerson->getRelation2($cuid,$uids);
			if($res['error_code']==1){//是否成功请求接口
				foreach($res['result'] as &$v){
					$tmp[]=$v['id'];
				}
				unset($v);
			}
			foreach($data as &$v){
				if(in_array($v['userinfo']['id'],$tmp)){
					$v['userinfo']['relation'] = 1;
					continue;
				}
				$v['userinfo']['relation'] = 0;
			}
			unset($v);

		}
		*/
//			echo '<pre>';
//			print_r($dj_info);
//			exit;
		return $dj_info;
	}


	/**
	 * 获取电台收藏排行榜
	 * @param int $num		//获取个数
	 */
	public function getCollectionRank($num = 10){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_COLLECTION_TOP10;

		$data = $this->getCacheData($mc_key);
		$common_mc_key = md5('data.dradiorank.getcollectionrank');
		if ($data){
			$this->setCacheData($common_mc_key, $data,259200);
		}else{
			$data =$this->getCacheData($common_mc_key);
		}
		if($num > 0){
			return array_slice($data,0,$num);
		}
		else{
			return $data;
		}
	}

	/**
	 * 更新电台收藏排行榜缓存
	 * @param bool $fromdb
	 */
	public function updateCollectionRank($fromdb = false){
		$url = RADIO_COLLECTION_TOP10;
		$json_result = $this->curlGetData($url, 3);
		$aResult = json_decode($json_result,true);
		$aResult = $aResult['result'];
		$data = array();
		if(empty($aResult)){
			return false;
		}
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		foreach($aResult as $value){
			$radioinfo = $objRadioInfo->getRadioInfoByRid(array($value['vid']),$fromdb);
			if($radioinfo['result'][$value['vid']]['online'] == '1'){
				$radioinfo = $radioinfo['result'][$value['vid']];
				$radioinfo['fm'] = strtoupper($radioinfo['fm']);
				$data[] = array('info'=>$radioinfo,'counts'=>$value['value']);
			}
		}
		$mc_key = MC_KEY_RADIO_COLLECTION_TOP10;
		//print_r($data);exit;
		$mc_res = $this->setCacheData($mc_key, $data, MC_TIME_RADIO_COLLECTION_TOP10);
		return $mc_res;
	}

	/**
	 * 更新影响力排行榜
	 * @param bool $fromdb
	 */
	public function updateInfluenceRank($fromdb = false){
		$type = array('day','week','month');
		foreach($type as $value){
			$url = sprintf(RADIO_INFLUENCE_RANK,$value,1);
			$json_result = $this->curlGetData($url, 3);
			$aResult = json_decode($json_result,true);
			if(!empty($aResult['list'])){
				$aResult = $aResult['list'];
//				print_r($aResult);
//				exit;
				foreach ($aResult as &$val){
					$val['order_change'] = $val['first'] - $val['second'];
					if($val['order_change']>0){
						$val['status'] = 'down';
					}else{
						$val['status'] = 'up';
					}
					$val['order_change'] = abs($val['order_change']);
					//添加电台信息
					$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
					$tmp = $objRadioInfo->getRadioByUid(array($val['uid']));
					if($tmp['errorno']==1){
						$val['radio_info'] = $tmp['result'][$val['uid']];
					}else{
						$val['radio_info'] = array();
					}
				}
				unset($val);
//				print_r($aResult);
//				exit;
				if($value == 'day'){
					$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_DAY;
					$mc_time = MC_TIME_RADIO_INFLUENCE_RANK_DAY;
				}
				elseif($value == 'week'){
					$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_WEEK;
					$mc_time = MC_TIME_RADIO_INFLUENCE_RANK_WEEK;
				}
				elseif($value == 'month'){
					$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_MONTH;
					$mc_time = MC_TIME_RADIO_INFLUENCE_RANK_MONTH;
				}
				$mc_res = $this->setCacheData($mc_key,$aResult,$mc_time);
				if($mc_res == false){
					$mc_error[] = $value;
				}
			}
		}
		if(!empty($mc_error)){
			return implode(',',$mc_error);
		}
		return true;
	}

	/**
	 * 获取影响力榜
	 * @param string $type	//获取类型（日day，周week，月month）
	 * @param int $num		//获取个数
	 */
	public function getInfluenceRank($num = 10,$cuid = 0){
		$dPerson = clsFactory::create(CLASS_PATH . "data", "dPerson", "service" );
		$type = array('day','week','month');
		foreach($type as $value){
			if($value == 'day'){
				$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_DAY;
			}
			elseif($value == 'week'){
				$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_WEEK;
			}
			elseif($value == 'month'){
				$mc_key = MC_KEY_RADIO_INFLUENCE_RANK_MONTH;
			}
			$mc_data = $this->getCacheData($mc_key);
			
			$common_mc_key = md5('data.dradiorank.getInfluenceRank'.$value);
			if ($mc_data){
				$this->setCacheData($common_mc_key, $mc_data,259200);
			}else{
				$mc_data =$this->getCacheData($common_mc_key);
			}
			
			if($num > 0){
				$data[$value] = array_slice($mc_data,0,$num);
			}
			else{
				$data[$value] = $mc_data;
			}
			foreach($data[$value] as $k=>$v){
				if($cuid > 0){
					$res=$dPerson->getRelation2($cuid,array($v['uid']));
					if(!empty($res['result'])){
						$data[$value][$k]['relation'] = 1;
					}else{
						$data[$value][$k]['relation'] = 0;
					}
				}else{
					$data[$value][$k]['relation'] = 0;
				}
//				print_r($res);
//				exit;
			}
//			if($cuid > 0){
//				$uids = array();
//				foreach ($data[$value] as $val){
//					$uids[] = $val['uid'];
//				}
//				//调用接口获取
//				$args = array(
//					'uid'  => $cuid,
//					'fuids' => implode(',',$uids)
//				);
//				//接口安全调用参数
//				$args['appid'] = RADIO_SOURCE_APP_ID;
//				$aRelation = $dPerson->newGetUserRelation($args);
//				if($aRelation!== false && $aRelation['one2many'] !== false){
//					//拼装关注数据
//					foreach($data[$value] as &$data_val){
//						$data_val['relation'] = $aRelation['one2many'][$data_val['uid']] == true ? true : false;
//					}
//				}
//			}
		}
//		echo '<pre>';
//		print_r($data);
//		exit;
		return $data;
	}

	/**
	 * 获取黑名单列表
	 * @param bool $fromdb
	 */
	public function getRankBlackList($fromdb = false){
		$mc_key = MC_KEY_RADIO_RANK_BLACKLIST;
		$data = $this->getCacheData($mc_key);
		if($data == false || $fromdb == true){
			$data = $this->dbRead(array('type'=>1));
			if($data === false){
				return $this->returnFormat('RADIO_00003');
			}
			$this->setCacheData($mc_key,$data,MC_TIME_RADIO_RANK_BLACKLIST);
		}
		return $this->returnFormat(1,$data);
	}
//	public function getRankBlackList($fromdb = false){
//		$mc_key = MC_KEY_RADIO_RANK_BLACKLIST;
//		$data = $this->getCacheData($mc_key);
//		if($data == false || $fromdb == true){
//			$db_res = $this->dbRead();
//			if($db_res === false){
//				return $this->returnFormat('RADIO_00003');
//			}
//			$data = array();
//			foreach ($db_res as $value){
//				$uids[] = $value['uid'];
//			}
//			$tmp_info = $this->getRankBlackListByUid($uids,$fromdb);
//			$tmp_info = $tmp_info['result'];
//			foreach($db_res as $value){
//				$data[$value['type']][$value['uid']] = $tmp_info[$value['uid']][$value['type']];
//			}
//			$this->setCacheData($mc_key,$data,MC_TIME_RADIO_RANK_BLACKLIST);
//		}
//		return $this->returnFormat(1,$data);
//	}

	/**
	 * 根据用户id获取黑名单用户信息
	 * @param array $uids
	 * @param bool $fromdb
	 */
	public function getRankBlackListByUid($uids,$fromdb = false){
		if(empty($uids) || !is_array($uids)){
			return $this->returnFormat(-4);
		}
		$data = array();
		if($fromdb === false){
			foreach($uids as $key => $value){
				$mc_key = sprintf(MC_KEY_RADIO_RANK_BLACKLIST_UID,$value);
				$data[$value] = $this->getCacheData($mc_key);
				if(!empty($data[$value])){
					unset($uids[$key]);
				}
			}
		}
		if(!empty($uids)){
			$db_res = $this->dbRead(array('uid' => $uids),array('type ASC'));
			if($db_res === false){
				return $this->returnFormat('RADIO_00003');
			}
			foreach($db_res as $value){
				$data[$value['uid']][$value['type']] = $value;
			}
			foreach ($uids as $value){
				$mc_key = sprintf(MC_KEY_RADIO_RANK_BLACKLIST_UID,$value);
				$this->setCacheData($mc_key,$data[$value],MC_TIME_RADIO_RANK_BLACKLIST_UID);
			}
		}
		return $this->returnFormat(1,$data);
	}

	/**
	 * 黑名单列表
	 * @param array $where
	 * @param string $postfixArgs 排序以及分页
	 * @return array
	 */
	public function getRankBlack($where = array(), $postfixArgs = array()) {
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
	public function addRankBlack($data) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbInsert($data);
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$this->getRankBlackList(true);
		return $this->returnFormat(1, array('uid' => $data['uid']));
	}

	/**
	 * 删除黑名单
	 * @param array $where 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delRankBlack($where) {
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
		$uid = $where['uid'];
		if($uid > 0){
			$mc_key = sprintf(MC_KEY_RADIO_RANK_BLACKLIST_UID,$uid);
			$this->setCacheData($mc_key,array(),MC_TIME_RADIO_RANK_BLACKLIST_UID);
		}
		return $this->returnFormat(1);
	}


	/**
	 * 设置Dj活跃榜数据
	 */
	public function setDjRank($args,$fromdb=false){
		if(empty($args) || !is_array($args)){
			$this->returnFormat(-4);
		}

		foreach($args as $key=>$val){
			$uids[] = $val['uid'];
			$rids[] = $val['vradio_id'];
		}
		$userinfo = $this->getUserInfoByUid($uids,$fromdb);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		$radioInfo = $objRadioInfo->getRadioInfoByRid($rids,$fromdb);
		$radioInfo = $radioInfo['result'];
		$data = array();
		foreach($args as $val){
			$data[$val['uid']]['userinfo'] = $userinfo[$val['uid']];
			$data[$val['uid']]['radioinfo'] = $radioInfo[$val['vradio_id']];
		}

		$aResult = $this->getActiveDjRank(0);
		if(!empty($aResult)){
			foreach($aResult as $key => $value){
				if(empty($data[$key])){
					$data[$key] = $value;
				}
			}
		}

		$mc_key = MC_KEY_RADIO_ACTIVE_DJ_RANK;
		$mc_res = $this->setCacheData($mc_key,$data,MC_TIME_RADIO_ACTIVE_DJ_RANK);
		return $this->returnFormat(1,$mc_res);

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
