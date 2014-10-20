<?php
/**
 *
 * 电台feed的data层
 *
 * @package
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 *
 */

include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioFeed extends dRadio{
	/*
	 * 通过rid和页码获取电台feed
	 * @param string $rid	电台id
	 * @param int $page		页码
	 * @return array
	 */
	public function getFeedListByRid($rid,$page = 1){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}
		if($page == 0){
			for($feed_page=1;$feed_page <= RADIO_FEEDLIST_MAXPAGE;$feed_page++){
				$mc_key[] = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE, $rid, $feed_page);
			}
		}
		else{
			$mc_key[] = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE, $rid, $page);
		}
		//feed总数
		$count_mc_key = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE, $rid, 'count');
		$aFeedList_count = $this->getValueByKey(array($count_mc_key));
		//feed列表
		$aFeedList = $this->getValueByKey($mc_key);
		if($aFeedList === false){
			$this->writeRadioErrLog(array('getFeedListByRid fail! '), 'RADIO_ERR');
			return $this->returnFormat('RADIO_00002');
		}

		$feed_info = array();
		foreach($aFeedList as $value){
			if(empty($value)){
				break;
			}
			else{
				foreach ($value as &$val){
					$val['created_at'] = $this->timeFormat($val['time']);
				}
				$feed_info = array_merge($feed_info,$value);
			}
		}

		$result = array('count' => $aFeedList_count[$count_mc_key],'result' => $feed_info);
		return $result;
	}

	/*
	 * 更新全部电台feed
	 */
	public function updateAllFeed(){
		$objRadioInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioInfo", "service" );
		$radioInfo = $objRadioInfo->getAllOnlineRadio(true);
		$radioInfo = $radioInfo['result'];
		if(empty($radioInfo)){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台列表失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_feed');
			return $this->returnFormat(-1);
		}
		return $this->updateFeedList($radioInfo,RADIO_FEEDLIST_MAXPAGE);
	}


	/*
	 * 根据电台id更新电台feed
	 *
	 * @param string $rid 电台信息列表
	 * @param int $page		feed页码
	 */
	public function updateFeedByRid($rid,$page = 1){
		$objRadioInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioInfo", "service" );
		$aradioInfo = $objRadioInfo->getRadioInfoByRid(array($rid));
		$radioInfo = $aradioInfo['result'];
		if(empty($radioInfo)){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台列表失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_feed');
			return $this->returnFormat(-1);
		}
		return $this->updateFeedList($radioInfo,$page);
	}

	/*
	 * 根据电台信息更新feed信息
	 * @param array $radioinfo 电台信息列表
	 * @param int $page		feed页码
	 */
	public function updateFeedList($radioinfo,$page = 1){
		if(empty($radioinfo) || !is_array($radioinfo)){
			return $this->returnFormat(-9,'param error!');
		}

		foreach($radioinfo as $key => $value){
			$endtime = time();
			$res = $this->updateFeedListCache($value,$page,$endtime);
			if($res['errorno'] == 1 && $res['result'] == false){
				$rids[] = $value['rid'];
				$aErr = array(
					'errno' => '-2',
					'errmsg' => '更新FEED缓存失败，电台id：'.$value['rid'].'|电台名称：'.$value['info']
				);
				$this->writeRadioErrLog($aErr,'cron_update_feed');
			}
		}
		if(!empty($rids)){
			return $this->returnFormat(-1,$rids);
		}
		return $this->returnFormat(1,'update feed succeed!');
	}

	/*
	 * 更新电台feed缓存
	 * @param array $radioinfo 电台信息列表
	 * @param int $maxpage		feed最大页数
	 * @param int $endtime		微博搜索结束时间
	 */
	public function updateFeedListCache($aRadioInfo,$maxpage = 1,$endtime = 0){
		if($endtime > 0){
			$args['endtime'] = $endtime;
		}
		if($aRadioInfo['search_type'] == 1){
			$args['base_app'] = 1;
		}
		preg_match_all('|^#(.*)#$|', $aRadioInfo['tag'], $out);
		if($out[1][0] != ''){
			//精确查找
			$args['q'] = $out[1][0];
			$args['istag'] = 2;
		}
		else{
			//模糊查找
			$args['q'] = $aRadioInfo['tag'];
			$args['istag'] = 1;
		}
		$args['q'] = urlencode($args['q']);
		//feed过滤条件 此处不知道干啥...by wenda 
		if(!empty($aRadioInfo['feed_require'])){
			$feed_require = unserialize($aRadioInfo['feed_require']);
		}else{
			$feed_require = array();
		}
		//$feed_require = unserialize($aRadioInfo['feed_require']);
		//$feed_require = !empty($feed_require) ? $feed_require : array();

		//feed过滤指定的appid  add by baishen
		$feed_require['appid'] = array('1h05rR','5uc6Gr','1Sr2bu','io2FX');

		//测试数据
		//$feed_require = unserialize('a:5:{s:9:"faceicons";s:4:"true";s:9:"bindphone";s:4:"true";s:5:"rtime";s:1:"3";s:6:"mblogs";s:1:"4";s:6:"myfans";s:1:"5";}');

		//按页存储电台feed
		$search_page = 1;
		$feed_list = array();
		$mc_value = array();
		$count = 0;
		for($feed_page=1;$feed_page<=$maxpage;$feed_page++){
			$search_end = false;
			while($search_end == false){
				$args['page'] = $search_page;
				$result = $this->searchMblogByrpc($args);
				usleep(5000);
				if(count($result['statuses']) > 0){
					$record = $this->filterFeed($result['statuses'],$feed_require);
					$feed_list = array_merge($feed_list,$record);
				}
				else{
					//将不足一页数据的微博存入当前页。
					$mc_key = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE,$aRadioInfo['rid'],$feed_page);
					if(!empty($feed_list)){
						$mc_value[$mc_key] = $feed_list;
						$count += count($feed_list);
						$feed_list = array();
					}
					else{
						$mc_value[$mc_key] = array();
					}
					break;
				}
				if(count($feed_list) >= RADIO_FEEDLIST_PAGESIZE){
					$feed_page_content = array_slice($feed_list,0,RADIO_FEEDLIST_PAGESIZE);
					$mc_key = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE,$aRadioInfo['rid'],$feed_page);
					$mc_value[$mc_key] = $feed_page_content;
					$count += count($feed_page_content);
					$feed_list = array_slice($feed_list,RADIO_FEEDLIST_PAGESIZE);
					$search_end = true;
				}
				$search_page++;
			}
		}

		//feed总数
		$mc_key = sprintf(MC_KEY_RADIO_FEEDLIST_PAGE,$aRadioInfo['rid'],'count');
		$mc_value[$mc_key] = $count;
		//更新落地缓存表,更新缓存
		$mc_res = $this->updateKeyValue($mc_value, MC_TIME_RADIO_FEEDLIST_PAGE);
		return $this->returnFormat(1,$mc_res);
	}

	/**
	 * 根据$feed_require过滤$data并返回
	 * @param array $data	需过滤数组
	 * @param array $feed_require	过滤条件数组
	 */
	public function filterFeed($data,$feed_require){
		if(empty($data)){
			return false;
		}
		//获取后台黑名单列表
		$objRadioBlack = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioBlack','service');
		$blacklist = $objRadioBlack->getBlack();
		$blackuids = array();
		if( $blacklist['errorno'] == 1 && count($blacklist['result']['content']) > 0 ){
			foreach($blacklist['result']['content'] as $value){
				$blackuids[] = $value['uid'];
			}
		}
		//如果过滤条件为空，且黑名单为空，直接返回数组$data
		if( empty($feed_require) && empty($blackuids) ){
			return $data;
		}


		foreach($data as $key => $value){
			//删除黑名单用户的微博
			if(in_array($value['user']['id'],$blackuids)){
				unset($data[$key]);
				continue;
			}

			//删除为上传头像用户的微博
			if($feed_require['faceicons']){
				$tmp = explode('/',$value['user']['profile_image_url']);
				if($tmp[count($tmp)-2] <= 0){
					unset($data[$key]);
					continue;
				}
			}
			//删除注册时间小于后台设置天数的用户微博
			if($feed_require['rtime']){
				$nRegTime = strtotime($value['user']['created_at']);
				$nDiff = time() - $nRegTime ;
				if($nDiff < $feed_require['rtime']*24*60*60){
					unset($data[$key]);
					continue;
				}
			}
			//删除微博数小于后台设置的用户微博
			if($feed_require['mblogs'] > 0){
				if($value['user']['statuses_count'] <= $feed_require['mblogs']){
					unset($data[$key]);
					continue;
				}
			}
			//删除粉丝数小于后台设置的用户微博
			if($feed_require['myfans'] > 0){
				if($value['user']['followers_count'] <= $feed_require['myfans']){
					unset($data[$key]);
					continue;
				}
			}
			//删除指定appid的用户微博 add by baishen
			if(!empty($feed_require['appid'])){
				foreach($feed_require['appid'] as $app) {
					if (strstr($value['source'], $app)) {
						unset($data[$key]);
						break;
					}
				}
				continue;
			}
		}
		//删除未绑定手机的用户微博
		if($feed_require['bindphone']){
			$fuids = array();
			foreach ($data as $value){
				if(!in_array($value['user']['id'],$fuids)){
					$fuids[] = $value['user']['id'];
				}
			}
			$binding_info = $this->isBindingMobileMulti(implode(',',$fuids));
			foreach ($data as $key => $value){
				if($binding_info[$value['user']['id']]['binding'] != true){
					unset($data[$key]);
				}
			}
		}

		$data = array_merge($data);
		return $this->formatFeed($data);
	}

	/**
	 * 获取是否存在新feed
	 * @param int $starttime	查询起始时间
	 * @param int $rid
	 * @return array
	 */
	public function checkNewFeed($starttime,$rid){
		if(empty($starttime) || empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  starttime='.$starttime.'&rid='.$rid );
			return $this->returnFormat('RADIO_00001');
		}
		//检查现有缓存中是否存在$starttime之后的feed
		$feedinfo = $this->getFeedListByRid($rid,1);
		if(!empty($feedinfo['result'])){
			foreach($feedinfo['result'] as $value){
				if($value['time'] > $starttime){
					return $this->returnFormat(1,$value['time']);
//					return $this->returnFormat(1);
					break;
				}
			}
		}

		//现有缓存不存在$starttime之后的feed,调取搜索接口
		$objRadioInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioInfo", "service" );
		$aRadioInfo = $objRadioInfo->getRadioInfoByRid(array($rid));
		$aRadioInfo = $aRadioInfo['result'][$rid];
		if(empty($aRadioInfo)){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台信息失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_feed');
			return $this->returnFormat(-1);
		}

		//查询从$starttime到当前时间的范围内的微博
		$args['starttime'] = $starttime;
		$args['endtime'] = time();
		if($aRadioInfo['search_type'] == 1){
			$args['base_app'] = 1;
		}
		preg_match_all('|^#(.*)#$|', $aRadioInfo['tag'], $out);
		if($out[1][0] != ''){
			//精确查找
			$args['q'] = $out[1][0];
			$args['istag'] = 2;
		}
		else{
			//模糊查找
			$args['q'] = $aRadioInfo['tag'];
			$args['istag'] = 1;
		}
		$args['q'] = urlencode($args['q']);
		//feed过滤条件
		$feed_require = unserialize($aRadioInfo['feed_require']);
		$feed_require = !empty($feed_require) ? $feed_require : array();
		//feed过滤指定的appid (蜻蜓fm、优听Radio、JSSDK、找节操) add by baishen
		$feed_require['appid'] = array('1h05rR','5uc6Gr','1Sr2bu','io2FX','5pCbU3');

		//测试数据
		//$feed_require = unserialize('a:5:{s:9:"faceicons";s:4:"true";s:9:"bindphone";s:4:"true";s:5:"rtime";s:1:"3";s:6:"mblogs";s:1:"4";s:6:"myfans";s:1:"5";}');

		//按页存储电台feed
		$search_page = 1;
		$feed_list = array();
		$feed_page=1;
		$search_end = false;
		while($search_end == false){
			$args['page'] = $search_page;
			$result = $this->searchMblogByrpc($args);
			if(count($result['statuses']) > 0){
				$record = $this->filterFeed($result['statuses'],$feed_require);
				$feed_list = array_merge($feed_list,$record);
			}
			else{
				//将不足一页数据的微博存入当前页。
				if(!empty($feed_list)){
					$feed_page_content = $feed_list;
				}
				$search_end = true;
			}
			if(count($feed_list) >= RADIO_FEEDLIST_PAGESIZE){
				$feed_page_content = array_slice($feed_list,0,RADIO_FEEDLIST_PAGESIZE);
				$search_end = true;
			}
			$search_page++;
		}
		if(!empty($feed_page_content)){
			foreach($feed_page_content as $value){
				if($value['time'] > $starttime){
					return $this->returnFormat(1,$value['time']);
					//return $this->returnFormat(1);
					break;
				}
			}
		}
		//没有新feed
		return $this->returnFormat(-1);
	}

	/**
	 *
	 * 获取新feed
	 * @param string $starttime  查询起始日期
	 * @param string $rid	电台id
	 * @return array
	 */
	public function getNewFeed($starttime,$rid){
		if(empty($starttime) || empty($rid)) {
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  starttime='.$starttime.'&rid='.$rid );
			return $this->returnFormat('RADIO_00001');
		}
		//检查现有缓存中是否存在$starttime之后的feed
		$feedinfo = $this->getFeedListByRid($rid,1);
		$result = array();
		if(!empty($feedinfo['result'])){
			foreach($feedinfo['result'] as $value){
				if($value['time'] > $starttime){
					$result[] = $value;
				}
			}
		}
		if(!empty($result)){
			return $this->returnFormat(1,$result);
		}

		//现有缓存不存在$starttime之后的feed,调取搜索接口
		$res = $this->updateFeedByRid($rid,RADIO_FEEDLIST_MAXPAGE);
		if($res['errorno'] == 1){
			$feedinfo = $this->getFeedListByRid($rid,1);
			$result = array();
			if(!empty($feedinfo['result'])){
				foreach($feedinfo['result'] as $value){
					if($value['time'] > $starttime){
						$result[] = $value;
					}
				}
			}
			if(!empty($result)){
				return $this->returnFormat(1,$result);
			}
		}
		return $this->returnFormat(-1);
	}

	/**
	 *
	 * 获取第一条feed的信息
	 * @param string $rid 电台id
	 */
	public function getFirstFeedInfo($rid){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}
		$feedinfo = $this->getFeedListByRid($rid,1);
		if(!empty($feedinfo['result'])){
			$result = array_shift($feedinfo['result']);
			return $this->returnFormat(1,$result);
		}
		$this->returnFormat(-1);
	}

}
?>
