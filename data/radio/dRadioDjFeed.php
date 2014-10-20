<?php
/**
 *
 * 电台dj feed的data层
 *
 * @package
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 *
 */

include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioDjFeed extends dRadio{
	/*
	 * 通过rid和页码获取电台feed
	 * @param string $rid	电台id
	 * @param int $page		页码
	 * @return array
	 */
	public function getDjFeedListByRid($rid,$page = 1){

		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}
		if($page == 0){
			for($feed_page=1;$feed_page <= RADIO_DJ_FEEDLIST_MAXPAGE;$feed_page++){
				$mc_key[] = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE, $rid, $feed_page);
			}
		}
		else{
			$mc_key[] = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE, $rid, $page);
		}
		//feed总数
		$count_mc_key = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE, $rid, 'count');
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
	public function updateAllDjFeed(){
		$objRadioInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioInfo", "service" );
		$radioInfo = $objRadioInfo->getAllOnlineRadio();
		$radioInfo = $radioInfo['result'];
		if(empty($radioInfo)){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台列表失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_dj_feed');
			return $this->returnFormat(-1);
		}
		$rids = array_keys($radioInfo);
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioDjInfo", "service" );
		$aradioDjInfo = $objRadioDjInfo->getDjInfoByRid($rids);
		return $this->updateFeedList($aradioDjInfo['result'],RADIO_DJ_FEEDLIST_MAXPAGE);
	}

	/*
	 * 根据电台id更新电台feed
	 *
	 * @param string $rid 电台信息列表
	 * @param int $page		feed页码
	 */
	public function updateDjFeedListByRid($rid,$page = 1){
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioDjInfo", "service" );
		$aradioDjInfo = $objRadioDjInfo->getDjInfoByRid(array($rid));
		if(empty($aradioDjInfo['result'][$rid])){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台列表失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_dj_feed');
			return $this->returnFormat(-1);
		}

		return $this->updateFeedList($aradioDjInfo['result'],$page);
	}

	/*
	 * 根据电台信息更新feed信息
	 * @param array $radioinfo 电台信息列表
	 * @param int $page		feed页码
	 */
	public function updateFeedList($djinfo,$page = 1){
		if(empty($djinfo) || !is_array($djinfo)){
			return $this->returnFormat(-9,'param error!');
		}
		foreach($djinfo as $key => $value){
			$endtime = time();
			$res = $this->updateFeedListCache($value,$page,$endtime);
			if($res['errorno'] == 1 && $res['result'] == false){
				$rids[] = $value['djinfo']['rid'];
				$aErr = array(
					'errno' => '-2',
					'errmsg' => '更新FEED缓存失败，电台id：'.$value['djinfo']['rid']
				);
				$this->writeRadioErrLog($aErr,'cron_update_dj_feed');
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
	public function updateFeedListCache($djinfo,$maxpage = 1,$endtime = 0){
		//print_r($djinfo);exit;

		$uids = explode(',',$djinfo['uids']);
        $uids = array_slice($uids,0,20);        
        $args['uids'] = implode(',',$uids);

		//按页存储电台feed
		$search_page = 1;
		$feed_list = array();
		$mc_value = array();
		$count = 0;
		for($feed_page=1;$feed_page<=$maxpage;$feed_page++){
			$search_end = false;
			while($search_end == false){
                usleep(50000);
				$args['page'] = $search_page;
				$result = $this->getMblogsTimeLine($args);
				//print_r($result);exit;
				if(count($result['statuses']) > 0){
					$record = $this->formatFeed($result['statuses']);
					$feed_list = array_merge($feed_list,$record);
				}
				else{
					//将不足一页数据的微博存入当前页。
					$mc_key = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE,$djinfo['djinfo']['rid'],$feed_page);
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
				if(count($feed_list) >= RADIO_DJ_FEEDLIST_PAGESIZE){
					$feed_page_content = array_slice($feed_list,0,RADIO_DJ_FEEDLIST_PAGESIZE);
					$mc_key = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE,$djinfo['djinfo']['rid'],$feed_page);
					$mc_value[$mc_key] = $feed_page_content;
					$count += count($feed_page_content);
					$feed_list = array_slice($feed_list,RADIO_DJ_FEEDLIST_PAGESIZE);
					$search_end = true;
				}
				$search_page++;
			}
		}
		//feed总数
		$mc_key = sprintf(MC_KEY_RADIO_DJ_FEEDLIST_PAGE,$djinfo['djinfo']['rid'],'count');
		$mc_value[$mc_key] = $count;
		//更新落地缓存表,更新缓存
		$mc_res = $this->updateKeyValue($mc_value, MC_TIME_RADIO_DJ_FEEDLIST_PAGE);
		return $this->returnFormat(1,$mc_res);
	}


	/**
	 * 获取是否存在新feed
	 * @param int $starttime	查询起始时间
	 * @param int $rid
	 * @param int $mid
	 * @return array
	 */
	public function checkNewDjFeed($starttime,$rid,$mid){
		if(empty($starttime) || empty($rid) || empty($mid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  starttime='.$starttime.'&rid='.$rid );
			return $this->returnFormat('RADIO_00001');
		}
		//检查现有缓存中是否存在$starttime之后的feed
		$feedinfo = $this->getDjFeedListByRid($rid,1);
		if(!empty($feedinfo['result'])){
			foreach($feedinfo['result'] as $value){
				if($value['time'] > $starttime){
					return $this->returnFormat(1,$value['time']);
					break;
					//return $this->returnFormat(1);
				}
			}
		}

		//现有缓存不存在$starttime之后的feed,调取搜索接口
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . "data/radio", "dRadioDjInfo", "service" );
		$aradioDjInfo = $objRadioDjInfo->getDjInfoByRid(array($rid));
		if(empty($aradioDjInfo['result'][$rid])){
			$aErr = array(
				'errno' => '-1',
				'errmsg' => '获取全部电台列表失败！'
			);
			$this->writeRadioErrLog($aErr,'cron_update_dj_feed');
			return $this->returnFormat(-1);
		}
		$djinfo = $aradioDjInfo['result'][$rid];
		$args['uids'] = $djinfo['uids'];

		//按页存储电台feed
		$search_page = 1;
		$feed_list = array();
		$feed_page=1;
		$search_end = false;
		while($search_end == false){
			$args['page'] = $search_page;
			$result = $this->getMblogsTimeLine($args);
			if(count($result['statuses']) > 0){
				$record = $this->formatFeed($result['statuses']);
				$feed_list = array_merge($feed_list,$record);
			}
			else{
				//将不足一页数据的微博存入当前页。
				if(!empty($feed_list)){
					$feed_page_content = $feed_list;
				}
				$search_end = true;
			}
			if(count($feed_list) >= RADIO_DJ_FEEDLIST_PAGESIZE){
				$feed_page_content = array_slice($feed_list,0,RADIO_DJ_FEEDLIST_PAGESIZE);
				$search_end = true;
			}
			$search_page++;
		}
		if(!empty($feed_page_content)){
			foreach($feed_page_content as $value){
				if($value['time'] > $starttime){
					return $this->returnFormat(1,$value['time']);
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
	public function getNewDjFeed($starttime,$rid){
		if(empty($starttime) || empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  starttime='.$starttime.'&rid='.$rid );
			return $this->returnFormat('RADIO_00001');
		}
		//检查现有缓存中是否存在$starttime之后的feed
		$feedinfo = $this->getDjFeedListByRid($rid,1);
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
		$res = $this->updateDjFeedListByRid($rid,RADIO_DJ_FEEDLIST_MAXPAGE);
		if($res['errorno'] == 1){
			$feedinfo = $this->getDjFeedListByRid($rid,1);
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
	public function getFirstDjFeedInfo($rid){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}
		$feedinfo = $this->getDjFeedListByRid($rid,1);
		if(!empty($feedinfo['result'])){
			$result = array_shift($feedinfo['result']);
			return $this->returnFormat(1,$result);
		}
		$this->returnFormat(-1);
	}
}
?>
