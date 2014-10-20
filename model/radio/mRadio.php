<?php
/**
 *
 * 电台的model层
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
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT.'dagger/libs/extern.php';

class mRadio extends model {
	/**
	 *
	 * 获取电台列表 按省份分好了
	 */
	public function getRadioList($fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$aRadioInfo = $objRadioInfo->getRadioList($fromdb);
		if($aRadioInfo['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $aRadioInfo;
	}
	
	/**
	 *
	 * 获取电台列表 
	 */
	public function getAllRadioList($online=1,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$allRadioInfo = $objRadioInfo->getAllRadioList($online,$fromdb);
		if($allRadioInfo['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $allRadioInfo;
	}
	/**
	 *
	 * 根据传递的cid和pid获取电台列表
	 *	$arr=array('cid'=>分类id,'pid'=>地区id)
	 */
	public function sortRadioList($arr,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$sortRadioList = $objRadioInfo->sortRadioList($arr,$fromdb);
		if($sortRadioList['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $sortRadioList;
	}


	/**
	 *
	 * 获取所有电台配置列表
	 */
	public function getRadioStream($fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$aRadioInfo = $objRadioInfo->getRadioList($fromdb);
		if($aRadioInfo['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		if( !empty($aRadioInfo['result']) ){
			foreach($aRadioInfo['result'] as $val){
				foreach($val as $k=>$v){
				$radio_stream[$k]['epg_id'] = $v['epgid'];
				$radio_stream[$k]['radio_fm'] = $v['domain'];
				$radio_stream[$k]['http'] = $v['http'];
				$radio_stream[$k]['mu'] = $v['mu'];
				$radio_stream[$k]['start_time'] = $v['start_time'];
				$radio_stream[$k]['end_time'] = $v['end_time'];
				}
			}
		}
			//	error_log(strip_tags(print_r($radio_stream, true))."\n", 3, "/tmp/error.log");

		return $radio_stream;
	}
	/*
	 * 根据地区id获取电台信息
	 * @param array $pid
	 * @param array
	 */
	public function getRadioInfoByPid($pid,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioInfoByPid($pid,$fromdb);
	}

	/*
	 * 根据分类id获取电台信息
	 * @param int $pid
	 * @param array
	 */
	public function getRadioInfoByClassificationids($cids,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioInfoByClassificationids($cids,$fromdb);
	}

	/*
	 *更新所有类别电台信息
	 */
	public function updateAllRadioInfoByClassificationids(){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->updateAllRadioInfoByClassificationids();
	}

	/**
	 *
	 * 获取电台详情
	 * @param string $rid 电台id
	 * @param array  $aRadioList 电台列表
	 */
	public function getRadioInfoByRid($rids,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$aRadioInfo = $objRadioInfo->getRadioInfoByRid($rids,$fromdb);

		if($aRadioInfo['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		//@test 此处可能被接口调用 后期请做判断是否去掉
		//$aRadioInfo['result']['domain'] = strtolower($aRadioInfo['result']['domain']);
		return $aRadioInfo;
	}

	/**
	 *
	 * 获取正在收听电台的听众
	 * @param int $rid
	 */
	public function getListeners($rid, $uid){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}

		$objRadioDj = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioListeners', 'service');
		$aListeners = $objRadioDj->getCurrentListeners($rid, $uid);

		if($aListeners['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $aListeners;
	}

	/**
	 *
	 * 获取正在收听电台的听众
	 * @param int $rid
	 */
	public function getCurrentListeners2($rid, $uid){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid);
			return $this->returnFormat('RADIO_00001');
		}

		$objRadioDj = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioListeners', 'service');
		$aListeners = $objRadioDj->getCurrentListeners2($rid, $uid);

		if($aListeners['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $aListeners;
	}

	/**
	 *
	 * 更新feedlist
	 */
	public function updateFeedListByRid($rid,$page = 1){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioFeed', 'service');
		return $objRadioFeed->updateFeedByRid($rid,$page);
	}

	/**
	 *
	 * 更新正在听list
	 */
	public function updateCurrentListeners(){
		$objListeners = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioListeners', 'service');
		return $objListeners->updateCurrentListeners();
	}

	/**
	 *
	 * 用户行为日志
	 * @param array $args
	 * $args = array(
	 *		'typeid'   => $this->para['typeid'],
	 *		'radioid'  => $this->para['radioid'],
	 *		'from'     => $this->para['from'],
	 *		'mblogid'  => $this->para['mblogid'],
	 *		'optime'   => $strTime,
	 *		'serverip' => $strServiceIp,
	 *		'clientip' => $strRemoteIp,
	 *		'opuid'    => $strUid
	 *	);
	 */
	public function writeUserActionLog($args){
		if (empty($args)){
			return $this->returnFormat('RADIO_00001');
		}

		$objWrite = clsFactory::create(CLASS_PATH.'data/radio', 'dWriteActionLog', 'service');
		$result = $objWrite->writeUserActionLog($args);
		if($result['errorno'] != 1){
			$this->writeRadioErrLog($result, 'userAction');
		}
		return $result;
	}

	/**
	 * 发表评论
	 * @param $args参数数组包含如下参数项
	 *
	 *	uid=>当前用户UID(必选),
	 *	srcuid=>资源所属者UID(微博客发表人ID)必选,
	 *	srcid=>微博客ID(必选),
	 *	content=>评论内容（必选）,
	 *	isreply=>是否为回复某条评论(必选，0为评论微博客，1为回复评论),
	 *	res=>微博客内容(可选)，
	 *	rcmtid=>回复的评论ID(只有isreply为1是才需要此参数),
	 *	cmtuid=>回复的评论的作者UID(只有isreply为1是才需要此参数),
	 *	appid=>来源ID(可选，默认为6(来自WAP)),
	 * @return array
	 */
	public function addComment($data){
		if(empty($data) || !is_array($data)){
			return false;
		}
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->addComment($data);
	}

	/**
	 * 回复评论接口
	 * @param array $data
	 */
	public function replyComment($data){
		if(empty($data) || !is_array($data)){
			return false;
		}
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->replyComment($data);
	}

	/**
	 * 删除评论接口
	 * @param array $data
	 */
	public function delComment($data){
		if(empty($data) || !is_array($data)){
			return false;
		}
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->delComment($data);
	}

	/**
	 * 获取微博信息（批量）
	 */
	public function getMblog($mids){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->getMblog($mids);
	}
	
	/**
	 * 发布微博动态接口
	 * @param array $data
	 */
	public function addMblogActivity($data){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->addMblogActivity($data);
	}
	/**
	 * 发布微博接口
	 * @param array $data
	 */
	public function addMblog($data){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		if(!empty($data['pic_id'])){
			return $objRadio->addMblogHasPice($data);
		}
		else{
			return $objRadio->addMblogOnlyText($data);
		}
	}

	/**
	 * 获取电台附加信息（发微博接口参数annotations元数据）
	 * @param $rid	电台id
	 */
	public function getRadioAnnotations($rid){
		$radioInfo = $this->getRadioInfoByRid(array($rid));
		$radioInfo = $radioInfo['result'][$rid];
		$data = array();
		if(!empty($radioInfo)){
			$strTitle = str_replace('|', '', $radioInfo['info']);
			$title = htmlspecialchars_decode($strTitle);
			$title = preg_replace("/<(.*?)>/","",$title);
			$appTitle = $title;
			if(mb_strlen($appTitle, 'UTF-8') > 9) {
				$appTitle = htmlspecialchars(mb_substr($appTitle, 0, 9, 'UTF-8')) . "...";
			}
			$data[] = array("id" => ""
					,'appid' => RADIO_SOURCE_APP_ID
          			,'name' => $title
          			,'title' => $appTitle
          			,'url'=> RADIO_URL."/".$radioInfo['province_spell']."/".$radioInfo['domain']
          			,'server_ip'=>tCheck::getIp());
		}
		return json_encode($data);
	}

	/**
	 * 转发微博并添加评论
	 * @param $mid 微博mid
	 * @param $reason 转发理由
	 * @param $isComment 是否添加评论
	 * @return array
	 */
	public function repostMblog($data){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->repostMblog($data);
	}

	/**
	 * 将i.api.weibo.com接口返回的微博信息转换为电台feed的数据格式
	 * @param array $data
	 * @return array
	 */
	public function formatFeed($data){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->formatFeed($data);
	}


	/**
	 * 获取评论列表接口
	 * @param array $data
	 */
	public function getCommentList($mid){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->getCommentList($mid);
	}

		/**
	 * 长链转短链接口
	 * @param array $data
	 */
	public function long2short_url($long_url){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->long2short_url($long_url);
	}

	/**
	 * 格式化返回结果
	 * @param $errno
	 * @param $result
	 * @return unknown_type
	 */
	private function returnFormat($errorno, $result = array()){
		$res = array(
			'errorno' => $errorno,
			'result'  => $result
		);
		return $res;
	}

	/**
	 *
	 * 写日志
	 * @param unknown_type $errorMes
	 * @param unknown_type $filename
	 */
	private function writeRadioErrLog($errorMes, $filename = "") {
		if(empty($errorMes)){
			return false;
		}
		$objLog = clsFactory::create ( 'framework/tools/log/', 'ftLogs', 'service' );
		$objLog->switchs ( 1 ); //1 开    0 关闭
		$objLog->write ( 'radio', $errorMes, 'model_' . $filename );
	}

	/**
	 * 获得电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $whereArgs domain,info,tag,source,recommend,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getRadio($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['search_key'])){
			if(preg_match('/&/',$args['search_key'])){
				$delimiter = '&';
			}
			if(preg_match('/\|/',$args['search_key'])){
				$delimiter = '|';
			}
			if(!empty($delimiter)){
				$search_key = explode($delimiter,htmlspecialchars_decode($args['search_key']));
				$search_value = explode($delimiter,htmlspecialchars_decode($args['search_value']));
				$search_type = explode($delimiter,htmlspecialchars_decode($args['search_type']));
			}
			if(!empty($search_key) && !empty($search_value) && !empty($search_type)){
				$count = count($search_key);
				$whereArgs = array();
				for($key=0; $key < $count; $key++){
					if(strtolower($search_type[$key]) == 'in'){
						$value = explode(',',$search_value[$key]);
					}
					else{
						$value = $search_value[$key];
					}
					$whereArgs[$search_key[$key]] = $value;
				}
			}
			else{
				if(strtolower($args['search_type']) == 'in'){
					$value = explode(',',$args['search_value']);
				}
				else{
					$value = $args['search_value'];
				}
				$whereArgs = array($args['search_key'] => $value);
			}

		}
		$postfixArgs = array('search_type' => !empty($search_type) ? $search_type : $args['search_type'],
							'field' => $args['order_field'],
							'order' => $args['order'],
							'page' => $args['page'],
							'pagesize' => $args['pagesize']
							);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadio($whereArgs, $postfixArgs);
	}

	/**
	 * 添加电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$checkRadio = $this->checkRadio($args, 'add');
		if($checkRadio['errorno'] !== 1) return $this->returnFormat($checkRadio['errorno']);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->addRadio($args);
	}

	/**
	 * 编辑电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if ((int)$args['rid'] == 0){
			$checkRadio = $this->checkRadio($args, 'set');
			if($checkRadio['errorno'] !== 1) return $this->returnFormat($checkRadio['errorno']);
		}

		$tmpArgs = $args;
		unset($tmpArgs['rid']);
		$whereArgs = array('rid' => $args['rid']);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->setRadio($tmpArgs, $whereArgs);
	}
	/**
	 * 编辑电台分类
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setRadioClassification($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$tmpArgs = array('classification_id' =>0);
		$whereArgs = array('classification_id' => $args['classification_id']);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->setRadio($tmpArgs, $whereArgs);
	}
	/**
	 * 删除电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args rids
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('rid' => is_numeric($args['rids']) ? $args['rids'] : explode(',', $args['rids']));
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->delRadio($whereArgs);
	}

	/**
	 * 验证电台信息
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @param string $type add,set
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function checkRadio($args, $type = 'set') {
		$checkArgs = array(
			'rid'		=>	array('is' => 'int'),
			'domain'	=>	array('len' => array('min' => 1, 'max' => 50)),
			'info'		=>	array('len' => array('min' => 1, 'max' => 200, 'charset' => 'gbk'), 'fun' => count(explode('|', $args['info'])) > 1),
			'tag'		=>	array('len' => array('min' => 1, 'max' => 200, 'charset' => 'gbk')),
			'source'	=>	array('len' => array('min' => 1, 'max' => 200)),
			// 'recommend'	=>	array('is' => 'int'),
			'upuid'		=>	array('len' => array('min' => 1, 'max' => 20)),
			// 'uptime'	=>	array('fun' => true)
		);
		if($type == 'add') unset($checkArgs['rid']);
		return $this->_check($args, $checkArgs);
	}

	/**
	 * 数据验证
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param array $sourceArgs 源数组
	 * @param array $checkArgs 验证数组
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	protected function _check($sourceArgs, $checkArgs) {
		foreach($checkArgs as $key => $value) {
			foreach($value as $k => $v) {
				if($k == 'is') {
					switch($v) {
						case 'empty' :
							if(empty($sourceArgs[$key]) && $sourceArgs[$key] !== 0) {
								$unEmpty = false;
								continue;
							} else {
								$unEmpty = true;
							}
						break;
						case 'int' :
							if(!is_int($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNINT');
						break;
						case 'float' :
							if(!is_float($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNFLOAT');
						break;
						case 'numeric' :
							if(!is_numeric($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNNUMERIC');
						break;
						case 'array' :
							if(!is_array($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNARRAY');
						break;
						case 'bool' :
							if(!is_bool($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNBOOL');
						break;
						case 'string' :
							if(!is_string($sourceArgs[$key])) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNSTRING');
						break;
					}
				}
				if(empty($sourceArgs[$key])){
					if($unEmpty === true){
						return $this->returnFormat('RADIO_M_CK_' . $key . '_UNEMPTY');
					}
				}
				if($k == 'fun') {
					if($v !== true && is_bool($v)) {
						return $this->returnFormat('RADIO_M_CK_' . $key . '_UNFUN');
					} else {
						// 调用公有的方法比如同时支持电话和手机
					}
				}
				if($k == 'in') {
					if(!in_array($sourceArgs[$key], $v)) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNIN');
				}
				if($k == 'len') {
					if($v['min'] > $v['max']) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNMINMAX');
					if(empty($v['charset'])) {
						if(strlen($sourceArgs[$key]) < $v['min']) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNMIN');
						if(strlen($sourceArgs[$key]) > $v['max']) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNMAX');
					} else {
						$tmpCharset = empty($v['charset']) ? 'UTF-8' : $v['charset'];
						if(mb_strlen($sourceArgs[$key], $tmpCharset) < $v['min']) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNMIN');
						if(mb_strlen($sourceArgs[$key], $tmpCharset) > $v['max']) return $this->returnFormat('RADIO_M_CK_' . $key . '_UNMAX');
					}
				}
			}
		}
		return $this->returnFormat(1);
	}


	/**
	 *
	 * 获取关注关系
	 */
	public function checkattrelation($uid,$fuid){
		$dRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $dRadioInfo->checkattrelation($uid,$fuid);
	}

	/**
	 * 根据province_id判断并添加地区
	 */
	public function addRadioArea($pid){
		$dRadioArea = clsFactory::create(CLASS_PATH.'data/radio','dRadioArea','service');
		return $dRadioArea->addRadioArea($pid);
	}

	/**
	 * 根据province_id判断并删除地区
	 */
	public function delRadioArea($pid){
		$dRadioArea = clsFactory::create(CLASS_PATH.'data/radio','dRadioArea','service');
		return $dRadioArea->delRadioArea($pid);
	}

	/**
	 * 设置电台官方微博信息
	 * @param array $args
	 * @return array
	 */
	public function setMinfo($args){
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$checkRadio = $this->checkMinfo($args);
		if($checkRadio['errorno'] !== 1) return $this->returnFormat($checkRadio['errorno']);
		$tmpArgs = $args;
		unset($tmpArgs['rid']);
		$whereArgs = array('rid' => $args['rid']);
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->setRadio($tmpArgs, $whereArgs);
	}

	/**
	 * 验证电台信息
	 * @param array $args domain,info,tag,source,recommend,upuid,uptime
	 * @param string $type add,set
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function checkMinfo($args) {
		$checkArgs = array(
			'rid'		=>	array('is' => 'int'),
			'intro'		=>	array('len' => array('min' => 0, 'max' => 301, 'charset' => 'gbk')),
		);
		return $this->_check($args, $checkArgs);
	}

	/**
	 * 获取地区列表
	 * @return array
	 */
	public function getAreaList($fromdb = false){
		$dRadioArea = clsFactory::create(CLASS_PATH.'data/radio','dRadioArea','service');
		return $dRadioArea->getAreaList($fromdb);
	}

	/**
	 * 设置地区信息
	 * @param array $args
	 * @return array
	 */
	public function setArea($set,$setcache = true){
		$province_id = $set['province_id'];
		unset($set['province_id']);
		$whereArgs = array('province_id' => $province_id);
		$dRadioArea = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioArea', 'service');
		return $dRadioArea->setArea($set, $whereArgs,$setcache);
	}

	/**
	 * 获取电台主持人信息
	 * @param array $args
	 * @return array
	 */
	public function getDjInfoByRid($rids,$fromdb = false){
		if(!is_array($rids) || empty($rids)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$dRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $dRadioDjInfo->getDjInfoByRid($rids,$fromdb);
	}

	/**
	 * 添加主持人信息
	 * @param array $args rid,publink,uids,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addDjInfo($data) {
		$checkDj = $this->checkDjInfo($data, 'add');
		if($checkDj['errorno'] !== 1){
			return $this->returnFormat($checkDj['errorno']);
		}

		$dRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $dRadioDjInfo->addDjInfo($data);
	}

	/**
	 * 编辑主持人信息
	 * @param array $args rid,publink,uids,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setDjInfo($data) {
		$checkDj = $this->checkDjInfo($data, 'set');
		if($checkDj['errorno'] !== 1){
			return $this->returnFormat($checkDj['errorno']);
		}
		$rid = $data['rid'];
		unset($data['rid']);
		$where = array('rid' => $rid);
		$dRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $dRadioDjInfo->setDjInfo($data, $where);
	}

	/**
	 * 验证主持人信息
	 * @param array $args rid,publink,uids,upuid,uptime
	 * @param string $type add,set
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function checkDjInfo($args, $type = 'set') {
		$checkArgs = array(
			'rid'		=>	array('is' => 'int'),
			'publink'	=>	array('len' => array('min' => 0, 'max' => 200)),
//			'uids'		=>	array('len' => array('min' => 1, 'max' => 1000)),
			'upuid'		=>	array('len' => array('min' => 1, 'max' => 20)),
			// 'uptime'	=>	array('fun' => true)
		);
		return $this->_check($args, $checkArgs);
	}

	/**
	 *
	 * 获取电台推荐主持人
	 * @param string $rid  电台id
	 */
	public function getDjDetail($rid, $uid , $fromdb = false){
		if(empty($rid)){
			//参数失败
			$this->writeRadioErrLog(array('errno'=>RADIO_00001).'参数错误  rid='.$rid );
			return $this->returnFormat('RADIO_00001');
		}
		$objRadioDjInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjInfo', 'service');
		$aDj = $objRadioDjInfo->getDjDetail($rid,$uid,$fromdb);

		if($aDj['errorno'] != 1){
			return $this->returnFormat('RADIO_00003');
		}
		return $aDj;
	}

	/**
	 * 格式化时间
	 * @param int $time
	 */
	public function timeFormat($time){
		$objRadio = clsFactory::create(CLASS_PATH.'data/radio', 'dRadio', 'service');
		return $objRadio->timeFormat($time);
	}

	/**
	 * 获得黑名单列表信息
	 * @param $args
	 * @return array
	 */
	public function getBlack($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['page']) && !empty($args['pagesize'])) {		// 支持分页查询
			$tmpArgs = $args;
			unset($tmpArgs['page']);
			unset($tmpArgs['pagesize']);
			$whereArgs = $tmpArgs;
			$postfixArgs = array(
				'field' => 'uptime',
				'order' => 'DESC',
				'page' => $args['page'],
				'pagesize' => $args['pagesize']
			);
		} else {		// 支持查询全部
			if (isset($args['uid']) && (int)$args['uid'] > 0) {
				$whereArgs = array('uid' => intval($args['uid']));
			}else{
				$whereArgs = array();
				$postfixArgs = array('field' => 'uptime','order' => 'DESC');
			}
		}
		$objBlack = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioBlack', 'service');
		return $objBlack->getBlack($whereArgs, $postfixArgs);
	}


	/**
	 * 添加黑名单
	 * @param array $args uid,url,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addBlack($data) {
		$objBlack = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioBlack', 'service');
		return $objBlack->addBlack($data);
	}

	/**
	 * 删除黑名单
	 * @param array $args uid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delBlack($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('uid' => is_numeric($args['uid']) ? $args['uid'] : explode(',', $args['uid']));
		$objBlack = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioBlack', 'service');
		return $objBlack->delBlack($whereArgs);
	}



	/**
	 * 添加统一推荐管理  -- 轮播大图
	 * @param array $args rid link_url pic_url upuid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addPicInfo($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPicInfo', 'service');
		return $objHome->addPicInfo($args);
	}

	/**
	 * 获得轮播大图的信息
	 * @param $args
	 * @return array
	 */
	public function getPicInfo($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['pic_id'])) {		// 支持单个和批量查询
			$whereArgs = array('pic_id' => is_numeric($args['pic_id']) ? $args['pic_id'] : explode(',', $args['pic_id']));
			$postfixArgs = array();
		} else if(!empty($args['page']) && !empty($args['pagesize'])) {		// 支持分页查询
			$tmpArgs = $args;
			unset($tmpArgs['page']);
			unset($tmpArgs['pagesize']);
			$whereArgs = $tmpArgs;
			$postfixArgs = array(
				'page' => $args['page'],
				'pagesize' => $args['pagesize']
			);
		} else {		// 支持查询全部
			$whereArgs = array();
			$postfixArgs = array();
		}
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPicInfo', 'service');
		return $objHome->getPicInfo($whereArgs, $postfixArgs);
	}
	
		/**
	 * 获得首页左侧推荐图的信息
	 * @param $args
	 * @return array
	 */
	public function getLeftPic($fromdb = false) {
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPicInfo', 'service');
		return $objHome->getLeftPic($fromdb);
	}


	/**
	 * 删除轮播图片
	 * @param array $args uid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delPicInfo($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('pic_id' => is_numeric($args['pic_id']) ? $args['pic_id'] : explode(',', $args['pic_id']));
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPicInfo', 'service');
		return $objHome->delPicInfo($whereArgs);
	}

	/**
	 * 添加统一推荐管理  -- 热门电台信息
	 * @param array $args rid link_url pic_url upuid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addHotRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHome', 'service');
		return $objHome->addHotRadio($args);
	}

	/**
	 * 获得推荐的热门电台信息
	 * @param $args
	 * @return array
	 */
	public function getHotRadio($fromdb = false) {
		$objRadioHotInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHotInfo', 'service');
		return $objRadioHotInfo->getHotRadio($fromdb);
	}


	/**
	 * 删除推荐的热门电台信息
	 * @param array $args uid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delHotRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('rid' => is_numeric($args['rid']) ? $args['rid'] : explode(',', $args['rid']));
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHome', 'service');
		return $objHome->delHotRadio($whereArgs);
	}

	/**
	 * 获得地区推荐电台
	 * @param $args
	 * @return array
	 */
	public function getRadioProvince($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['province_id'])) {		// 支持单个和批量查询
			$whereArgs = array('province_id' => is_numeric($args['province_id']) ? $args['province_id'] : explode(',', $args['province_id']));
			$postfixArgs = array();
		} else if(!empty($args['page']) && !empty($args['pagesize'])) {		// 支持分页查询
			$tmpArgs = $args;
			unset($tmpArgs['page']);
			unset($tmpArgs['pagesize']);
			$whereArgs = $tmpArgs;
			$postfixArgs = array(
				'page' => $args['page'],
				'pagesize' => $args['pagesize']
			);
		} else {		// 支持查询全部
			$whereArgs = array();
			$postfixArgs = array();
		}
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHome', 'service');
		return $objHome->getRadioProvince($whereArgs, $postfixArgs);
	}

	/**
	 * 添加地区首页的信息
	 * @param array $args province_id rolling_picture publink dj_info right_picture
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRadioProvince($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHome', 'service');
		return $objHome->addRadioProvince($args);
	}

	/**
	 * 编辑设置地区首页的信息
	 * @param array $args province_id rolling_picture publink dj_info right_picture
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setRadioProvince($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$tmpArgs = $args;
		unset($tmpArgs['province_id']);
		$whereArgs = array('province_id' => $args['province_id']);
		$objHome = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioHome', 'service');
		return $objHome->setRadioProvince($tmpArgs, $whereArgs);
	}

	/**
	 * 获取电台收听排行榜
	 * @param int $num	//获取数量
	 * @return array
	 */
	public function getListenRank($num = 10){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getListenRank($num);
	}

	/**
	 * 更新全部电台收听排行榜缓存
	 * @param bool $fromdb
	 */
	public function updateListenRank($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateListenRank($fromdb);
	}

	/**
	 * 根据地区id获取电台收听排行榜
	 * @param int $num	//获取数量
	 * @param int $pid	//地区id
	 * @return array
	 */
	public function getListenRankByPid($pid,$num = 10,$date = 0){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getListenRankByPid($pid,$num,$date);
	}

	/**
	 * 更行全部电台收听排行榜（按地区） 废弃啦
	 * @param bool $fromdb
	 */
	public function updateListenRankByProvince($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateListenRankByProvince($fromdb);
	}
	//启用
	public function updateListenRankByProvince2($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateListenRankByProvince2($fromdb);
	}

	/**
	 * 根据官方微博uid获取电台信息
	 */
	public function getRadioByUid($uids,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioByUid($uids,$fromdb);
	}

	/**
	 * 根据官方微博uid获取dj用户id
	 */
	public function getDjInfoByUids($uids,$fromdb = false){
		$radioinfo = $this->getRadioByUid($uids,$fromdb);
		if($radioinfo['errorno'] != 1){
			return $radioinfo;
		}
		foreach($radioinfo['result'] as $val){
			$rids[] = $val['rid'];
		}
		return $this->getDjInfoByRid($rids);
	}

	/**
	 * 调取基础服务接口，批量发送私信
	 * @param int $fromuid
	 * @param string $content
	 * @param string $touids
	 *
	 */
	public function sendMessageMulti($fromuid,$content,$touids){
                $objRadio = clsFactory::create(CLASS_PATH . "data/radio","dRadio","service");
                return $objRadio->sendMessageMulti($touids[0], $content, $fromuid);
                /*
		$objBasic = clsFactory::create(CLASS_PATH . "data/radio", "dRadioBasic", "service" );
		$args['cuid'] = $fromuid;
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$args['cip'] = tCheck::getIp();
		$args['appkey'] = RADIO_SOURCE_APP_KEY;
		$args['fromuid'] = $fromuid;
		$args['content'] = $content;
		$args['touids'] = $touids;
		return $objBasic->sendMessageMulti($args);
                */
	}

	/**
	 * 添加收藏电台
	 * @param array $args
	 */
	public function addRadioCollection($args){
		$objRadioCollection = clsFactory::create(CLASS_PATH . "data/radio","dRadioCollection","service");
		return $objRadioCollection->addRadioCollection($args);
	}

	/**
	 * 根据用户id获取用户收藏电台id
	 * @param int $uid
	 */
	public function getRadioCollection($uids,$fromdb = false){
		$objRadioCollection = clsFactory::create(CLASS_PATH . "data/radio","dRadioCollection","service");
		return $objRadioCollection->getRadioCollection($uids,$fromdb);
	}

	/**
	 * 根据用户id获取用户收藏电台列表信息
	 * @param unknown_type $uid
	 */
	public function getCollectionList($uid){
		$objRadioCollection = clsFactory::create(CLASS_PATH . "data/radio","dRadioCollection","service");
		return $objRadioCollection->getCollectionList($uid);
	}

	/**
	 * 更新用户收藏列表信息
	 * @param $args
	 */
	public function updateRadioCollection($args){
		if(empty($args['uid']) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$uid = $args['uid'];
		$rids = $args['rids'];
		$objRadioCollection = clsFactory::create(CLASS_PATH . "data/radio","dRadioCollection","service");
		return $objRadioCollection->updateRadioCollection(array('rids' => $rids),array('uid' => $uid));
	}


	/**
	 * 根据所给时间判断与当前时间间隔是否在配置时间范围内
	 * @param int $time
	 */
	public function checkRadioIsNew($time){
		$interval = time()-$time;
		if($interval <= RADIO_NEW_INTERVAL){
			return true;
		}
		return false;
	}

	/**
	 * 添加电台节目单信息
	 * @param array $args
	 */
	public function addRadioProgram($args){
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgram","service");
		return $objRadioProgram->addRadioProgram($args);
	}

	/**
	 * 根据电台id获取节目单信息
	 * @param int $rid $day
	 */
	public function getRadioProgram($rid,$day,$flag = false){
		if(!is_numeric($rid) || !is_numeric($day)){
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgramV2","service");
		return $objRadioProgram->getRadioProgram($rid,$day,$flag);
	}

	/**
	 * 根据电台id获取节目单信息
	 * @param int $rid $day
	 */
	public function getRadioProgram2($rid,$day,$flag = false){
		if(!is_numeric($rid) || !is_numeric($day)){
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgramV2","service");
		return $objRadioProgram->getRadioProgram2($rid,$day,$flag);
	}

	/**
	 * 根据节目名称获取节目信息
	 * @param int $rid $day
	 * @param int $day 星期几 1-7
	 */
	public function getRadioProgramByName($name,$day,$flag = false){
		if(empty($name) || !is_numeric($day)){
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgramV2","service");
		return $objRadioProgram->getRadioProgramByName($name,$day,$flag);
	}


	/**
	 * 根据节目id获取节目信息
	 * @param int $program_id
	 */
	public function getRadioProgramByProgramId($program_id,$flag = false){
		if(empty($program_id)){
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgramV2","service");
		return $objRadioProgram->getRadioProgramByProgramId($program_id,$flag);
	}

	/**
	 * 根据电台id获取节目单信息
	 * @param int $rid
	 */
	public function getProgramList($rid,$flag = false){
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgram","service");
		return $objRadioProgram->getProgramList($rid,$flag);
	}

	/**
	 * 解析数组返回节目单信息
	 * @param array $args
	 */
	public function getProgramInfo($args){
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgram","service");
		return $objRadioProgram->getProgramInfo($args);
	}

	/**
	 * 获取电台收藏排行榜
	 * @param int $num		//获取个数
	 */
	public function getCollectionRank($num = 10){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getCollectionRank($num);
	}

	/**
	 * 更新电台收藏排行榜缓存
	 * @param bool $fromdb
	 */
	public function updateCollectionRank($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateCollectionRank($fromdb);
	}

	/**
	 * 判断字符串是否含有非法字符
	 * @param string $str
	 */
	public function checkKeyWord($str){
		$objRadio = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
		return $objRadio->checkKeyWord($str);
	}

	/**
	 * 根据用户id获取用户是否收藏过电台
	 * @param int $uid,$rid
	 * @return bool
	 */
	public function hasCollected($uid,$rid){
		$objRadioCollection = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioCollection', 'service');
		return $objRadioCollection->hasCollected($uid,$rid);
	}

	/**
	 * 删除电台的公告信息
	 */
	public function delRadioNotice(){
		$objRadioNotice = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNotice', 'service');
		return $objRadioNotice->delRadioNotice();
	}
	
	/**
	 * 添加电台公告信息
	 * @param array $args
	 * @return array
	 */
	public function addRadioNotice($args){
		$objRadioNotice = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNotice', 'service');
		return $objRadioNotice->addRadioNotice($args);
	}

	/**
	 * 获取电台公告信息
	 * @return array
	 */
	public function getRadioNotice(){
		$objRadioNotice = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNotice', 'service');
		return $objRadioNotice->getRadioNotice($fromdb = false);
	}

	/**
	 * 判断用户是否为电台的当前在线dj
	 * @param int $uid
	 * @param int $rid
	 */
	public function isCurrentDj($uid,$rid) {
		$today = date('N');
		$programs = $this->getRadioProgram2($rid,$today);
		if(empty($programs)){
			return false;
		}
		foreach($programs as $value){
			if(strtotime($value['begintime']) <= time() && strtotime($value['endtime']) > time() && !empty($value['dj_info'][$uid])){
				return strtotime($value['endtime']);
			}
		}
		return false;
	}

	/**
	 * 添加在线dj的feed
	 * @param array $mids
	 * @param int $rid
	 * @param int $liveTime
	 */
	public function addDjFeed($mids,$rid,$liveTime){
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $objRadioDjInfo->addDjFeed($mids,$rid,$liveTime);
	}

	/**
	 * 获取在线dj的feed
	 * @param int $rid
	 *
	 */
	public function getDjFeed($rid){
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $objRadioDjInfo->getDjFeed($rid);
	}

	/**
	 * 删除在线dj的feed
	 * @param string $mid
	 * @param int $rid
	 * @param int $liveTime
	 */
	public function delDjFeed($mid,$rid,$liveTime){
		$objRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $objRadioDjInfo->delDjFeed($mid,$rid,$liveTime);
	}

	/**
	 * 获取微电台官方微博信息
	 * @param int $uid
	 */
	public function getOfficialMinfo($uid){
		$objRadio = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
		return $objRadio->getOfficialMinfo($uid);
	}
	
		/**
	 * 更新短链接接口
	 * @param char $radiourl
	 */
	public function updateShortUrl($radiourl){
		$objRadio = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
		return $objRadio->updateShortUrl($radiourl);
	}
	
	/**
	 * 根据province_spell和domain获取radio信息
	 * @param unknown_type $args
	 */
	public function getRadioByDomainAndPro($domain,$province_spell){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioByDomainAndPro($domain,$province_spell);
	}

	public function getRadioByPidAndDomain($pid,$domain,$fromdb = false){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getRadioByPidAndDomain($pid,$domain,$fromdb);
	}

	/**
	 * 根据rid获取crontab跑出来的缓存数据
	 * @param $rid
	 */
	public function getNewsfeedByCrontab($province_spell,$domain){
		if(!$province_spell || !$domain) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getNewsfeedByCrontab($province_spell,$domain);
	}

	/**
	 * crontab 调用的最新微博信息的更新cache接口
	 * @param $rid
	 */
	public function updateCrontabWeiboCache($key_word,$province_spell,$search_type,$domain){
		if(!$province_spell || !$domain) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->updateCrontabWeiboCache($key_word,$province_spell,$search_type,$domain);
	}

	/**
	 *
	 * 从落地缓存中获取正在收听的所有用户List
	 * @param unknown_type $rid
	 */
	public function getAllListenersByMc($rid){
		if(!$rid) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$objRadioListeners = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioListeners', 'service');
		return $objRadioListeners->getAllListenersByMc($rid);
	}

	/**
	 * 提供给搜索所有的电台列表
	 */
	public function getAllOnlineRadio($fromdb = false) {
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getAllOnlineRadio($fromdb);
	}

	/**
	 * 更新所有电台节目单缓存
	 */
	public function updateAllRadioProgram(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->updateAllRadioProgram();
	}

	/**
	 * 更新所有电台节目单缓存
	 */
	public function updateAllRadioProgramV2(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->updateAllRadioProgramV2();
	}

	/**
	 * 根据地区id获取当前正在直播的节目
	 * @param int pid
	 * @return array
	 */
	public function getProgramNowByPid($pid,$wday = -1){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getProgramNowByPid($pid,$wday);
	}

	/**
	 * 根据地区id获取当前正在直播的dj
	 * @param int pid
	 * @return array
	 */
	public function getDjNowByPid($pid,$wday = -1){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getDjNowByPid($pid,$wday);
	}

	/**
	 * 根据电台id获取电台节目为维度的节目单数据
	 * @param int pid
	 * @return array
	 */
	public function getProgramForNameByRid($rid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getProgramForNameByRid($rid);
	}

	/**
	 * 更新用户电台节目单缓存
	 * @param int $rid
	 * @param string $day
	 * @param array $data
	 */
    public function updateProgramMc($rid, $day){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->updateProgramMc($rid, $day);
    }

	/**
	 * 根据电台id获取电台dj主持的节目信息
	 * @param int rid
	 * @return array
	 */
	public function getDjProgramByRid($rid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getDjProgramByRid($rid);
	}

	/**
	 * 更新热门节目（定时任务）
	 */
	public function updateHotProgramByDay(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->updateHotProgramByDay();
	}
	/**
	 * 更新热门节目（定时任务）
	 */
	public function updateHotProgramByDay2(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->updateHotProgramByDay2();
	}

	/*
	 * 通过时间获取热门节目
	 * @param $pid
	 * @return array
	 */
	public function getHotProgramByDay($hour = -1){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getHotProgramByDay($hour);
	}
	/*
	 * 获取当天的热门节目
	 * @param $pid
	 * @return array
	 */
	public function getHotProgramByDay2(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getHotProgramByDay2();
	}

	/**
	 * 更新热门节目（定时任务）
	 */
	public function updateHotProgram(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->updateHotProgram();
	}

	/**
	 * 更新热门节目（定时任务）
	 */
	public function updateHotProgram2(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->updateHotProgram2();
	}

	/*
	 * 获取热门节目排行榜
	 * @return array
	 */
	public function getHotProgramRank($num = 10){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getHotProgramRank($num);
	}

	/*
	 * 根据用户id获取其主持的节目信息
	 */
	public function getProgramByUid($uid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getProgramByUid($uid);
	}

	/*
	 * 获取用户名片（微电台专用）
	 * @param int $uid
	 * @return array
	 */
	public function getNameCard($uid,$pname = ''){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getNameCard($uid,$pname);
	}

	/*
	 * 获取电台名片（微电台专用）
	 * @param int $uid
	 * @return array
	 */
	public function getRadioCardByUid($uid,$fromdb = false){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getRadioCardByUid($uid,$fromdb);
	}
	/*
	 * 获取用户简单信息名片（微电台专用）
	 * @param int $uid
	 * @return array
	 */
	public function getSimpleNameCard($uid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getSimpleNameCard($uid);
	}

	/*
	 * 获取用户简单信息名片（微电台专用）
	 * @param array $uids 批量
	 * @return array
	 */
	public function getSimpleNameCard2($uids){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getSimpleNameCard2($uids);
	}
	/**
	 * 根据个性域名获取用户信息
	 */
	public function getUserInfoByDomain($domain){
		$dRadio = clsFactory::create(CLASS_PATH.'data/radio','dRadio','service');
		return $dRadio->getUserInfoByDomain($domain);
	}

	/**
	 * 根据用户id获取用户信息
	 */
	public function getUserInfoByUid($uid){
		$dRadio = clsFactory::create(CLASS_PATH.'data/radio','dRadio','service');
		return $dRadio->getUserInfoByUid($uid);
	}

	/**
	 * 根据用户名称获取用户信息
	 */
	public function getUserInfoByName($names){
		$dRadio = clsFactory::create(CLASS_PATH.'data/radio','dRadio','service');
		return $dRadio->getUserInfoByName($names);
	}

	/**
	 * 获取用户水印信息
	 * @param int $cuid
	 */
	public function getWaterMark($cuid){
		$dRadio = clsFactory::create(CLASS_PATH.'data/radio','dRadio','service');
		return $dRadio->getWaterMark($cuid);
	}

	/*
	 * 更新全部电台feed缓存
	 */
	public function updateAllFeed(){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio','dRadioFeed','service');
		return $objRadioFeed->updateAllFeed();
	}

	/*
	 * 更新全部节目feed缓存
	 */
	public function updateAllProgramFeed(){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio','dRadioProgramFeed','service');
		return $objRadioFeed->updateAllProgramFeed();
	}

	/**
	 *
	 * 获取feedlist
	 * @param string $rid 电台id
	 * @param int $page	页码
	 */
	public function getFeedListByRid($rid,$page = 1){
		//获取feedList
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioFeed', 'service');
		return $objRadioFeed->getFeedListByRid($rid,$page);
	}

	/**
	 *
	 * 获取feedlist
	 * @param string $rid 电台id
	 * @param int $page	页码
	 */
	public function getFeedListByProgramName($pgname,$page = 1){
		//获取feedList
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioProgramFeed', 'service');
		return $objRadioFeed->getFeedListByProgramName($pgname,$page);
	}

	/**
	 *
	 * 获取是否存在新feed
	 * @param string $starttime  查询起始日期
	 * @param string $rid	电台id
	 * @param string $mid	起始微博mid
	 * @return array
	 */
	public function checkNewFeed($starttime, $rid){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioFeed', 'service');
		return $objRadioFeed->checkNewFeed($starttime,$rid);
	}

	public function checkNewProgramFeed($starttime, $pgname){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioProgramFeed', 'service');
		return $objRadioFeed->checkNewProgramFeed($starttime,$pgname);
	}

	/**
	 *
	 * 获取新feed
	 * @param string $starttime  查询起始日期
	 * @param string $rid	电台id
	 * @param string $mid	起始微博mid
	 * @return array
	 */
	public function getNewFeed($starttime,$rid){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioFeed', 'service');
		return $objRadioFeed->getNewFeed($starttime,$rid);
	}

	public function getNewProgramFeed($starttime,$pgname){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioProgramFeed', 'service');
		return $objRadioFeed->getNewProgramFeed($starttime,$pgname);
	}

	/**
	 *
	 * 获取第一条feed的信息
	 * @param string $rid 电台id
	 */
	public function getFirstFeedInfo($rid){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioFeed', 'service');
		return $objRadioFeed->getFirstFeedInfo($rid);
	}

	public function getFirstProgramFeedInfo($pgname){
		$objRadioFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioProgramFeed', 'service');
		return $objRadioFeed->getFirstProgramFeedInfo($pgname);
	}

	/**
	 * 更新轮播图片信息
	 * @param $args
	 */
	public function updatePicInfo($args){
		if(empty($args['pic_id']) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$pic_id = $args['pic_id'];
		$objRadioPicInfo = clsFactory::create(CLASS_PATH . "data/radio","dRadioPicInfo","service");
		return $objRadioPicInfo->updatePicInfo($args,array('pic_id' => $pic_id));
	}

	/**
	 *
	 * 更新feedlist
	 */
	public function updateDjFeedListByRid($rid,$page = 1){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->updateDjFeedListByRid($rid,$page);
	}

	/*
	 * 通过rid和页码获取电台feed
	 * @param string $rid	电台id
	 * @param int $page		页码
	 * @return array
	 */
	public function getDjFeedListByRid($rid,$page = 1){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->getDjFeedListByRid($rid,$page);
	}

	/*
	 * 更新全部电台feed
	 */
	public function updateAllDjFeed(){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->updateAllDjFeed();
	}

	/**
	 *
	 * 获取是否存在新feed
	 * @param string $starttime  查询起始日期
	 * @param string $rid	电台id
	 * @param string $mid	起始微博mid
	 * @return array
	 */
	public function checkNewDjFeed($starttime, $rid, $mid){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->checkNewDjFeed($starttime,$rid, $mid);
	}

	/**
	 *
	 * 获取新feed
	 * @param string $starttime  查询起始日期
	 * @param string $rid	电台id
	 * @param string $mid	起始微博mid
	 * @return array
	 */
	public function getNewDjFeed($starttime,$rid){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->getNewDjFeed($starttime,$rid);
	}

	/**
	 *
	 * 获取第一条feed的信息
	 * @param string $rid 电台id
	 */
	public function getFirstDjFeedInfo($rid){
		$objRadioDjFeed = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioDjFeed', 'service');
		return $objRadioDjFeed->getFirstDjFeedInfo($rid);
	}

	/**
	 * 获取存在上线电台的地区列表
	 */
	public function getAreaHasOnline($fromdb = false){
		$objRadioArea = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioArea', 'service');
		return $objRadioArea->getAreaHasOnline($fromdb);
	}

	/*
	 * 获取热门节目排行榜
	 * @return array
	 */
	public function getHotProgramRankByPid($pid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgram', 'service');
		return $objRadioProgram->getHotProgramRankByPid($pid);
	}

	/*
	 * 获取热门节目排行榜
	 * @return array
	 */
	public function getHotProgramRankByPid2($pid){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getHotProgramRankByPid2($pid);
	}

	//所有节目中
	public function getHotProgramTop10(){
		$objRadioProgram = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgram->getHotProgramTop10();
	}

	/**
	 * 更新影响力排行榜
	 * @param bool $fromdb
	 */
	public function updateInfluenceRank($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateInfluenceRank($fromdb);
	}

	/**
	 * 获取影响力榜
	 * @param string $type	//获取类型（日day，周week，月month）
	 * @param int $num		//获取个数
	 */
	public function getInfluenceRank($num = 10,$cuid = 0){
		$objRadioRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getInfluenceRank($num,$cuid);
	}

	/**
	 * 更新微电台用户活跃榜数据
	 * @param bool $fromdb
	 */
	public function updateActiveUserRank($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateActiveUserRank($fromdb);
	}

	/**
	 * 获取微电台的用户活跃榜数据
	 */
	public function getActiveUserRank($num = 10,$cuid = 0){
		$objRadioRank = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getActiveUserRank($num,$cuid);
	}

	/**
	 * 更新dj活跃榜缓存
	 * @param bool $fromdb
	 */
	public function updateActiveDjRank($fromdb = false){
		$objRadioRank = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->updateActiveDjRank($fromdb);
	}

	/**
	 * 获取微电台的DJ活跃榜数据
	 */
	public function getActiveDjRank($num=10,$cuid=0){
		$objRadioRank = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->getActiveDjRank($num,$cuid);
	}

	/**
	 * 获取黑名单列表
	 * @param bool $fromdb
	 */
	public function getRankBlackList($fromdb = false){
		$objRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRank->getRankBlackList($fromdb);
	}

	/**
	 * 根据用户id获取黑名单用户信息
	 * @param array $uids
	 * @param bool $fromdb
	 */
	public function getRankBlackListByUid($uids,$fromdb = false){
		$objRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRank->getRankBlackListByUid($uids,$fromdb);
	}

	/**
	 * 获得活跃榜黑名单
	 * @param $args
	 * @return array
	 */
	public function getRankBlack($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['page']) && !empty($args['pagesize'])) {		// 支持分页查询
			$tmpArgs = $args;
			unset($tmpArgs['page']);
			unset($tmpArgs['pagesize']);
			$whereArgs = $tmpArgs;
			$postfixArgs = array(
				'field' => 'uptime',
				'order' => 'DESC',
				'page' => $args['page'],
				'pagesize' => $args['pagesize']
			);
		} else {		// 支持查询全部
			if(isset($args['uid']) && isset($args['type'])){
				$whereArgs = array('uid' => intval($args['uid']),'type' => $args['type']);
			}else if (isset($args['uid']) && (int)$args['uid'] > 0) {
				$whereArgs = array('uid' => intval($args['uid']));
			}else{
				$whereArgs = array();
				$postfixArgs = array('field' => 'uptime','order' => 'DESC');
			}
		}
		$objRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRank->getRankBlack($whereArgs, $postfixArgs);
	}



	/**
	 * 添加活跃榜黑名单
	 * @param array $args uid,url,upuid,uptime
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRankBlack($data) {
		$objRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRank->addRankBlack($data);
	}

	/**
	 * 删除活跃榜黑名单
	 * @param array $args uid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delRankBlack($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('uid' => is_numeric($args['uid']) ? $args['uid'] : explode(',', $args['uid']));
		$objRank = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRank', 'service');
		return $objRank->delRankBlack($whereArgs);
	}

	/**
	 * 编辑Dj活跃榜
	 *
	 */
	public function setDjRank($args){
		$objRadioRank = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioRank', 'service');
		return $objRadioRank->setDjRank($args);
	}
	
	/**
	 * 
	 * 通过Rid取的djUid的信息
	 */
	public function getAllDjUids($args){
		$dRadioDjInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		return $dRadioDjInfo->getAllDjUids($args);
	}
	
	/**
	 * OpenAPI接口--所有的电台列表
	 */
	public function getAllOnlineForOpen($fromdb = false) {
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getAllOnlineForOpen($fromdb);
	}
	
	/**
	 * 获取不稳定音频流的接口
	 * @param array $args
	 */
	public function getUnableMu(){
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->getUnableMu();
		
	}
	
	/**
	 * 获取所有黑名单用户列表
	 */
	public function getAllBlackList($fromdb = false) {
		$objRadioBlack = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioBlack', 'service');
		return $objRadioBlack->getAllBlackList($fromdb);
	}
	
	/**
	 * 获取转码信息
	 * @array $args=array('rid'=>电台rid,'source'=>源流地址,'start_time'=>,'end_time'=>)
	 *$flag 为true 慎用 改版专用参数 清流后使用
	 */
	public function transcodeRadio2($args,$flag=FALSE) {
		$tmp['rid']=$args['rid'];
		$tmp['source']=$args['source'];
		//生产环境启用
//		//针对新增加电台时候的处理
		if(empty($args['start_time'])||empty($args['end_time'])||$flag){
			$tmp['start_time']=time()+100;
			$tmp['end_time']=strtotime(date("Y-m-d",strtotime("+1 day")))+10800;//第二天的三点结束
		}else{
			//日常脚本
			$tmp['start_time']=time()+200;
			$tmp['end_time']=$tmp['start_time']+86660;
		}
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->transcodeRadio2($tmp);
	}
	
	/**
	 * 强制转码某个电台 根据rid 和源流 新增电台时专用 可设定流开始结束时间
	 * @array $args=array('rid'=>电台rid,'source'=>源流地址,'start_time'=>,'end_time'=>)
	 *
	 */
	public function transcodeRadioForce($args) {
		$tmp['rid']=$args['rid'];
		$tmp['source']=$args['source'];
		//针对新增加电台时候的处理
		if(empty($args['start_time'])||empty($args['end_time'])){
			$tmp['start_time']=time()+100;
			$tmp['end_time']=strtotime(date("Y-m-d",strtotime("+1 day")))+10800;//第二天随机时间1点-2点之间
		}else{
			//日常脚本
			$tmp['start_time']=time()+200;
			$tmp['end_time']=$tmp['start_time']+800;
		}
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->transcodeRadio2($tmp);
	}
	
	/**
	 * 获得最近快要过期的流，并转码
	 */
	public function updateRecentCode(){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		return $objRadioInfo->updateRecentCode();
	}
	/**
	 * 给前台增加管理员
	 */
	public function addPower($data) {
		$objPower = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPower', 'service');
		return $objPower->addPower($data);
	}
	
	/**
	 * 获得管理员列表信息
	 * @param $args
	 * @return array
	 */
	public function getPower($args) {
		if(!is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		if(!empty($args['page']) && !empty($args['pagesize'])) {		// 支持分页查询
			$tmpArgs = $args;
			unset($tmpArgs['page']);
			unset($tmpArgs['pagesize']);
			$whereArgs = $tmpArgs;
			$postfixArgs = array(
				'field' => 'uptime',
				'order' => 'DESC',
				'page' => $args['page'],
				'pagesize' => $args['pagesize']
			);
		} else {		// 支持查询全部
			if (isset($args['uid']) && (int)$args['uid'] > 0) {
				$whereArgs = array('uid' => intval($args['uid']),
					'power' =>intval($args['power'])
				);
			}else{
				$whereArgs = array('power' =>intval($args['power']));
				$postfixArgs = array('field' => 'uptime','order' => 'DESC');
			}
		}
		$objPower = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPower', 'service');
		return $objPower->getPower($whereArgs, $postfixArgs);
	}

	/**
	 * 删除权限名单
	 * @param array $args uid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delPower($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_M_CK_00001');
		}
		$whereArgs = array('uid' => is_numeric($args['uid']) ? $args['uid'] : explode(',', $args['uid']),
			'power'=>$args['power']
		);
		$objPower = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPower', 'service');
		return $objPower->delPower($whereArgs);
	}
	
	/**
	 * 获取权限用户列表，供前台权限判断使用
	 */
	public function getAllPowerList() {
		$objPower = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPower', 'service');
		return $objPower->getAllPowerList();
	}
	/**
	 *按类型获取已推荐给无线的推荐电台图
	 */
	public function getRecommendFromDB($type){
		$objRadioRecommend = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		return $objRadioRecommend->getRecommendFromDB($type);
	}
	/**
	 *添加无线端电台推荐图
	 */
	public function addRadioRecommend($args){
		$objRadioRecommend = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		return $objRadioRecommend->addRadioRecommend($args);
	}
		/**
	 *清空无线电台推荐的相同类型的数据
	 */
	public function delRadioRecommend($type){
		$objRadioRecommend = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		return $objRadioRecommend->delRadioRecommend($type);
	}
		/**
	 *获取无线电台推荐的所有数据
	 */
	public function getAllRecommend($fromdb = false){
		$objRadioRecommend = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		return $objRadioRecommend->getAllRecommend($type);
	}
	 /**
	 * 获取电台分类列表
	 * @return array
	 */
	public function getClassificationList($fromdb = false){
		$dRadioClassification = clsFactory::create(CLASS_PATH.'data/radio','dRadioClassification','service');
		return $dRadioClassification->getClassificationList($fromdb);
	}
		 /**
	 * 添加电台分类
	 * @return array
	 */
	public function addClassification($data){
		$dRadioClassification = clsFactory::create(CLASS_PATH.'data/radio','dRadioClassification','service');
		return $dRadioClassification->addClassification($data);
	}
    /**
    *添加节目分类
    *
    */
		/**
	 * 设置分类信息
	 * @param array $args
	 * @return array
	 */
	public function insertRadioProgramType($program_type){
        
		$dRadioProgramType = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $dRadioProgramType->insertRadioProgramType($program_type);
	}

    	/**
	 * 设置节目分类信息
	 * @param array $args
	 * @return array
	 */
	public function updateRadioProgramType($set){
		$id = $set['id'];
        $type= $set['program_type'];
		unset($set['id']);
		$dRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $dRadioProgramType->updateRadioProgramType($id,$type);
	}

    /**
     *更改一个节目分类排序
     *@param int $program_type_id  节目分类id
     *@param int $sort 节目分类排序
     */
	public function updateRadioProgramTypeSort($program_type_id, $sort){
		$dRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $dRadioProgramType->updateRadioProgramTypeSort($program_type_id, $sort);
	}

	/**
	 * 根据classification_id删除分类
	 */
	public function delRadioClassification($cid){
		$dRadioClassification = clsFactory::create(CLASS_PATH.'data/radio','dRadioClassification','service');
		return $dRadioClassification->delRadioClassification($cid);
	}
    public function delRadioProgramType($program_type_id){
        $dRadioProgramType = clsFactory::create(CLASS_PATH.'data/radio','dRadioProgramType','service');
        return $dRadioProgramType->delRadioProgramType($program_type_id);
    }
	/**
	 * 获取首页新闻信息
	 */
	public function getNewsForIndex($fromdb = false){
		$objRadioNews = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->getNewsForIndex($fromdb);
	}
	/**
	 * 获取新闻列表页分页信息
	 */
	public function getNewsForPage($page,$fromdb = false){
		$objRadioNews = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->getNewsForPage($page,$fromdb = false);
	}
	/**
	 * 获取分页新闻信息
	 */
	public function getNewsNum($fromdb = false){
		$objRadioNews = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->getNewsNum($fromdb = false);
	}
	
	/**
	 *按类型获取新闻内容
	 */
	public function getNewsFromDB($whereArgs,$postfixArgs=array()){
		if(empty($whereArgs) || !is_array($whereArgs)){
			return false;
		}
		$objRadioNews = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->getNewsFromDB($whereArgs,$postfixArgs=array());
	}
	/**
	 *添加新闻内容
	 */
	public function addRadioNews($args){
		if(empty($args) || !is_array($args)){
			return false;
		}
		$objRadioNews = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->addRadioNews($args);
	}
	/**
	 *删除新闻内容
	 */
	public function delRadioNews($data){
		if(empty($data) || !is_array($data)){
			return false;
		}
		$objRadioNews= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioNews', 'service');
		return $objRadioNews->delRadioNews($data);
	}

	/**
	 * 生成特殊feed预览缩略图
	 * @param array $radioinfo
	 */
	public function generateFeedThumbnail($radioinfo = array()){
		$img_obj = clsFactory::create(CLASS_PATH."model/radio", "mRadioFeedPic", "service");
		try{
			$url = $img_obj->generateThumbnail($radioinfo);
		}catch (Exception $e){
			$url = '';
		}
		return $url;
	}
	/**
	 * 生成特殊feed展开后的电台logo图片缩略图
	 * @param array $radioinfo
	 */
	public function generateFeedRadioThumbnail($radiopic){
		$img_obj = clsFactory::create(CLASS_PATH."model/radio", "mRadioFeedPic", "service");
		try{
			$url = $img_obj->generateRadioThumbnail($radiopic);
		}catch (Exception $e){
			$url = '';
		}
		return $url;
	}
	
	/**
	 * 生成特殊feed预览缩略图
	 * @param array $radioinfo
	 */
	public function generateFeedThumbnailPre($radioinfo = array()){
		$img_obj = clsFactory::create(CLASS_PATH."model/radio", "mRadioFeedPic", "service");
		try{
			$url = $img_obj->generateThumbnailPre($radioinfo);
		}catch (Exception $e){
			$url = '';
		}
		return $url;
	}

	/**
	 * 根据电台的节目id获取节目分类
	 * @param int $program_id		电台节目id
	 */
	public function getRadioProgramType($program_id,$flag=false){
		$objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $objRadioProgramType->getRadioProgramType($program_id,$flag);
	}

		/**
	 * 根据电台的节目id获取节目分类
	 * @param array $arr		电台节目数组 批量获取
	 */
	public function getRadioProgramType2($arr,$flag=false){
		$objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $objRadioProgramType->getRadioProgramType2($arr,$flag);
	}

	/**
	 * 查询传入时间是否有其他节目
     * return 没冲突返回false, 有冲突返回冲突的节目信息
	 */
    public function isRadioProgramTimeConflict($rid, $day, $begintime, $endtime){
		$objRadioProgramV2= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		return $objRadioProgramV2->isRadioProgramTimeConflict($rid, $day, $begintime, $endtime);
    }


	/**
	 * 获取所有节目分类
	 */
	public function  getRadioProgramTypeList(){
		$objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		return $objRadioProgramType->getRadioProgramTypeList();
	}

    public function insertRadioProgram(Array $program, Array $types){
		$objRadioProgramV2= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
        $ret = $objRadioProgramV2->insertRadioProgram($program, $types);
        return $ret;
    }

    public function updateRadioProgram(Array $program, $types){
		$objRadioProgramV2= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
        $ret = $objRadioProgramV2->updateRadioProgram($program, $types);
        return $ret;
    }


    /**
     *复制某天节目 到其他天
     *@param int $rid
     *@param int $from_day
     *@param str $to_day
     */
    public function copyProgram($rid, $from_day, $to_day){
		$objRadioProgramV2= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
        $ret = $objRadioProgramV2->copyProgram($rid, $from_day, $to_day);
        return $ret;
    }

	
	/**
     *换新流时将旧的流存入radio_seek表
     *@param array $arr start_time,rid,end_time,epgid
     *
     */
    public function addSeek($arr){
		$dRadioSeek= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioSeek', 'service');
		$tmp=array();
		foreach ($arr as $k=>$v){
			$tmp[$k]['start_time']=$v['start_time'];
			$tmp[$k]['rid']=$v['rid'];
			$tmp[$k]['end_time']=$v['end_time'];
			$tmp[$k]['epgid']=$v['epgid'];
			//unset($arr[$k]);
		}
        $res = $dRadioSeek->addSeek($tmp);
        return $res;
    }

	/**
     *seek7天内流方法
     *@param array $arr start_time,rid,end_time,epgid
     *
     */
    public function getSeek($arr,$fromdb=false){
        $dRadioSeek= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioSeek', 'service');
        $res = $dRadioSeek->getSeek($arr,$fromdb);
        return $res;
    }
	
	/**
     *处理每个页面顶端的数据,之后分配给scope
     *@param $cur_rid 电台id 当前电台id
     *
     */
    public function formatScope($cur_rid=0){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
        $res = $dRadio->formatScope($cur_rid);
        return $res;
    }

	/**
     *获取热播节目
     *@param $rid 电台id
     *
     */
    public function getHotProgram($begintime,$endtime,$type,$pagesize,$page=1){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
		$res = $dRadio->getHotProgram($begintime,$endtime,$type,$pagesize,$page);
        return $res;
    }

	/**
     *根据电台名称搜索电台
     *@param $rid 电台id
     *
     */
    public function searchRadioInfoByRadioName($radioName,$page=1,$pagesize=10){
		if(empty($radioName)||empty($page)){
			return false;
		}
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		$res = $dRadio->searchRadioInfoByRadioName($radioName,$page,$pagesize);
        return $res;
    }

	/**
     *根据节目名称搜索节目
     *@param $rid 电台id
     *
     */
    public function searchRadioInfoByProgramName($programName,$page=1,$pagesize=10){
		if(empty($programName)||empty($page)){
			return false;
		}
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		$res = $dRadio->searchRadioInfoByProgramName($programName,$page,$pagesize);
        return $res;
    }

	/**
     *根据dj名称搜索电台信息
     *@param $rid 电台id
     *
     */
    public function searchRadioInfoByDjName($djName,$page=1,$pagesize=10){
		if(empty($djName)||empty($page)){
			return false;
		}
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioDjInfo', 'service');
		$res = $dRadio->searchRadioInfoByDjName($djName,$page,$pagesize);
        return $res;
    }

	/**
     *根据dj名称搜索节目信息
     *@param $rid 电台id
     *
     */
    public function searchProgramInfoByDjName($djName,$page=1,$pagesize=10){
		if(empty($djName)||empty($page)){
			return false;
		}
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		$res = $dRadio->searchProgramInfoByDjName($djName,$page,$pagesize);
        return $res;
    }

	/**
     *查看转码完后的流是否正常
     *@param $online 上线的电台
     *
     */
    public function checkAllStream($online=1){
		$dRadioCheckStream= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioCheckStream', 'service');
		$res = $dRadioCheckStream->checkAllStream($online);
        return $res;
    }

	/**
	 * 更新计算每个省份的节目数量 
	 */
	public function updateAllProgramNumber(){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		$res = $dRadio->updateAllProgramNumber();
        return $res;
    }

	/**
	 * 更新计算每个省份的节目数量 
	 */
	public function getProgramNumberByProvince($pid){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramV2', 'service');
		$res = $dRadio->getProgramNumberByProvince($pid);
        return $res;
    }

	/**
	 * 更新计算每个省份的节目数量 
	 */
	public function getStaticData(){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		$res = $dRadio->getStaticData();
        return $res;
    }
	
	//获取无线推荐图
	public function getRecommendPic($type,$fromdb=false){
		if(!in_array($type, array(1,2))){
			return false;
		}
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		$res = $dRadio->getRecommendPic($type,$fromdb);
        return $res;
	}

	//获取所有的无线推荐图
	public function getAllRecommendPic2($fromdb=false){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		$res = $dRadio->getAllRecommendPic2($fromdb);
        return $res;
	}

	//获取当前时段的无线推荐图
	public function getRecommendPicNow($fromdb=false){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioRecommend', 'service');
		$res = $dRadio->getRecommendPicNow($fromdb);
        return $res;
	}

	//添加电台页面上某区域信息 目前仅有首页
	public function addRadioPage($arr){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->addRadioPage($arr);
        return $res;
	}

	//删除电台页面上某区域信息
	public function delRadioPage($arr){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->delRadioPage($arr);
        return $res;
	}

	//添加电台页面上某区域信息(更新)
	public function updateRadioPage($arr){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->updateRadioPage($arr);
        return $res;
	}

	//获取电台页面上某区域信息
	public function getRadioPageInfoByBlockName($block_name,$type=1,$visable=0,$fromdb = false){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->getRadioPageInfoByBlockName($type,$block_name,$visable,$fromdb = false);
        return $res;
	}
	//后台使用 带分页
	//获取内部链接
//	public function getRadioPageInfoByBlockName2($block_name,$type=1,$page=1,$fromdb = false){
//		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
//		$res = $dRadio->getRadioPageInfoByBlockName2($type,$block_name,$page,$fromdb = false);
//        return $res;
//	}
	//获取内部链接
//	public function getRadioPageInfoByBlockName2($block_name,$type=1,$page=1,$fromdb = false){
//		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
//		$res = $dRadio->getRadioPageInfoByBlockName2($type,$block_name,$page,$fromdb = false);
//        return $res;
//	}

	public function getRadioPageInfoByBlockName2($block_name,$type=1,$fromdb = false){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->getRadioPageInfoByBlockName2($type,$block_name,$fromdb = false);
        return $res;
	}

	public function getRadioPageById($id){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->getRadioPageById($id);
        return $res;
	}

	public function getRadioPage($type,$visable=0,$fromdb = false){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPage', 'service');
		$res = $dRadio->getRadioPage($type,$visable,$fromdb = false);
        return $res;
	}

	public function setCacheData($key, $value, $expire = 0){
		$dRadio= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadio', 'service');
		$res = $dRadio->setCacheData($key, $value, $expire);
        return $res;
	}

	public function getWeiBoCardByUid($uid,$fromdb = false){
		$dRadioInfo= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
		$res = $dRadioInfo->getWeiBoCardByUid($uid,$fromdb);
        return $res;
	}

	//@test
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
}
?>
