<?php
/**
 *
 * 电台信息的data层
 *
 * @package
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
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
include_once SERVER_ROOT."dagger/libs/extern.php"; 
class dRadioInfo extends dRadio{
	public $table_field = '`rid`,`domain`,`info`,`tag`,`source`,`recommend`,`img_path`,`intro`,`uid`,`url`,`classification_id`,`province_id`,`province_spell`,`city_id`,`feed_require`,`is_feed`,`online`,`search_type`,`right_picture`,`admin_uid`,`first_online_time`,`admin_url`,`program_visible`,`epgid`,`http`,`mu`,`start_time`,`end_time`,`uptime`';
	public $table_name = 'radio_info';
	/**
	 *
	 * 获取电台列表 按省份分了
	 */
	public function getRadioList($fromdb = false,$needsort=true){
		//从缓存中获取
		$key = MC_KEY_RADIO_LIST;
		$aRadioList = $this->getCacheData($key);
		if($aRadioList == false || $fromdb == true){
			//从数据库取数据
			$objRadioArea = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioArea', 'service');
			$aRadioArea = $objRadioArea->getAreaList($fromdb);
			if($aRadioArea['errorno'] != 1){
				//获取电台地区列表失败
				$this->writeRadioErrLog($aRadioArea);
				return $this->returnFormat('RADIO_00003');
			}
			//按照地区列表排序规则生成电台列表
			$result = array();
			$count = 0 ;
			foreach($aRadioArea['result'] as $k => $v){
				$aRadioInfo = $this->getRadioInfoByPid(array($v['province_id']),$fromdb);
				$count += count($aRadioInfo['result'][$v['province_id']]);
				if($aRadioInfo['errorno'] == 1){
//					$aRadioInfo['result'] = $this->reorderRadio($aRadioInfo['result']);
					$result[$v['province_id']] = $aRadioInfo['result'][$v['province_id']];
				}
			}
			$aRadioList['content'] = $result;
			$aRadioList['count'] = $count;

			if(empty($aRadioList)){
				return $this->returnFormat('RADIO_00003');
			}
			$this->setCacheData($key, $aRadioList, MC_TIME_RADIO_LIST);
		}
		return array(
			'errorno' => 1,
			'result'  => $aRadioList['content'],
			'count'   => $aRadioList['count']
		);
	}

	/**
	 *
	 * 获取电台列表 默认不包括下线的
	 */
	public function getAllRadioList($online = 1,$fromdb = false){
		//从缓存中获取
		$key = MC_KEY_RADIO_LIST_V2;
		$allRadioList = $this->getCacheData($key);
		if($allRadioList == false || $fromdb == true || empty($allRadioList)){
			$allRadioList = $this->dbRead(array('online' => $online));
			if(!empty($allRadioList)){
				foreach($allRadioList as &$v){
						$tmp=explode('|',$v['info']);
						$v['name']=$tmp[0];
						$v['fm']=$tmp[1];
						$v['radio_url']=RADIO_URL."/".$v['province_spell'].'/'.$v['domain'];
				}
				unset($v);
			}
//			print_r($allRadioList);
//			exit;
			$this->setCacheData($key,$allRadioList,86400);
		}
		return array(
			'errorno' => 1,
			'result'  => $allRadioList,
		);

	}

	/**
	 * 通过pid和cid进行结果查询电台列表
	 * @param $arr=array('cid'=>分类id,'pid'=>地区id)
	 * @return array $array
	 */
	public function sortRadioList($arr,$fromdb=false,$program_visible=2){
        if(empty($arr)||!is_array($arr)){
            return $this->returnFormat(-4);
        }   
        $radioList=array();
        //search mc
        $mc_key=sprintf(MC_KEY_RADIO_LIST_SORT,$arr['cid'],$arr['pid']);
        $radioList=$this->getCacheData($mc_key);
        //search mysql
        if(empty($radioList)||$radioList==false||$fromdb==true){
            $radioList=$this->dbRead(array('classification_id'=>$arr['cid'],'province_id'=>$arr['pid'],'program_visible'=>$program_visible),array("recommend"));
			if(!empty($radioList)){
				foreach($radioList as &$v){
						$tmp=explode('|',$v['info']);
						$v['name']=$tmp[0];
						$v['fm']=$tmp[1];
						$v['radio_url']=RADIO_URL."/".$v['province_spell'].'/'.$v['domain'];
				}
				unset($v);
			}
			//更新mc数据
			$this->setCacheData($mc_key,$radioList,MC_TIME_RADIO_LIST_SORT);
        }
		return $this->returnFormat(1,$radioList);
    }   

	/**
	 * 按照地区分类排序重新排序电台信息
	 * @param $arr=array('cid'=>分类id,'pid'=>地区id)
	 * @return array $array
	 */
	public function reorderRadio($array){
		foreach($array as $k => &$v){
			if($v['recommend'] != $k+1){
				$this->setRadio(array("recommend" => $k+1),array("rid" => $v['rid']));
				$v['recommend'] = $k+1;
			}
		}
		return $array;
	}

//	/*
//	 * 根据地区id获取该地区共有多少电台
//	 * @param int $pids	地区id
//	 * @param int $online	默认上线电台
//	 * @param bool $fromdb 是否从数据库获取数据
//	 * @param array
//	 */
//	public function getRadioNumByPid($pid,$online = 1,$fromdb = false){
//		if(empty($pid)){
//			return $this->returnFormat(-4);
//		}
//		$res=0;
//        //search mc
//        $mc_key=sprintf(MC_KEY_RADIO_NUM_BY_PID,$pid);
//        $res=$this->getCacheData($mc_key);
//        //search mysql
//        if(empty($res)||$res==false||$fromdb==true){
//			$sql="SELECT COUNT(*) AS `total` FROM {$this->table_name} WHERE `province_spell`={$pid} AND `online`={$online}";
//			$res =$this->_dbReadBySql($sql);
//			//更新mc数据
//			$this->setCacheData($mc_key,$res,600);
//        }
//		return $this->returnFormat(1,$radioList);
//
//	}
	


	/*
	 * 根据地区id获取电台信息（支持批量）
	 * @param int $pids	地区id
	 * @param bool $fromdb 是否从数据库获取数据
	 * @param array
	 */
	public function getRadioInfoByPid($pids,$fromdb = false){
		if(empty($pids) || !is_array($pids)){
			return $this->returnFormat(-4);
		}
		$aRadioInfo = array();
		if($fromdb == false){
			foreach($pids as $key => $value){
				$mc_key = sprintf(MC_KEY_RADIO_LIST_BY_PID,$value);
				$aRadioInfo[$value] = $this->getCacheData($mc_key);
				if(!empty($aRadioInfo[$value])){
					unset($pids[$key]);
				}
			}
		}
		if(!empty($pids)){
			foreach ($pids as $k => &$v) {
				$v = intval($v);
			}
			unset($v);
			$radioInfo = $this->dbRead(array('province_id' => $pids),array("province_id,recommend"));
			if($radioInfo === false){
				return $this->returnFormat('RADIO_00003');
			}
			$pid_rids = array();
			foreach($radioInfo as $key => $value){
				if(empty($pid_rids[$value['province_id']])){
					$pid_rids[$value['province_id']][] = $value['rid'];
				}
				else{
					if(!in_array($value['rid'],$pid_rids[$value['province_id']])){
						$pid_rids[$value['province_id']][] = $value['rid'];
					}
				}
			}
			foreach($pid_rids as $key => $value){
				$tmp = $this->getRadioInfoByRid($value,$fromdb);
				$aRadioInfo[$key] = $tmp['result'];
				$mc_key = sprintf(MC_KEY_RADIO_LIST_BY_PID,$key);
				$this->setCacheData($mc_key, $aRadioInfo[$key], MC_TIME_RADIO_LIST_BY_PID);
			}
		}

		return $this->returnFormat(1,$aRadioInfo);
	}

	/*
	 * 根据类别id获取电台信息（支持批量）
	 * @param int $classification_ids	类别id
	 * @param bool $fromdb 是否从数据库获取数据
	 * @param array
	 */
	public function getRadioInfoByClassificationids($cids,$fromdb = false){
		if(empty($cids) || !is_array($cids)){
			return $this->returnFormat(-4);
		}
		$aRadioInfo = array();
		foreach($cids as $key => $value){
			$mc_key = sprintf(MC_KEY_RADIO_LIST_BY_CID,$value);
			$aRadioInfo[$value] = $this->getCacheData($mc_key);
			if(!empty($aRadioInfo[$value])){
				unset($cids[$key]);
			}
		}
		//如果有还未更新的cid
		if(!empty($cids)){
			foreach ($cids as $k => &$v) {
				$v = intval($v);
			}
			$radioInfo = $this->dbRead(array('classification_id' => $cids,'online' => '1'),array("classification_id,province_id,recommend"));
			if($radioInfo === false){
				return $this->returnFormat('RADIO_00003');
			}
			$cid_rids = array();
			foreach($radioInfo as $key => $value){
				if(empty($cid_rids[$value['classification_id']])){
					$cid_rids[$value['classification_id']][] = $value['rid'];
				}
				else{
					if(!in_array($value['rid'],$cid_rids[$value['classification_id']])){
						$cid_rids[$value['classification_id']][] = $value['rid'];
					}
				}
			}
			$used_cids = array();//记录更新过的cid
			foreach($cid_rids as $key => $value){
				$tmp = $this->getRadioInfoByRid($value,$fromdb);
				$aRadioInfo[$key] = $tmp['result'];
				$mc_key = sprintf(MC_KEY_RADIO_LIST_BY_CID,$key);
				$this->setCacheData($mc_key, $aRadioInfo[$key], MC_TIME_RADIO_LIST_BY_CID);
				$used_cids[] = $key;
			}
			//取存在却未更新的cid，即分类下没有电台的id
			$cid_diff = array_merge(array_diff($cids, array_intersect($cids, $used_cids)), array_diff($used_cids, array_intersect($cids, $used_cids)));
			//将分类下没有电台的 置空
			if(!empty($cid_diff)){
				foreach($cid_diff as $val){
					$empty_val= false;
					$mc_key = sprintf(MC_KEY_RADIO_LIST_BY_CID,$val);
					$this->setCacheData($mc_key, $empty_val, MC_TIME_RADIO_LIST_BY_CID);
				}
			}
		}
		return $this->returnFormat(1,$aRadioInfo);
	}

	/*
	 * 更新所有类别电台信息
	 */
	public function updateAllRadioInfoByClassificationids(){
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$sortList = $mRadio->getClassificationList();
		if(!empty($sortList['result'])){
			$cids = array();
			foreach($sortList['result'] as $v){
				$cids[] = $v['classification_id'];
			}
			$result = $this->getRadioInfoByClassificationids($cids,true);
		}else{
			return $this->returnFormat(-4);
		}
		return $result;
	}

	/**
	 *
	 * 获取某电台详情（支持批量）
	 * @param array $rids	电台id
	 * @param bool $fromdb	是否从数据库获取数据
	 * @return array
	 */
	public function getRadioInfoByRid($rids,$fromdb = false){
		if(empty($rids) || !is_array($rids)){
			return $this->returnFormat(-4);
		}
		if($fromdb == false){
			foreach($rids as $key => $value){
				$mc_key = sprintf(MC_KEY_RADIO_BY_RID, $value);
				$aRadioInfo[$value] = $this->getCacheData($mc_key);
				if(!empty($aRadioInfo[$value])){
					unset($rids[$key]);
				}
			}
		}
		if(!empty($rids)){
			if(is_array($rids)){
				foreach($rids as &$v){
					$v = intval($v);
				}
			}else{
				$rids =intval($rids);
			}
			$radioInfo = $this->dbRead(array('rid' => $rids));
//			print '<pre>';
//			print_r($radioInfo);
//			exit;
			if($radioInfo === false){
				return $this->returnFormat('RADIO_00003');
			}
			$reg="/(&lt;.+&lt;\/a&gt;)/s";
			foreach($radioInfo as $key => $value){
				$value['intro_old']=$value['intro'];
				$value['intro']=preg_replace($reg,'',$value['intro']);
				$value['intro']=str_replace('&lt;br&gt;','',$value['intro']);
				$tmp = explode('|',$value['info']);
				$value['name'] = $tmp[0];
				$value['fm'] = $tmp[1];
				$value['radio_url'] = RADIO_URL.'/'.$value['province_spell'].'/'.$value['domain'];
				$tmp_aRadioInfo[$value['rid']] = $value;
				$recommend[$value['rid']] = $value['recommend'];
				$mc_key = sprintf(MC_KEY_RADIO_BY_RID, $value['rid']);
				$this->setCacheData($mc_key, $value, MC_TIME_RADIO_BY_RID);
			}
			if(is_array($recommend)&&is_array($tmp_aRadioInfo)){
				array_multisort($recommend,SORT_ASC,$tmp_aRadioInfo);
			}
			if(!empty($tmp_aRadioInfo)){
				foreach($tmp_aRadioInfo as $value){
					$aRadioInfo[$value['rid']] = $value;
				}
			}
		}
		return $this->returnFormat(1, $aRadioInfo);
	}

	/**
	 * 获取微博评论数
	 * @param $args 参数数组
	 * @return array
	 */
	public function getMblogCommentCount($args) {
		if(empty($args) || !is_array($args)) {
			return false;
		}
		if(!isset($args['mid']) || empty($args['mid']) || !is_array($args['mid'])) {
			return false;
		}
		$result = array();
		$api = clsFactory::create('libs/api', 'InternalAPI');
		//接口安全调用参数
		$args['appid'] = isset($args['appid'])?$args['appid']:RADIO_SOURCE_APP_ID;
		$result =  $api->getCommentNumByResIds($args);
		if($result === false) {
			$this->writeRadioErrLog(array('获取信息API失败', '参数:$api->getCommentNumByResIds(' . implode('|', $args) . ')'), 'RADIO_ERR');
			return false;
		}
		return $result;
	}

	/**
	 * 获取电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $whereArgs domain,info,tag,source,recommend,upuid,uptime
	 * @param array $postfixArgs 排序以及分页
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getRadio($whereArgs = array(), $postfixArgs = array()) {
		if(!is_array($whereArgs) && !is_array($postfixArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->_radioInfo, '*', $whereArgs, $postfixArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$content = $st->fetchALL(PDO::FETCH_ASSOC);
		if(is_numeric($whereArgs['rid'])) {
			return $this->returnFormat(1, $content);
		} else {
			// 计算总数
			$postfixArgs['page'] = 1;
			$sqlArgs = $this->_makeSelect($this->_radioInfo, 'COUNT(*) AS count', $whereArgs, $postfixArgs);
			$st  = $db->prepare($sqlArgs['sql']);
			if(false == $st->execute($sqlArgs['data'])) {
				$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
				return $this->returnFormat('RADIO_D_DB_00003');
			}
			$count = $st->fetch(PDO::FETCH_ASSOC);
			return $this->returnFormat(1, array('count' => $count['count'], 'content' => $content));
		}
	}
	/**
	 * 添加电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$args['domain'] = strtolower($args['domain']);
		$sqlArgs = $this->_makeInsert($this->_radioInfo, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}

		$lastId = $db->lastInsertId();
		$trans_args['rid'] = $lastId;		// 电台ID
		$trans_args['rName'] = $args['info'];	// 电台名称
		$trans_args['rFm'] = $args['domain'];		// 电台调频
		$trans_args['mms'] = urlencode($args['source']);		// mms流地址
		$trans_args['mms_old'] = $args['source'];		//最原始的mms流
		$trans_args['source'] = $args['source'];		//最原始的mms流
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$res = $mRadio->transcodeRadio2($trans_args);

		// 更新MC
		//更新open的全部电台缓存
		$this->getAllOnlineForOpen(true);
		//更新地区电台信息
		$this->getRadioInfoByPid(array($args['province_id']),true);
		//更新分类电台信息
		$this->getRadioInfoByClassificationids(array($args['classification_id']),true);
		return $this->returnFormat(1, $this->getRadioList(true));
	}
	/**
	 * 编辑电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
		public function setRadio($args, $whereArgs) {
		if(empty($args) || !is_array($args) || (!empty($whereArgs) && !is_array($whereArgs))) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}

		if(!empty($args['domain'])){
			$args['domain'] = strtolower($args['domain']);
		}
		if(!empty($args['transcode_flag'])){
			$transcode_flag = 1;
			unset($args['transcode_flag']);
		}
		$sqlArgs = $this->_makeUpdate($this->_radioInfo, $args, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		//更新单个电台信息缓存
		$flag = $this->updateRadioByRidMC(array($whereArgs['rid']));
		//更新分类电台信息
		if(isset($args['classification_id'])||isset($args['online'])){
			$this->updateAllRadioInfoByClassificationids();
		}
		//单个转码的操作  不走更新全部电台的缓存。
		if(!isset($transcode_flag)){
			$flag2 = $this->getRadioList(true);
			//更新open的全部电台缓存
			$this->getAllOnlineForOpen(true);
		}
			// 更新radiolist缓存
		return $this->returnFormat(1,$flag.",".$flag2);

	}

	/**
	 * 更新电台信息缓存
	 * @param array $rids
	 */
	public function updateRadioByRidMC($rids){
		$aRadioInfo = $this->getRadioInfoByRid($rids,true);
		$aRadioInfo = $aRadioInfo['result'];
		if(empty($aRadioInfo)){
			return false;
		}
		return true;
	}

	/**
	 * 删除电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delRadio($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeDelete($this->_radioInfo, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		// 更新MC
		//更新open的全部电台缓存
		$this->getAllOnlineForOpen(true);
		return $this->returnFormat(1,$this->getRadioList(true));
	}

	/**
	 * 根据province_id获取电台信息个数
	 * @param int $pid
	 * @return int
	 */
	public function getProvinceCounts($pid){
		$radioinfo = $this->dbRead(array('province_id' => $pid));
		return count($radioinfo);
	}

	/**
	 * 根据官方微博uid获取电台信息（支持批量）
	 * @param array $uids	官方微博uid
	 * @return array
	 */
	public function getRadioByUid($uids,$fromdb = false){
		if(empty($uids) || !is_array($uids)){
			return $this->returnFormat(-4);
		}
		$aRadioInfo = array();
		if($fromdb == false){
			foreach($uids as $key => $value){
				$mc_key = sprintf(MC_KEY_RADIO_BY_UID,$value);
				$aRadioInfo[$value] = $this->getCacheData($mc_key);
				if(!empty($aRadioInfo[$value])){
					unset($uids[$key]);
				}
			}
		}
		if(!empty($uids)){
			$radioinfo = $this->dbRead(array('uid' => $uids, 'online'=>1));
			if($radioinfo === false){
				return $this->returnFormat('RADIO_00003');
			}			
			$rids = array();
			foreach($radioinfo as $value){
				if(!in_array($value['rid'],$rids)){
					$rids[] = $value['rid'];
				}
			}
			$tmp = $this->getRadioInfoByRid($rids,$fromdb);
			$tmp = $tmp['result'];
			if(!empty($tmp)){
				foreach($tmp as $value){
					$aRadioInfo[$value['uid']] = $value;
				}
			}
		}

		return $this->returnFormat(1,$aRadioInfo);
	}

	/**
	 * 根据条件获取某电台的最新Feed
	 */
	public function getNewsfeedByNum($key_word,$start_time,$end_time,$p_name,$search_type,$start=0,$num=10){
		//调取搜索接口，过滤feed
		$objMblog = clsFactory::create(CLASS_PATH . "data", "dMblog", "service" );
		$dPerson = clsFactory::create(CLASS_PATH.'data','dPerson','service');
		$objBasic = clsFactory::create(CLASS_PATH . "data/radio", "dRadioBasic", "service" );

		//接口安全调用参数
		$args['cuid'] = RADIO_ADMIN_UID;
		$args['num'] = $num;
		$args['start'] = $start;
		$args['starttime'] = $start_time;
		$args['endtime'] = $end_time;
		if($search_type == 1){
			$args['appid'] = RADIO_SOURCE_APP_ID;
		}
		//$args['cip'] = tCheck::getIp();
		$args['sid'] = "t-radio_c";
		$args['nofilter'] = 2;

		$strKeyword = preg_match_all('|^#(.*)#$|', $key_word, $out);
		if($out[1][0] != ''){
			$args['key'] = $out[1][0];
			$args['istag'] = 1;
		}else{
			$args['key'] = $key_word;
		}

		$args['key'] = rawurlencode($args['key']);
		//调用搜索接口
		$rs = $this->searchMblogByrpc($args);
		//为防止修改后的参数有变化，修改了新接口返回的参数
		$rs['record'] = $rs['statuses'];
		$rs['total'] = $rs['total_number'];
		unset($rs['statuses']);
        unset($rs['total_number']);
		return $rs;

	}

	/**
	 * 根据province_spell和domain获取radio信息
	 * @param unknown_type $args
	 */
	public function getRadioByDomainAndPro($domain,$province_spell,$fromdb = false){
		if(empty($domain) || empty($province_spell)){
			//参数错误
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$key = sprintf(MC_KEY_RADIO_BY_DOMAIN_PROVINCE, $domain,$province_spell);
		$radioInfo = $this->getCacheData($key);
		if($radioInfo == false || $fromdb == true){
			//从数据库中获取
			$aRadioInfo = $this->dbRead(array('domain' => $domain,'province_spell' => $province_spell));
			if($aRadioInfo === false){
				return $this->returnFormat('RADIO_00003');
			}
			$aRadioInfo = $aRadioInfo[0];
			$radioInfo = $this->getRadioInfoByRid(array($aRadioInfo['rid']),$fromdb);
			$radioInfo = $radioInfo['result'][$aRadioInfo['rid']];
			//更新缓存
			$this->setCacheData($key, $radioInfo, MC_TIME_RADIO_BY_DOMAIN_PROVINCE);
		}
		return $this->returnFormat(1, $radioInfo);
	}

	/**
	 * 根据province_spell和domain获取radio信息
	 * @param unknown_type $args
	 */
	public function getRadioByPidAndDomain($pid,$domain,$fromdb = false){
		if(empty($pid) || empty($domain)){
			//参数错误
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$key = sprintf(MC_KEY_RADIO_BY_DOMAIN_PROVINCE_ID, $pid,$domain);
		$radioInfo = $this->getCacheData($key);
		if($radioInfo == false || $fromdb == true){
			//从数据库中获取
			$aRadioInfo = $this->dbRead(array('province_id' => $pid,'domain' => $domain,'online'=>1));
			if($aRadioInfo === false){
				return $this->returnFormat('RADIO_00003');
			}
			$aRadioInfo = $aRadioInfo[0];
			$radioInfo = $this->getRadioInfoByRid(array($aRadioInfo['rid']),$fromdb);
			$radioInfo = $radioInfo['result'][$aRadioInfo['rid']];
			//更新缓存
			$this->setCacheData($key, $radioInfo, 600);
		}
		return $this->returnFormat(1, $radioInfo);
	}


	/**
	 * 根据rid获取crontab跑出来的缓存数据
	 * @param $rid
	 */
	public function getNewsfeedByCrontab($province_spell,$domain){
		if(!$province_spell || !$domain) {
			//参数错误
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$key = sprintf(MC_KEY_RADIO_RECENT_WEBINFO_BY_CRONTAB, $province_spell.$domain);
		$aWeiboList = $this->getCacheData($key);

		if($aWeiboList !== false){
			return $this->returnFormat(1, $aWeiboList);
		}else{
			return $this->returnFormat(1, '');
		}

		/* ---  如果从缓存中获取失败，就直接返回null,不用再去调用搜索接口了。
		//从搜索接口中获取数据
		$aWeiboList = $this->getNewsfeedByNum($key_word,$start_time,$end_time,$p_name,$search_type,0,50);
		if($aWeiboList === false){
			return $this->returnFormat('RADIO_00003');
		}
		//更新缓存
		$this->setCacheData($key, $aWeiboList, MC_TIME_RADIO_RECENT_WEBINFO_BY_CRONTAB);
		return $this->returnFormat(1, $aWeiboList);
		*/
	}


	/**
	 * crontab 调用的最新微博信息的更新cache接口
	 * @param $rid
	 */
	public function updateCrontabWeiboCache($key_word,$province_spell,$search_type,$domain){
		if(!$province_spell || !$key_word || !$domain){
			//参数错误
			return $this->returnFormat('RADIO_D_CK_00001');
		}

		//从搜索接口中获取数据
		$aWeiboList = $this->getNewsfeedByNum($key_word,'','',$province_spell,$search_type,0,50);

		$key = sprintf(MC_KEY_RADIO_RECENT_WEBINFO_BY_CRONTAB, $province_spell.$domain);
		if($aWeiboList === false){
			$aWeiboList = $this->getCacheData($key);
		}else{
			//如果搜索没有值，就不用处理
			$this->setCacheData($key, $aWeiboList, MC_TIME_RADIO_RECENT_WEBINFO_BY_CRONTAB);
		}

		return $this->returnFormat(1, $aWeiboList);
	}


	/**
	 *
	 * 获取所有的电台信息的接口--提供给搜索
	 */
	public function getAllOnlineRadio($fromdb = false){
		$mcKey = MC_KEY_RADIO_ALL_ONLINE_LIST;
		$aRadioList = $this->getCacheData($mcKey);
		if($aRadioList == false || $fromdb == true){
			//从数据库中获取
			$db_res = $this->dbRead(array('online' => 1));
			if($db_res === false){
				return $this->returnFormat('RADIO_00003');
			}
			$aRadioList = array();
			$rids = array();
			foreach($db_res as $value){
				if(!in_array($value['rid'],$rids)){
					$rids[] = $value['rid'];
				}
			}
			$aRadioList = $this->getRadioInfoByRid($rids,$fromdb);
			$aRadioList = $aRadioList['result'];
			if(!empty($aRadioList) > 0){
				//循环处理得到的电台数据，把电台的图片取出来
				foreach($aRadioList as $key=>$value){
					if($value['img_path'] != ''){
						$aRadioList[$value['rid']]['radio_image'] = $value['img_path'];
					}else{
						$uids[] = $value['uid'];
					}
				}
				if(!empty($uids)){
					//通过uid去取官方头像
					$userInfo = $this->getUserInfoByUid($uids);
					foreach($userInfo as $key => $val){
						$aRadioList[$key]['radio_image'] = $val['avatar_large'];
					}
				}
				//更新缓存
				$this->setCacheData($mcKey, $aRadioList, MC_TIME_RADIO_ALL_ONLINE_LIST);
			}
		}
		return $this->returnFormat(1, $aRadioList);
	}

	/**
	 * 
	 * openAPI - 获取所有在线的电台信息
	 */
	public function getAllOnlineForOpen($fromdb = false){
		$mcKey = MC_KEY_RADIO_ALL_ONLINE_FOR_OPEN;
		$aRadioList = $this->getCacheData($mcKey);
		// 缓存中没有值的时候，或者数据开关为true的时候，去DB中去取值，并把version版本写进去。
		if($aRadioList == false || $fromdb == true){
			//从数据库中获取
			$db_res = $this->dbRead(array('online' => 1));
			if($db_res === false){
				return $this->returnFormat('RADIO_00003');
			}
			$aRadioList = array();
			$rids = array();
			foreach($db_res as $value){
				if(!in_array($value['rid'],$rids)){
					$rids[] = $value['rid'];
				}
			}
			$aRadioList = $this->getRadioInfoByRid($rids,$fromdb);
			$aRadioList = $aRadioList['result'];
			if(!empty($aRadioList) > 0){
				//循环处理得到的电台数据，把电台的图片取出来
				foreach($aRadioList as $key=>$value){
					if($value['img_path'] != ''){
						$aRadioList[$value['rid']]['radio_image'] = $value['img_path'];
					}else{
						$uids[] = $value['uid'];//找出没有电台图片的电台官方微博账号
//						$uid = $value['uid'];//找出没有电台图片的电台官方微博账号
//						$userInfo = $this->getUserInfoByUid(array($uid));
//						$aRadioList[$value['rid']]['radio_image']=$userInfo[$uid]['profile_image_url'];
					}
				}
				if(!empty($uids)){
					//通过uid去取官方头像
					$userInfo = $this->getUserInfoByUid($uids);
					foreach($userInfo as $key => $val){
						$aRadioList[$key]['radio_image'] = $val['avatar_large'];
					}
				}
				$aRadioList = array(
					'version' => time(),
					'radios' => $aRadioList
				);
				//更新缓存
				$this->setCacheData($mcKey, $aRadioList, MC_TIME_RADIO_ALL_ONLINE_FOR_OPEN);
			}
		}
		return $this->returnFormat(1, $aRadioList);
	}
	
	
	/**
	 * 数据库SELECT数据库操作
	 * @param $where
	 * @param $order
	 */
	public function dbRead($where = array(), $order = array()){
		return $this->_dbRead($this->table_name,$this->table_field,$where,$order);
	}
	//为电台音频源转码调用看点的音频流接口特殊处理 绑定host
	protected static function requestGettranscode($url, $timeout=3,$httpHeader=array('Host:inner.kandian.com')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		if(!empty($httpHeader) && is_array($httpHeader))  
    {  
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpHeader);  
    }  
		$result = curl_exec($ch);
		curl_close($ch);
		if($result===false) return error(SYSTEM_ERROR);
		return $result;
	}
	/**
	 * 电台音频源转码调用看点的音频流接口
	 * @param array $args
	 */
	public function transcodeRadio($args){
		if(isset($args['continue']) && $args['continue'] != ''){
			$rId = $args['rid'];
			if(empty($rId)){
				//参数错误
				return $this->returnFormat('RADIO_D_CK_00001');
			}
			$url = sprintf(RADIO_KANDIAN_TRANSCODE_URL,$rId,$args['continue']);
			$aResult = json_decode($this->requestGettranscode($url, 2),true);
		}else{
			$rId = $args['rid'];
			$rName = $args['rName'];
			$rFm = $args['rFm'];
			$mms = $args['mms'];		
			$url = sprintf(RADIO_KANDIAN_TRANSCODE_URL_CREATE,$rId,$rName,$rFm,$mms);
			$aResult = json_decode($this->requestGettranscode($url, 2),true);	
		}
		//BaseModelCommon::debug($url,'url');
		//BaseModelCommon::debug($aResult,'aResult');
		//进行转码成功后的操作
		if($aResult['code'] == 'A0001'){
			if(isset($args['mms_old'])){
				$args['mms'] = $args['mms_old'];
			}
			//更新电台操作
			$update_args['source'] = $args['mms'];						//新填写的mms
			$update_args['epgid'] = $aResult['data']['epgId'];			// 音频流ID
			$update_args['http'] = htmlspecialchars_decode($aResult['data']['http']);			// http
			$update_args['mu'] = htmlspecialchars_decode($aResult['data']['m3u8']);				// m3u8
			$update_args['start_time'] = $aResult['data']['startTime'];	// 音频流开始时间
			$update_args['end_time'] = $aResult['data']['endTime'];		// 音频流结束时间
			$update_args['transcode_flag'] = 1;
			$whereArgs = array('rid' => $rid);
			$this->setRadio($update_args, $whereArgs);	
		}
		return $aResult;
	}


	/**
	 * 电台音频源转码调用看点的音频流接口
	 * @param array $args('rid'=>,'source'=>,start_time,$end_time)
	 *
	 */
     public function transcodeRadio2($args){
        $rid = $args['rid']; 
        $key = 'weidiantai';
        $channel_url = trim($args['source'].'$48');
		$start_time = $args['start_time'];
		$end_time = $args['end_time'];
		//新电台上线转流 改版转流等情况的处理
        $callback_url = '';
        $channel_url = urlencode($channel_url);
        $callback_url = urlencode($callback_url);
		$stream_retry_secs = 60;//断流重试间隔
		$valide = md5( $end_time . $key . $start_time );
		$url = sprintf(RADIO_TRANSCODE_URL_REDIS, $channel_url, $start_time, $end_time, $callback_url, $key, $valide, $stream_retry_secs);
		$channel_id = $this->curlGetData($url,6);
		$channel_id = trim($channel_id);
		if(!is_numeric($channel_id)){
			$type = 'radio_err';
			$channel_id = json_encode($channel_id.$rid);
			$curl_data = array('type'=>$type,'log'=>$channel_id);
			$url = 'http://i.alarm.mix.sina.com.cn/tmplog.php';
			$this->request($url,$curl_data);
		}
		if(is_numeric($channel_id)){
            $m3u8 = "http://wtv.v.iask.com/player/ovs1_rt_chid_{$channel_id}_br_3_pn_weidiantai_tn_0_sig_md5.m3u8";
        }
		//进行转码成功后的操作
		if(isset($m3u8)){
			//更新电台操作
			$update_args['epgid'] = $channel_id;     // 音频流ID
			$update_args['http'] = $m3u8;			// http
			$update_args['mu'] = $m3u8;				// m3u8
			$update_args['start_time'] = $start_time;	// 音频流开始时间
			$update_args['end_time'] = $end_time;		// 音频流结束时间
			$whereArgs = array('rid' => $rid);
            $sqlArgs = $this->_makeUpdate($this->table_name, $update_args, array('rid'=>$rid));
            $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
		}
		return $update_args;
	}
	
	//@test
	//测试用
	 function request($url, $curl_data = '', $timeout = 30, $header = 0, $follow = 0) {
		$ch = curl_init();
		$options = array(
			CURLOPT_URL             => $url,
			CURLOPT_TIMEOUT         => $timeout,
			CURLOPT_HEADER          => $header,
			CURLOPT_FOLLOWLOCATION  => $follow,
			CURLOPT_RETURNTRANSFER  => 1,
			CURLOPT_USERAGENT       => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:12.0) Gecko/20100101 Firefox/12.0',
			CURLOPT_SSL_VERIFYPEER  => false,
			CURLOPT_SSL_VERIFYHOST  => false,
		);

		if (!empty($curl_data)) {
			$options[CURLOPT_POST]       = 1;
			$options[CURLOPT_POSTFIELDS] = $curl_data;
		}

		curl_setopt_array($ch, $options);
		$result = $header ? curl_getinfo($ch) : curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	
	/**
	 * 根据电台名称搜索电台
	 * 
	 * @param radioName	电台名字
	 * @param page		页码
	 * @param pagesize	页面大小
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function searchRadioInfoByRadioName($radioName,$page=1,$pagesize=10,$fromdb=false) {
		//将搜索结果的第一页放入mc
		$tmp_name = urlencode($radioName);
		$key = sprintf(MC_KEY_RADIO_SEARCH_TYPE_KEY_PAGE,'radio_name',$tmp_name,$page);
		$res = $this->getCacheData($key);
		if(empty($res)||$fromdb==true){
			$radioName = '%'.$radioName.'%';
			$offset = ($page-1)*$pagesize;
			$sql = 'SELECT '.$this->table_field.' FROM '.$this->table_name.' WHERE `online`= 1 AND `info` LIKE ? GROUP BY `rid` ORDER BY `province_id`,`recommend` LIMIT '.$offset.' , '.$pagesize;
			$radioInfo =$this->queryData($sql,array($radioName));
			if(!empty($radioInfo)){
				foreach($radioInfo as &$v){
					$v['radio_url']=RADIO_URL.'/'.$v['province_spell'].'/'.$v['domain'];
					$tmp=explode('|',$v['info']);
						$v['name']=$tmp[0];
						$v['fm']=$tmp[1];
						$v['radio_url']=RADIO_URL."/".$v['province_spell'].'/'.$v['domain'];
				}
				unset($v);
				$res = array();
				$res['result']=$radioInfo;
				$res['pages']=ceil(count($programInfo)/$pagesize);
				$error=$this->setCacheData($key,$res,600);//缓存10分钟
				if($error===false){
					return 'fail to set mc';
				}
			}
		}
		return $res;
	}
	
	/**
	 * 获取不稳定音频流的接口
	 * @param array $args
	 */
	public function getUnableMu(){
		$url = "http://60.28.2.208/m3u8s.json";
		$aResult = json_decode($this->curlGetData($url, 1),true);	
		return $aResult;
	}
	
	/**
	 *统计 所有地区 所有电台 所有dj @radio_statistic_info
	 *
	 */
	public function getStaticData(){
		$key = MC_KEY_RADIO_STATIC_INFO;
		$res = $this->getCacheData($key);
		if($res == false || empty($res)){
			//统计地区数量
			$sql = 'SELECT DISTINCT `province_id` FROM `radio_info` WHERE `online` = 1';
			$areas = $this->_dbReadBySql($sql);
			$areas = count($areas);
	//		print_r($areas);
	//		exit;
			//统计电台数量
			$sql = 'SELECT COUNT(*) AS radios FROM `radio_info` WHERE `online` = 1';
			$radios = $this->_dbReadBySql($sql);
			$radios = $radios[0]['radios'];
			//统计dj数量
			$sql = 'SELECT b.uids FROM `radio_info` a JOIN `radio_dj_info` b WHERE a.rid=b.rid';
			$djInfo = $this->_dbReadBySql($sql);
			$sum = 0;//js数量计数器
			foreach($djInfo as $v){
				$tmp = explode(',',$v['uids']);
				$sum +=count($tmp);
			}
			
			$res = array(
				'areas'		=>	$areas,
				'radios'	=>	$radios,
				'djs'		=>	$sum
				);
			$this->setCacheData($key,$res,86400);//缓存10分钟
		}
		return $res;
	}

    public function getWeiBoCardByUid($uid,$fromdb = false){
        if(empty($uid)){
            return $this->returnFormat('RADIO_D_CK_00001');
        }
        $mc_key = sprintf(MC_KEY_RADIO_WEIBO_CARD_UID,$uid);
        $card_info = $this->getCacheData($mc_key);
        if($card_info ==false || $fromdb){
            $sql = 'SELECT `rid`,`domain`,`info`,`intro`,`province_spell`,`admin_uid` FROM radio_info where `online`=1 AND `admin_uid` = ?';
            $card_info = $this->queryData($sql,array($uid));
            if(!empty($card_info)){
                $card_info = $card_info[0];
				$radio_name = explode('|',$card_info['info']);
                $card_info['radio_url'] = 'http://radio.weibo.com/'.$card_info['province_spell'].'/'.$card_info['domain'];
                $card_info['radio_name'] = $radio_name[0];
                $this->setCacheData($mc_key,$card_info,38400);
            }
        }
        return $card_info;
    }


}
?>
