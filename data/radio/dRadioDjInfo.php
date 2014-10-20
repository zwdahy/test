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
include_once SERVER_ROOT.'dagger/libs/extern.php';
class dRadioDjInfo extends dRadio{
	public $table_field = 'rid,publink,uids,upuid,uptime,sort_type';
	public $table_name = 'radio_dj_info';

	/**
	 * 获取主持人信息(支持批量)
	 * @author 高超<gaochao@staff.sina.com.cn>
	 * @param array $rids
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getDjInfoByRid($rids,$fromdb = false){
		if(empty($rids) || !is_array($rids)) {
			return $this->returnFormat(-4);
		}
		$result = array();
		if($fromdb == false){
			foreach ($rids as $key => $value){
				$mc_key = sprintf(MC_KEY_RADIO_DJ,$value);
				$djinfo = $this->getCacheData($mc_key);
				if($djinfo == false){					
					continue;
				}
				$result[$value] = $djinfo;
				unset($rids[$key]);
			}
		}
		if(!empty($rids)){
			$db_res = $this->dbRead(array('rid' => $rids));
			if($db_res === false){
				return $this->returnFormat('RADIO_00003');
			}
			foreach($db_res as $value){
				$uids = array();
				$tmp_djinfo = explode(',',$value['uids']);
				foreach ($tmp_djinfo as $val){
					$tmp = explode('|',$val);
					if(!in_array($tmp[0],$uids)){
						$uids[] = $tmp[0];
					}
				}
				$mc_data = array('djinfo' => $value,'uids' => implode(',',$uids));
				$result[$value['rid']] = $mc_data;
				$mc_key = sprintf(MC_KEY_RADIO_DJ,$value['rid']);
				$this->setCacheData($mc_key,$mc_data,MC_TIME_RADIO_DJ);
			}
		}

		return $this->returnFormat(1,$result);
	}

	/**
	 * 添加主持人信息
	 * @author 高超<gaochao@staff.sina.com.cn>
	 * @param array $args rid,publink,uids,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addDjInfo($data) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat(-4);
		}
		$db_res = $this->dbInsert($data);
		if($db_res == false){
			return $this->returnFormat(-1);
		}
		return $this->returnFormat(1, $this->updateDjInfoMC($data['rid']));		// 更新MC
	}
	/**
	 * 编辑主持人信息
	 * @author 高超<gaochao@staff.sina.com.cn>
	 * @param array $args rid,publink,uids,upuid,uptime
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setDjInfo($data, $where = array()) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat(-4);
		}
		$db_res = $this->dbUpdate($data,$where);

		if($db_res == false){
			return $this->returnFormat(-1);
		}
		if($where['rid'] > 0){
			return $this->returnFormat(1, $this->updateDjInfoMC($where['rid']));		// 更新MC
		}
		else{
			return $this->returnFormat(1);
		}
	}

	/**
	 * 更新电台MC，先删除缓存，然后重建，便于以后扩展更新所有缓存的方法
	 * @author 高超<gaochao@staff.sina.com.cn>
	 */
	public function updateDjInfoMC($rid) {
		$djinfo = $this->getDjDetail($rid,0,true);
		$djinfo = $djinfo['result'];
		if(!empty($djinfo)){
			unset($djinfo['userinfo']);
			$mc_key = sprintf(MC_KEY_RADIO_DJ,$rid);
			$this->setCacheData($mc_key,$djinfo,MC_TIME_RADIO_DJ);
			return true;
		}
		else{
			return false;
		}
	}

	/**
	 * 获取dj详细信息
	 * @param int $rid
	 * @param int $uid
	 * @param bool $fromdb
	 * @return array
	 */
	public function getDjDetail($rid,$uid = 0,$fromdb = false){
		$dPerson = clsFactory::create(CLASS_PATH . "data", "dPerson", "service" );
		$mc_key = sprintf(MC_KEY_RADIO_DJ_INFO, $rid);
		$result = $this->getCacheData($mc_key);
		if ($result === false || $fromdb == true){
			//从数据库中读取
			$aDj = $this->dbRead(array('rid' => $rid));
			if($aDj === false){
				return $this->returnFormat('RADIO_00003');
			}

			$aDj = $aDj[0];
			$Uids = array();
			$tmp_uid = explode(',', $aDj['uids']);
			foreach($tmp_uid as $value){
				$u_info = explode("|",$value);
				if(!in_array($u_info[0],$Uids)){
					$Uids[] = $u_info[0];
				}
				$djinfo[$u_info[0]] = array('uid' => $u_info[0],'domain' => $u_info[1],'nick' => $u_info[2],'intro' => $u_info[3]);
			}

			$userInfo = $this->getUserInfoByUid($Uids);
			if($userInfo == false){
				$this->writeRadioErrLog(array('获取用户列表API失败'), 'RADIO_ERR');
				return $this->returnFormat('RADIO_00003');
			}
			foreach($djinfo as $key => $value){
				$djinfo[$key] = array(
					'uid' => $value['uid'],
					'nick' => !empty($value['nick']) ? $value['nick'] : $userInfo[$value['uid']]['name'],
					'intro' => !empty($value['intro']) ? $value['intro'] : $userInfo[$value['uid']]['description'],
					'domain' => $userInfo[$value['uid']]['link_url'],
					'portrait' => $userInfo[$value['uid']]['profile_image_url'],
					'followers_count' => $userInfo[$value['uid']]['followers_count'],
					'relation' => false,
					'user' => $userInfo[$value['uid']],
				);
				$followers_count[$key] = $userInfo[$value['uid']]['followers_count'];
			}
			if($aDj['sort_type'] == '2'){
				array_multisort($followers_count,SORT_DESC,$djinfo);
			}

			$result = array('djinfo' => $aDj,'uids' => implode(',',$Uids),'userinfo' => $djinfo);
			$this->setCacheData($mc_key, $result, MC_TIME_RADIO_DJ_INFO);
		}

		if(empty($result['userinfo'])){
			return $this->returnFormat(-1);
		}

		if($uid > 0){
			//调用接口获取
			$args = array(
				'uid'  => $uid,
				'fuids' => $result['uids']
			);
			//接口安全调用参数
			$args['appid'] = RADIO_SOURCE_APP_ID;
			$aRelation = $dPerson->newGetUserRelation($args);
			if($aRelation!== false && $aRelation['one2many'] !== false){				
				//拼装数据
				foreach($result['userinfo'] as $key=>$value){
					$result['userinfo'][$key]['relation'] = $aRelation['one2many'][$result['userinfo'][$key]['user']['id']];
				}
			}else{
				$aError = array(
					'errmsg' => 'getHotRecommend failed, get data from newGetUserRelation interface failed',
					'param'  => implode('|', $args)
				);
				$this->writeRadioErrLog($aError, 'RADIO_ERR');
			}
		}
		return $this->returnFormat(1, $result);
	}

	/**
	 * 添加在线dj的feed
	 * @param array $mids
	 * @param int $rid
	 * @param int $time	//持续时间
	 */

	 public function addDjFeed($mids,$rid,$time){
//		print '<pre>';
//		print_r($time);
//		exit;
		//根据传递的mids
		$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid);
		$old_blog_info = $this->getCacheData($mckey);//获得该电台的三条微博
		if(empty($old_blog_info)||$old_blog_info==false){
			//$old_blog_info = array(1=>'',2=>'',3=>'');//放置三个
			$old_blog_info = array();
		}
		$new_blog_info = $this->getMblog($mids);
//		print '<pre>';
//		print_r($old_blog_info);
//		exit;
		if(!empty($new_blog_info)){
			$new_blog_info = array_merge($new_blog_info,$old_blog_info);
//			print '<pre>';
//			print_r($new_blog_info);
//			exit;
			$new_blog_info = array_slice($new_blog_info,0,3);
//			print '<pre>';
//			print_r($new_blog_info);
			//去掉重复的微博
			//$new_blog_info = array_unique($new_blog_info);
//			print '<pre>';
//			print_r($new_blog_info);
//			exit;
			}
//			print '<pre>';
//			print_r($new_blog_info);
//			exit;
		$res=$this->setCacheData($mckey,$new_blog_info,$time);
		return $this->returnFormat(1, $res);
	 }



//	public function addDjFeed($minfo,$rid,$program_endtime){
//		$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid,$program_endtime);
//		
//		$cache_minfo = $this->getCacheData($mckey);
//		
//		if(is_array($minfo)){
//			$now = time();
//			$time = date("Y-m-d H:i:s",$now); 
//			$minfo['pub_time'] = $time;
//			$cache_minfo[$minfo['mid']] = $minfo;
//		}
//		else{
//			$feedinfo = $this->getDjFeedByMid($minfo);
//			if(empty($feedinfo)){
//				return false;
//			}else{
//				$now =time();
//				$time = date("Y-m-d H:i:s",$now); 
//				foreach ($feedinfo as $value){
//				$value['pub_time'] = $time;
//				$cache_minfo[$value['mid']] = $value;
//				}
//			}
//		}
//		foreach($cache_minfo as $k=>$v){
//				$pub_time[$v['mid']] = $v['pub_time']; 
//		}
//		array_multisort($pub_time,SORT_DESC,$cache_minfo);
//		for($i=0;$i<=2;$i++){
//			$n_minfo[] = array_shift($cache_minfo);
//		}
//		foreach($n_minfo as $k=>$v){
//			$new_minfo[$v['mid']] = $v;
//		}
//		return $this->setCacheData($mckey,$new_minfo,$program_endtime-time());
//	}

	/**
	 * 通过mid获取电台在线dj Feed
	 * @param unknown_type $mid  应该废了
	 */
	public function getDjFeedByMid($mid){
		$dPerson = clsFactory::create(CLASS_PATH.'data','dPerson','service');
		$MblogInfo = $this->getMblog(array($mid));
		$content = array();
		if(empty($MblogInfo)){
			return $content;
		}
		$content = $this->formatFeed($MblogInfo);

		return $content;
	}

	/**
	 * 获取在线dj的feed
	 * @param int $rid
	 * @param int $program_endtime
	 */
	public function getDjFeed($rid){
		$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid);
		$djfeed = $this->getCacheData($mckey);
//		print '<pre>';
//		print_r($djfeed);
//		exit;
		if(empty($djfeed)){
			$djfeed = array();
		}else{
			$djfeed = $this->formatFeed($djfeed);
		}
//		print '<pre>';
//		print_r($djfeed);
//		exit;
		return $this->returnFormat(1, $djfeed);
	}


//	public function getDjFeed($rid,$program_endtime){
//		$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid,$program_endtime);
//		$djfeed = $this->getCacheData($mckey);
//		
//		if(!empty($djfeed)){
//			foreach($djfeed as $k =>$v){
//				if($v!=NULL){
//				$tmp[$k] = $v;
//				}
//			}
//		    $djfeed = $tmp;
//			$aMids[] = array();
//			foreach ($djfeed as $value){
//				if(!in_array($value['mid'],$aMids)){
//					$aMids[] = $value['mid'];
//				}
//				if(!empty($value['rt']) && !in_array($value['rt']['rootmid'],$aMids)){
//					$aMids[] = $value['rt']['rootmid'];
//				}
//			}
//			if(!empty($aMids)){
//				$mblog_info = $this->getMblog($aMids,true);
//				foreach($djfeed as $key => &$val){
//					if($mblog_info[$val['mid']]['deleted'] == '1'){
//						unset($djfeed[$key]);
//					}
//					else{
//						$val['rtnum'] = !empty($mblog_info[$val['mid']]['reposts_count']) ? $mblog_info[$val['mid']]['reposts_count'] : $val['rtnum'];
//						$val['cmtnum'] = !empty($mblog_info[$val['mid']]['comments_count']) ? $mblog_info[$val['mid']]['comments_count'] : $val['cmtnum'];
//						if(!empty($val['rt'])){
//							$val['rt']['rootrtnum'] = !empty($mblog_info[$val['mid']]['reposts_count']) ? $mblog_info[$val['mid']]['reposts_count'] : $val['rt']['rootrtnum'];
//							$val['rt']['rootcmtnum'] = !empty($mblog_info[$val['mid']]['comments_count']) ? $mblog_info[$val['mid']]['comments_count'] : $val['rt']['rootcmtnum'];
//						}
//					}
//					$text = ereg_replace("<a [^>]*>|<\/a>","",$val['content']['text']);
//					$text = preg_replace('/<(img([^>]*src[^>]*title[^>]*))\/>/iU','[[[img$1/img]]]',$text);
//					$text=preg_replace("(\'|\")","abcdefg$1",$text); 
//					$text = htmlspecialchars($text);
//					$text=str_replace("abcdefg","\"",$text); 
//					$text=str_replace(array('[[[img','/img]]]'), array('<','>'), $text);
//					$val['content']['text'] = $text;
//					$tmp = $this->formatText($val['content']);
//					$val['content']['text'] = $tmp['text'];
//						
//				}
//			}
//		}
//		
//		return $djfeed;
//	}

	/**
	 * 删除在线dj的feed
	 * @param string $mid
	 * @param int $rid
	 * @param int $program_endtime
	 */
	public function delDjFeed($mid,$rid,$time){
//		echo $rid;
//		echo $mid;
//		exit;
		$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid);
		$old_blog_info = $this->getCacheData($mckey);//获得该电台的三条微博
//		print '<pre>';
//		print_r($old_blog_info);
		if(!empty($old_blog_info)){
			foreach($old_blog_info as $k=>$v){
				if($v['mid']==$mid){
					unset($old_blog_info[$k]);
					//break;
				}
			}
		}
//		print '<pre>';
//		print_r($old_blog_info);
//		exit;
		$new_blog_info = $old_blog_info;
		$res=$this->setCacheData($mckey,$new_blog_info,$time);
		return $this->returnFormat(1, $res);
	}
//	public function delDjFeed($mid,$rid,$program_endtime){
//		$feed_info = $this->getDjFeed($rid,$program_endtime);
//		if(!empty($feed_info)){
//			foreach($feed_info as $key => $value){
//				if($value['mid'] == $mid){
//					unset($feed_info[$key]);
//					break;
//				}
//			}
//			$mckey = sprintf(MC_KEY_RADIO_DJ_FEED,$rid,$program_endtime);
//			return $this->setCacheData($mckey,$feed_info,$program_endtime-time());
//		}
//		return false;
//	}

	/**
	 * 根据dj名称搜索电台
	 * 
	 * @param radioName	电台名字
	 * @param page		页码
	 * @param pagesize	页面大小
	 * @return array array('errorno' => 1, 'result' => array())
	 */
    public function searchRadioInfoByDjName($djName,$page=1,$pagesize=10,$fromdb=false){
		//将搜索结果的第一页放入mc
		$key = sprintf(MC_KEY_RADIO_SEARCH_TYPE_KEY_PAGE,'dj_name',$djName,$page);
		$djInfo = $this->getCacheData($key);
//		$fromdb = true;
		if(empty($djInfo)||$fromdb==true){
			$djName = '%'.$djName.'%';
			$offset = ($page-1)*$pagesize;
			$sql = 'SELECT '.$this->table_field.' FROM '.$this->table_name.' WHERE  `uids` LIKE ? LIMIT '.$offset.' , '.$pagesize;
			//error_log(strip_tags(print_r($sql, true))."\n", 3, "/tmp/err.log");
			$djInfo = $this->queryData($sql,array($djName));
			if(!empty($djInfo)){
				$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
				foreach($djInfo as &$v){
					$tmp = $mRadio->getRadioInfoByRid(array($v['rid']));
					$tmp = $tmp['result'][$v['rid']];
					if($tmp['is_del']==0){
						$v['radio_info'] = $tmp;
					}
				}
				unset($v);
				$error=$this->setCacheData($key,$djInfo,600);//缓存10分钟
				if($error===false){
					return 'fail to set mc';
				}
			}
		}
		return $djInfo;
	}


	/**
	 * 通过Rid取的所有的djUid的信息
	 * @param $args rid
	 */
	public function getAllDjUids($args){
		$mc_key = MC_KEY_RADIO_ALL_DJ_UIDS;
		$dj_uids = $this->getCacheData($mc_key);
		if($dj_uids == false || $dj_uids == true){
			$dj_uids = $this->getAllDjUidsFromDB($args);
			foreach ($dj_uids as $key=>$value){
				$arr_uids = explode(',',$value['uids']);
				foreach($arr_uids as $val){
					$need_uids[] = $val;
				}
			}	
			$this->setCacheData($mc_key,$need_uids,MC_TIME_RADIO_ALL_DJ_UIDS);
		}
		return $need_uids;
	}
	
	/**
	 * 获取所有DJ Uids
	 * @param array $rids
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getAllDjUidsFromDB($args){
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat(-4);
		}
		
		$db_res = $this->dbRead(array('rid' => $args));
		if($db_res === false){
			return $this->returnFormat('RADIO_00003');
		}
		return $db_res;
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
