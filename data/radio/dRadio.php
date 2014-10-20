<?php
/**
 *
 * 电台的data层
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
include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "config/apiconf.php";
include_once SERVER_ROOT . "config/config.php";
//include_once SERVER_ROOT . "config/area.php";
include_once(PATH_ROOT.'tools/store/tStore.php');
require_once(SERVER_ROOT.'dagger/libs/extern.php');
class dRadio extends data{
	public static $RADIO_DB = 'srv.sapps.city';  //电台数据库资源
	public static $RADIO_MC = 'srv.run.main';  //电台缓存资源

	public $_radioInfo = 'radio_info';		// 电台信息表
	public $_radioCollection = 'radio_collection';	//收藏电台信息表
	public $_radioProgram = 'radio_program';	//电台节目单信息表
	public $_radioPicInfo = 'radio_pic_info';	// 轮播大图信息表
	public $_radioHotInfo = 'radio_hot_info';	// 热门推荐电台信息表
	public $_radioProvinceInfo = 'radio_province_info';	// 推荐地区电台信息
	public $_radioNotice = 'radio_notice_info';	// 电台公告信息
    private static $db;
    private static $db_master;
    private static $mc;
    private $transaction_id;
	/**
	 * 连接数据库
	 * @return mysql_connection
	 */
	public function _connectDb($master=0){
        if(empty($master)){//主从需要分开
            if(!empty(self::$db)){
                return self::$db;
            }
            self::$db = $this->connectDb(self::$RADIO_DB, $master);
            if(self::$db == false) {
                $this->writeRadioErrLog(array('连接DB失败', '参数：this->connectDb(' . self::$RADIO_DB . ', ' . $master . ')'), 'RADIO_ERR');
                return false;
            }
            return self::$db;
        }else{
            if(!empty(self::$db_master)){
                return self::$db_master;
            }
            self::$db_master = $this->connectDb(self::$RADIO_DB, $master);
            if(self::$db_master == false) {
                $this->writeRadioErrLog(array('连接DB失败', '参数：this->connectDb(' . self::$RADIO_DB . ', ' . $master . ')'), 'RADIO_ERR');
                return false;
            }
            return self::$db_master;
        }
	}

    public function beginTransaction(){
        return;
        ++$this->transaction_id;
        if($this->transaction_id >= 2) return;
        return self::$db_master->beginTransaction();
    }
    public function rollBack(){
        return;
       return self::$db_master->rollBack();
    }
    public function commit(){
        return;
        if(1 == $this->transaction_id--)
            return self::$db_master->commit();
    }

	/**
	 * 返回MC实例对象
	 * @return mc实例
	 */
	public function _connectMc(){
        if(!empty(self::$mc)){
            return self::$mc;
        }
        self::$mc = $this->connectMc(self::$RADIO_MC);
		if(self::$mc == false) {
			$this->writeRadioErrLog(array('连接MC失败', '参数：this->connectMc(' . self::$RADIO_MC . ')'), 'RADIO_ERR');
		}
		return self::$mc;
	}
	//【基础功能方法】
	/**
	 * 查询数据，主要操作辅库
	 * @param	string	$_sql 查询sql语句
	 * @param	array	$_param = array("SQL字段1" => "变量值1","SQL字段2" => "变量值2")
	 * @return	正确 查询结果,
	 * 错误	false；
	 */
	public function queryData($_sql, $_param = array()){
		$linkobj = $this->_connectDb();
		if ($linkobj == false) {
			return false;
		}
		try{
			$stam = $linkobj->prepare($_sql);
		}catch (PDOException $e){
			$this->writeRadioErrLog(array('PDO预处理失败', "{$e->getMessage()}"),"RADIO_ERR");
			return false;
		}			
		$re = $stam->execute($_param);
		if($re){
			$retData = $stam->fetchAll(PDO::FETCH_ASSOC);
			$stam = null;
			$linkobj = null;
			return $retData;
		}else{
			$stam = null;
			$linkobj = null;
			$this->writeRadioErrLog(array('SQL语句执行失败', "参数:{$_sql}, {$_param}" ), "RADIO_ERR");
			return false;
		}
	}
	/**
	 * 操作数据，主要操作主库
	 * @param	string	$_sql 操作sql语句
	 * @param	array	$_param = array("SQL字段1" => "变量值1","SQL字段2" => "变量值2")
	 * @return	正确	 true,
	 * 错误	false
	 */
	public function operateData($_sql, $_param = array()){
		$linkobj = $this->_connectDb(1);
		if($linkobj == false){
			return false;
		}
		try{
			$stam = $linkobj->prepare($_sql);
		}catch ( PDOException $e){
			$this->writeRadioErrLog(array('PDO预处理失败', "返回值:{$e->getMessage()}", "参数:{$_sql}" ), "RADIO_ERR");
			return false;
		}
		$re = $stam->execute($_param);
		if($re){
			$retData = $linkobj->lastInsertId();
			$stam = null;
			$linkobj = null;
			return $retData;
		}else{
			$stam = null;
			$linkobj = null;
			$this->writeRadioErrLog(array ('SQL执行失败', "参数sql:{$_sql},参数param:".print_r($_param, true) ), "RADIO_ERR");
			return false;
		}
	}

	/**
	 * 通过缓存key获取数据
	 * @param $key 缓存key
	 */
	public function getCacheData($key) {
        if(request::get('_flush_cache', 'STR') == 1){
            return false;
        }
		$mc = $this->_connectMc();
		if($mc == false) {
			return false;
		}
		$data = $mc->get($key);
		return $data;
	}

	/**
	 * 通过缓存key获取数据
	 * @param $array 缓存key数组
	 */
	public function getMultiCacheData($array) {
        if(request::get('_flush_cache', 'STR') == 1){
            return false;
        }
		$mc = $this->_connectMc();
		if($mc == false) {
			return false;
		}
		$data = $mc->getMulti($array, null, Memcached::GET_PRESERVE_ORDER);
		return $data;
	}

	/**
	 * 通过key设置缓存数据
	 * @param $key 缓存key
	 */
	public function setCacheData($key, $value, $expire = 0) {
		$mc = $this->_connectMc();
		if($mc == false) {
			return false;
		}
		$status = $mc->set($key, $value, $expire);
		if($status == false) {
			$this->writeRadioErrLog(array('设置MC失败', 'KEY:' . $key, 'VALUE:' . serialize($value), 'EXPIRE:' . $expire), 'RADIO_ERR');
			return false;
		}
		return true;
	}

	/**
	 * 通过关联数组设置缓存数据 存储多个元素
	 * @param $array array('键' => '值' )
	 */
	public function setMultiCacheData($array, $expire = 0) {
		$mc = $this->_connectMc();
		if($mc == false) {
			return false;
		}
		$status = $mc->setMulti($array, $expire);
		if($status == false) {
			$this->writeRadioErrLog(array('设置MC失败', 'VALUE:' . serialize($array), 'EXPIRE:' . $expire), 'RADIO_ERR');
			return false;
		}
		return true;
	}

	/**
	 * 通过缓存key删除缓存数据
	 * @param $key 缓存key
	 */
	public function delCacheData($key) {
		$mc = $this->_connectMc();
		if($mc == false) {
			return false;
		}
		$status = $mc->delete($key);
		if($status == false) {
			$this->writeRadioErrLog(array('删除MC失败', 'KEY:' . $key), 'RADIO_ERR');
		}
		return $status;
	}

	/**
	 *
	 * 更新落地缓存表
	 * @param unknown_type $mc_key
	 * @param unknown_type $mc_value
	 */
	public function delKeyValue($mc_keys, $expireTime=0){
		foreach ($mc_keys as $val){
			$mc_value[$val] = array();
		}
		tStore::setApp(RADIO_APP_SOURCE);
        tStore::setAlias(self::$RADIO_MC, self::$RADIO_DB);
        $result = tStore::mSet($mc_value, $expireTime);
        return $result;
	}

	/**
	 *
	 * 更新落地缓存表
	 * @param unknown_type $mc_key
	 * @param unknown_type $mc_value
	 */
	public function updateKeyValue($mc_value, $expireTime=0){
		tStore::setApp(RADIO_APP_SOURCE);
        tStore::setAlias(self::$RADIO_MC, self::$RADIO_DB);
        foreach($mc_value as $k => $v){
            $result = tStore::mSet(array($k=>$v), $expireTime);
        }
        return $result;
	}

	/**
	 *
	 * 获取缓存落地信息表的值
	 * @param unknown_type $key
	 */
	public function getValueByKey($aKey){
		tStore::setApp(RADIO_APP_SOURCE);
        tStore::setAlias(self::$RADIO_MC, self::$RADIO_DB);
        $result = tStore::mGet($aKey);
		return $result;
	}

	/**
	 * 格式化返回结果
	 * @param $errno
	 * @param $result
	 * @return unknown_type
	 */
	public function returnFormat($errorno, $result = array()){
		$res = array(
			'errorno' => $errorno,
			'result'  => $result
		);
		return $res;
	}

	/**
	 *
	 * 写错误日志
	 * @param unknown_type $errorMes
	 * @param unknown_type $filename
	 */
	public function writeRadioErrLog($errorMes, $filename = "") {
		if(empty($errorMes)) {
			return false;
		}
		$objLog = clsFactory::create ( 'framework/tools/log/', 'ftLogs', 'service' );
		$objLog->switchs ( 1 ); //1 开    0 关闭
		$objLog->write ( 'radio', $errorMes, 'data_' . $filename );
	}

	/*
	 * 组装通用数据库插入方法
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param string $tableName 表名
	 * @param array $args 参数数组
	 * @return array
	 */
	protected function _makeInsert($tableName, $args) {
		$keys = array_keys($args);
		$vals = array_values($args);
		$paras = array_fill(0, count($keys), '?');
		$sql = "INSERT INTO {$tableName} (`" . join("`,`", $keys) . "`) VALUES(" . join(",", $paras) . ")";
		return array('sql' => $sql, 'data' => $vals);
	}
	/*
	 * 组装通用数据库查询方法
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param string $tableName 表名
	 * @param str $queryString 查询信息
	 * @param array $whereArgs where条件
	 * @param array $postfixArgs 分页条件以及排序条件
	 * @return array
	 */
	protected function _makeSelect($tableName, $queryString = '', $whereArgs = array(), $postfixArgs = array()) {
		$queryString = empty($queryString) ? '*' : $queryString;
		$vals = array();
		$keys = array();
		$order = '';
		$limit = '';
		if($whereArgs) {
			$count = 0;
			foreach($whereArgs as $k => $v) {	
				if(is_array($v)) {
					$keys[] = $k . ' IN (' . mysql_escape_string(implode(',', $v)) . ')';
				} else if(!is_null($v)) {
					if(is_array($postfixArgs['search_type'])){
						foreach ($postfixArgs['search_type'] as $key => $val){
							if($count == $key){
								if($val == 'like'){
									$keys[$key] = $k .' like ?';
									$vals[$key] = $v;
								}
								if($val == '='){
									$keys[$key] = $k .' = ?';
									$vals[$key] = $v;
								}
							}
						}
					}
					else if(strtolower($postfixArgs['search_type']) == 'like'){
						$keys[] = $k . ' like ?';
						$vals[] = $v;
					}
					else {
						$keys[] = $k . '=?';
						$vals[] = $v;
					}
				}
				$count++;
			}
		}
		if(!empty($postfixArgs)) {
			if(!empty($postfixArgs['field']) && !empty($postfixArgs['order'])) {		// ORDER排序
				$order = ' ORDER BY ' . $postfixArgs['field'] . ($postfixArgs['order'] ? ' ' . $postfixArgs['order'] : '');
			}
			if(empty($postfixArgs['page']) && !empty($postfixArgs['pagesize'])) {		// LIMIT分页
				$limit = ' LIMIT ' . $postfixArgs['pagesize'];
			} else if(!empty($postfixArgs['page']) && $postfixArgs['page'] == 1 && !empty($postfixArgs['pagesize'])) {
				$limit = ' LIMIT ' . $postfixArgs['pagesize'];
			} else if(!empty($postfixArgs['pagesize'])) {
				$limit = ' LIMIT ' . ($postfixArgs['page'] - 1) * $postfixArgs['pagesize'] . ',' . $postfixArgs['pagesize'];
			}
		}
		$sql = "SELECT {$queryString} FROM {$tableName}" . (!empty($keys) ? (' WHERE ' . implode(' AND ', $keys)) : '') . ($order ? ' ' . $order : '') . ($limit ? ' ' . $limit : '');
		return array('sql' => $sql, 'data' => $vals);
	}
	/**
	 * 生成 update table 的db 语句
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param $tableName
	 * @param $args
	 * @param $whereArgs
	 * @return array
	 */
	protected function _makeUpdate($tableName, $args, $whereArgs) {
		$vals = array();
		foreach($args as $k => $v) {
			$keys[] = '`' . $k . '`' . '=?';
			$vals[] = $v;
		}
		foreach($whereArgs as $k => $v) {
			if(is_array($v)) {
				$wKeys[] = $k . ' IN (' . mysql_escape_string(implode(',', $v)) . ')';
			} else {
				$wKeys[] = '`' . $k . '`=?';
				$vals[] = $v;
			}
		}
		$keys = join(',', $keys);
		$wKeys = join(' AND ', $wKeys);
		$sql = "UPDATE {$tableName} SET {$keys} WHERE {$wKeys}";
		return array('sql' => $sql, 'data' => $vals);
	}
	/**
	 * 生成delete table 的db 语句
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param $whereArgs
	 * @return array
	 */
	protected function _makeDelete($tableName, $whereArgs) {
		$keys = array();
		$vals = array();
		foreach($whereArgs as $k => $v) {
			if(is_array($v)) {
				$keys[] = $k . ' IN (' . mysql_escape_string(implode(',', $v)) . ')';
			} else {
				$keys[] = '`' . $k . '`=?';
				$vals[] = $v;
			}
		}
		$keys = join(' AND ', $keys);
		$sql = "DELETE FROM {$tableName} WHERE {$keys}";
		return array('sql' => $sql, 'data' => $vals);
	}

	/**
	 *
	 * Curl方法调用接口--get
	 * @param unknown_type $url
	 * @param unknown_type $num
	 */
	public function curlGetData($url,$num){
		return $this->requestGet($url,$num);
	}

	/**
	* 通过带cookie get方式访问url
	*
	* @param string $url
	* @param int $timeout
	* @return FALSE| array
	*/
	public function GetWithCookie($url,$num){
		return $this->requestGetWithCookie($url, $timeout=3);
	}

	/**
	 *
	 * Curl方法调用接口--post
	 * @param string $url
	 * @param array $data
	 * @param int $timeout
	 */
	public function curlPostData($url,$data,$timeout = 3){
		return $this->requestPost(0,$url,$data,'',$timeout);
	}

	/**
	 * 批量获取微博转发评论数
	 * @param $args array(uids=>uid数组,size=>头像大小)
	 * @return array
	 */
	public function getRtAndCmtNum($mids){
		if(empty($mids) || !is_array($mids)){
			return false;
		}

		$objBasic = clsFactory::create(CLASS_PATH . "data/radio", "dRadioBasic", "service" );
		$args['cuid'] = ADMIN_UID;
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$args['cip'] = tCheck::getIp();
		$args['appkey'] = RADIO_SOURCE_APP_KEY;
		$result = $objBasic->getRtAndCmtNum($args,$mids);

		return $result;
	}

	/**
	 * 判断字符串是否含有非法字符
	 * @param string $str
	 */
	public function checkKeyWord($str){
		$url = RADIO_CHECK_KEYWORD;
		$data['appkey'] = RADIO_SOURCE_APP_KEY;
		$data['content'] = $str;
		$json_result = $this->curlPostData($url, $data);
		$aResult = json_decode($json_result,true);
		if($aResult['result'] == -1 || $aResult['result'] == 4){
			return false;
		}
		return true;
	}

	/**
	 * 格式化微博数据
	 * @param array $search_rs
	 * @param array $mblog_rs
	 * @param array $user_rs
	 */
	public function formatMblog($search_rs,$mblog_rs,$user_rs,$mblog_num){
		$content = array();
		foreach($search_rs as $key => $value){
			if(empty($mblog_rs[$value['mid']]) || empty($user_rs[$value['uid']]) ){
				continue;
			}
			$content[$key]['mblogid'] = $value['mid62'];
			$content[$key]['uid'] = $value['uid'];
			$content[$key]['time'] = $mblog_rs[$value['mid']]['ctime'];
			$content[$key]['rtnum'] = !empty($mblog_num[$value['mid']]['rtnum']) ? $mblog_num[$value['mid']]['rtnum'] : 0;
			$content[$key]['cmtnum'] = !empty($mblog_num[$value['mid']]['cmtnum']) ? $mblog_num[$value['mid']]['cmtnum'] : 0;
			$content[$key]['content'] = array('text' => $mblog_rs[$value['mid']]['text']
											,'old_text' => $mblog_rs[$value['mid']]['content']
											,'encode_text' => rawurlencode ( $mblog_rs[$value['mid']]['text'] ));
			if(!empty($mblog_rs[$value['mid']]['pic']) && count($mblog_rs[$value['mid']]['pic']) > 0){
				foreach($mblog_rs[$value['mid']]['pic'] as $v){
					$content[$key]['content']['pic'][$v] = "http://ww2.sinaimg.cn/thumbnail/".$v.".jpg";
				}
			}
			else{
				$content[$key]['content']['pic'] = array();
			}

			$picArr = $content[$key]['content']['pic'];
			if ($picArr) {
				$content[$key] ['content'] ['pid'] = array_slice ( array_keys ( $picArr ), 0, 1 );
				$content[$key] ['content'] ['pid'] = $content[$key] ['content'] ['pid'] [0];
				$content[$key] ['content'] ['pic'] = $picArr [$content[$key] ['content'] ['pid']];
			}

			if($value['rootuid'] > 0){
				$content[$key]['rt'] = array('rootuid' => $value['rootuid']
										,'rootid' => $value['rootmid62']
										,'rootmid' => $value['rootmid']
										,'rootrtnum' => $mblog_num[$value['rootmid']]['rtnum']
										,'rootcmtnum' => $mblog_num[$value['rootmid']]['cmtnum']
										,'text' => $mblog_rs[$value['rootmid']]['text']
										,'rootuser' => $user_rs[$value['rootuid']]);
			}
			$content[$key]['source_link'] = $mblog_rs[$value['mid']]['source'];

			// 2010-10-10 10:12:25 样式
			$content[$key]['date'] = date("Y-m-d H:i:s");
			//转换获得十进制 mid
			$content[$key]['mid'] = $value['mid'];
			$content[$key]['icon'] = $user_rs[$value['uid']]['profile_image_url'];
			$content[$key]['nick'] = $user_rs[$value['uid']]['name'];
			$content[$key]['user_type'] = $user_rs[$value['uid']]['user_type'];
			if(!empty($content[$key]['content']['pic']) && count($content[$key]['content']['pic']) > 0){
				foreach($content[$key]['content']['pic'] as $pid => $src){
						$content[$key]['thumb_pic'] = $src;
						$content[$key]['thumb_pic_id']=  $pid;
						break;
				}
			}
			$content[$key]['user'] = $user_rs[$value['uid']];
		}
		return $content;
	}

	/**
	 *
     * 获取关注关系
     */
	public function checkattrelation($uid,$fuid){
		$dPerson = clsFactory::create(CLASS_PATH.'data','dPerson','service');
		if(is_string($fuid)){
			$fuids = $fuid;
		}
		else if (is_array($fuid)){
			$fuids = implode(',',$fuid);
		}
		//调用接口获取
		$args = array(
					'uid'  => $uid,
                    'fuids' => $fuids
                	);
		//接口安全调用参数
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$aRelation = $dPerson->newGetUserRelation($args);
		if($aRelation !== false){
			return $aRelation;
		}else{
			$aError = array(
						'errmsg' => 'get Listeners failed, get data from newGetUserRelation interface failed',
                        'param'  => implode('|', $args)
						);
			$this->writeRadioErrLog($aError, 'RADIO_ERR');
			}
	}

        /*
        * 新版发私信功能
        * by zihao1
        *
        *
        */
        public function sendMessageMulti($uid, $text, $id){
            $weiboClient = new BaseModelWeiboClient(RADIO_SOURCE_APP_KEY); 
            BaseModelCommon::debug($uid,'uid');
            BaseModelCommon::debug($text,'text');
            BaseModelCommon::debug($id,'id');
        
            $result = $weiboClient -> send_dm_by_id($uid, $text, $id);
            BaseModelCommon::debug($result);
            
            if(isset($result['id']))
                return true;
            else
                return false;
        }
        

	/*
	 * 获取微电台官方微博信息
	 * @param int $uid
	 * @return array
	 */
	public function getOfficialMinfo($uid){
		$Official_uid = RADIO_OFFICIAL_UID;
		$OfficialMinfo = $this->getUserInfoByUid(array($Official_uid));
		$OfficialMinfo = $OfficialMinfo[$Official_uid];
		if(!empty($OfficialMinfo)){
			$aRelation = $this->checkattrelation($uid,$Official_uid);
			//获取当前用户与电台官方微博的关注关系
			$attention = false;
			if($aRelation['one2many'][$Official_uid]){
				$attention = true;
			}
			$OfficialMinfo['attention'] = $attention;
			return $OfficialMinfo;
		}
		return false;
	}

	/*
	 * 调取热门节目接口
	 *
	 */
	public function getHotProgram($begintime,$endtime,$type,$pagesize,$page=1){
		$url = sprintf(RADIO_RADIO_HOT_PROGRAM,$begintime,$endtime,$type,$pagesize,$page);
		$json_result = $this->curlGetData($url, 3);
		$aResult = json_decode($json_result,true);
		return $aResult;
	}
	
	/*
	 * 调取更新短链接口
	 *
	 */
	public function updateShortUrl($radiourl){
		$url = sprintf(RADIO_SHORT_URL_UPDATE,$radiourl);
		$json_result = $this->curlGetData($url, 3);
		$aResult = json_decode($json_result,true);
		return $aResult;
	}
	
	/**
	 * 接口Get调用
	 * @param	string	$url		URL地址
	 * @param	integer	$timeout	超时时间
	 * @return	all
	 */
	public function curlGetDataUserPwd($url, $timeout=1) {
		$ch = curl_init();
		$userinfo = RADIO_APP_USER.":".RADIO_APP_PASS;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERPWD, $userinfo);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

	/**
	 * 接口POST调用
	 * @param	string	$url		URL地址
	 * @param	array $data			post参数数据
	 * @param	integer	$timeout	超时时间
	 * @return	all
	 */
	public function curlPostDataUserPwd($url,$data,$timeout){
		if(!is_array($data) or empty($data)) return false;
		foreach($data as $k => &$v) {
			$v = $k.'='.rawurlencode($v);
		}
		unset($v);

		$data = implode('&', $data);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_COOKIE, $_SERVER['HTTP_COOKIE']);

		$result = curl_exec($ch);
		curl_close($ch);

		return $result;
	}

	/**
	 * 根据个性域名获取用户信息
	 */
	public function getUserInfoByDomain($domain){
		$url = sprintf(API_DOMAIN_SHOW,$domain);
		$result = json_decode($this->curlGetDataUserPwd($url),true);
		return $result;
	}

	/**
	 * 根据个性域名获取用户信息
	 */
	public function getUserInfoByUid($uids,$fromdb = false){
		if(empty($uids) || !is_array($uids)){
			return false;
		}
		$result = array();
		if($fromdb == false){
			foreach($uids as $k => $v){
				$mcKey = sprintf(MC_KEY_RADIO_USERINFO,$v);
				$cacheData = $this->getCacheData($mcKey);
				if($cacheData !== false){
					$result[$v] = $cacheData;
					unset($uids[$k]);
				}
			}
		}
		if(!empty($uids)){
			$uid = implode(',',$uids);
			$url = sprintf(API_SHOW_BATCH_BY_UID,$uid);
			$tmp = json_decode($this->curlGetDataUserPwd($url),true);
			$tmp = $tmp['users'];
			if(is_array($tmp)&&!empty($tmp)){
				foreach($tmp as $val){
					$val['uid'] = $val['id'];
					$val['link_url'] = T_URL.'/'.$val['profile_url'];
					$val['user_type'] = 'user';
					if($val['level'] == 2){
						$val['user_type'] = $val['verified_type'] == 0 ? 'vip' : 'vip_blue';
					}
					if($val['level'] == 7){
						$val['user_type'] = 'club_talent';
					}
					$mcKey = sprintf(MC_KEY_RADIO_USERINFO,$val['id']);
					$this->setCacheData($mcKey,$val,MC_TIME_RADIO_USERINFO);
					$result[$val['id']] = $val;
				}
			}
		}
//		print_r($result);
//		exit;
		return $result;
	}

	/**
	 * 根据个性域名获取用户信息
	 */
	public function getUserInfoByName($names,$fromdb = false){
		if(empty($names) || !is_array($names)){
			return false;
		}

		$result = array();
		if($fromdb == false){
			foreach($names as $k => $v){
				$v = trim($v);
				$mcKey = sprintf(MC_KEY_RADIO_USERINFO_NAME,$v);
				$cacheData = $this->getCacheData($mcKey);
				if($cacheData !== false){
					$result[$v] = $cacheData;
					unset($names[$k]);
				}
			}
		}
		if(!empty($names)){
			$name = implode(',',$names);
			$url = sprintf(API_SHOW_BATCH_BY_NAME,$name);
			$result = json_decode($this->curlGetDataUserPwd($url),true);
			$tmp = $result['users'];
			unset($result);
			foreach($tmp as $val){
				$val['uid'] = $val['id'];
				$val['link_url'] = T_URL.'/'.$val['profile_url'];
				$val['user_type'] = 'user';
				if($val['level'] == 2){
					$val['user_type'] = $val['verified_type'] == 0 ? 'vip' : 'vip_blue';
				}
				if($val['level'] == 7){
					$val['user_type'] = 'club_talent';
				}
				$mcKey = sprintf(MC_KEY_RADIO_USERINFO_NAME,$val['name']);
				$this->setCacheData($mcKey,$val,MC_TIME_RADIO_USERINFO_NAME);
				$result[$val['name']] = $val;
			}
		}

		return $result;
	}

	/**
	 * 获取用户是否绑定手机
	 * @param array $uids
	 */
	public function isBindingMobileMulti($uids){
		$url = sprintf(API_MOBILE_BATCH,$uids);
		$result = json_decode($this->curlGetDataUserPwd($url),true);
		if(!empty($result)){
			foreach ($result as $val){
				$tmp[$val['id']] = $val;
			}
			$result = $tmp;
		}
		return $result;
	}

	/**
	 * 获取用户水印信息
	 * @param int $cuid
	 */
	public function getWaterMark($cuid){
		if(empty($cuid)) return false;
		$mcKey = sprintf(MC_PUBLIC_USER_MARK_INFO,$cuid);
		$markInfo = $this->getCacheData($mcKey);;
		if($markInfo == false){
			$url = sprintf(API_WATERMARK);
			$result = json_decode($this->curlGetDataUserPwd($url),true);
			$userinfo = $this->getUserInfoByUid(array($cuid));
			$userinfo = $userinfo[$cuid];
			$markInfo = array();
			if(empty($result)){
				return false;
			}

			if($result['nickname'] == 1){
				$markInfo['pic_nick'] = $userinfo['name'];
			}else{
				$markInfo['pic_nick'] = '';
			}
			if($result['logo'] == 1){
				$markInfo['pic_logo'] = 1;
			}else{
				$markInfo['pic_logo'] = '';
			}
			if($result['domain'] == 1){
				$markInfo['pic_url'] = T_DOMAIN.'/'.$userinfo['profile_url'];
			}else{
				$markInfo['pic_url'] = '';
			}
			$markInfo['pic_markpos'] = empty($result['position']) ? 3:$result['position'];

			$this->setCacheData($mcKey,$markInfo,MC_PUBLIC_USER_MARK_INFO_TIME);
		}
		return $markInfo;
	}

	/**
	 * 新搜索接口
	 * @param array $args
	 */
	public function searchMblogByrpc($args){
		if(is_array($args) && !empty($args)){
			//接口安全调用参数
			$args['source'] = RADIO_SOURCE_APP_KEY;
			$args['count'] = 50;
			if(!isset($args['sid'])){
				$args['sid'] = 't-radio';	
			}
			$args['dup'] = 0;
			$args['antispam'] = 0;
			
			//屏蔽搜索接口中的某个来源
			$args['appid'] = -424578;
			$param = "";
			foreach ($args as $key => $val){
				if(empty($param)){
					$param .= "{$key}={$val}";
				}
				else{
					$param .= "&{$key}={$val}";
				}
			}
			$url = API_SEARCH_STATUSES."{$param}";
			$user_result = json_decode ( $this->curlGetData( $url,10 ), true );
			return $user_result;
		}
		else{
			return array('errno'=>-4,'result'=>'参数错误');
		}
	}

	/**
	 * 获取微博信息（批量）
	 */
	public function getMblog($mids,$fromdb = false){
		if(empty($mids) || !is_array($mids)){
			return false;
		}
		$result = array();
		//此处mc没用到 如果需要启动 需要重新设计以后再说。。
//		if($fromdb == false){
//			foreach($mids as $k => $v){
//				$mcKey = sprintf(MC_KEY_RADIO_MBLOG,$v);
//				$cacheData = $this->getCacheData($mcKey);
//				if($cacheData !== false){
//					$result[$v] = $cacheData;
//					unset($mids[$k]);
//				}
//			}
//		}
		if(!empty($mids)){
			$url = sprintf(API_STATUSES_SHOW_BATCH,implode(',',$mids));
			$tmp = json_decode($this->GetWithCookie($url,1),true);
			$tmp = $tmp['statuses'];
			foreach($tmp as $val){
				$result[$val['mid']] = $val;
			}
		}
		return $result;
	}

		/**
	 * 发布微博动态
	 * @param array $data
	 */
	public function addMblogActivity($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = sprintf(API_ACTIVITY_PATH);
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}
	
	/**
	 * 发布微博接口（带图片）
	 * @param array $data
	 */
	public function addMblogHasPice($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = sprintf(API_STATUSES_ADD_PIC);
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 发布微博接口（仅文本）
	 * @param array $data
	 */
	public function addMblogOnlyText($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = sprintf(API_STATUSES_ADD);
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 转发微博接口
	 * @param array $data
	 */
	public function repostMblog($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = sprintf(API_STATUSES_REPOST);
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 添加评论接口
	 * @param array $data
	 */
	public function addComment($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = API_COMMENTS_CREATE;
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 回复评论接口
	 * @param array $data
	 */
	public function replyComment($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = API_COMMENTS_REPLY;
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 删除评论接口
	 * @param array $data
	 */
	public function delComment($data){
		$data['source'] = RADIO_SOURCE_APP_KEY;
		$url = API_COMMENTS_DESTROY;
		$result = json_decode($this->curlPostDataUserPwd($url,$data,3),true);
		return $result;
	}

	/**
	 * 获取评论列表接口
	 * @param array $data
	 */
	public function getCommentList($mid){
		$url = sprintf(API_COMMENTS_SHOW,$mid);
		$result = json_decode($this->curlGetDataUserPwd($url,3),true);
		return $result;
	}

	/**
	 * 长链转短链接口
	 * @param array $data
	 */
	public function long2short_url($long_url){
		$url = sprintf(API_SHORT_CREATE,$long_url);
		$result = json_decode($this->curlGetDataUserPwd($url,3),true);
		return $result;
	}

	/**
	 * 格式化微博内容
	 * @date 2010-08-31
	 *
	 * @param $args = array(
	 * 						'text' = //微博內容,
	 * 						'time' = //发表时间,
	 * 						'at_array' => array('用户昵称' => //用户昵称);
	 *  					)
	 *  @return array(
	 *  				'text' => //格式化后的微博内容
	 *  				'created_time' => 格式化后的发表时间
	 *  )
	 */
	public function formatText($args){
		$objBase62 = clsFactory::create ('libs/basic/tools', 'bBase62Parse' );
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$objEmo = clsFactory::create('tools/analyze','analyzeEmotion');//分析表情的类
		$objEmoji = clsFactory::create('tools/analyze','analyzeEmoji');//分析emoji表情的类		
		$objKeyWord = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeKeyWord','service');//分析##符号的类
		$objShortLink = clsFactory::create(CLASS_PATH.'tools/analyze/','TAnalyzeShortLink','service'); //解析短连接
		//$objTimeFormat =  clsFactory::create(CLASS_PATH.'tools/formatter','TimeFormatter','service'); //创建日期工具对象

		$result = array();
		//解析表情
		$text = $args['text'];
		$text = $objEmo->textToIcon($text);
		$text = $objEmoji->renderText($text);
		//解析sinaurl标签
		$text = $this->newAnalyseSinaUrl($text);

		//给短URL加超连接
		if(!isset($args['rootid'])){
			$text = $objShortLink->textToShortLink($text,true);
		}
		
		//解析关键字
		$text = $objKeyWord->renderTag($text, true);

		//解析对应@连接地址
		$at_array = $objAt->getAtUsername($text);

		//解析对应@连接地址
		$objAt->atTOlink($text, $at_array, true);

		$result['text'] = $text;
		return $result;
	}

	/**
	 * 解析<sina:link标签
	 * @param $text
	 * @return unknown_type
	 */
	public function newAnalyseSinaUrl($text){
		$out = array();
		$r = array();
		$s = array();
		$content = '';
		$patterns = preg_match_all("/\<\s*sina\s*\:\s*([a-zA-Z0-9]+)\s+([^\>]*)\/?\>/i", $text, $out);
		$fun=$out[1];
		$ora=$out[0];
		foreach($fun as $key => $value){
			if($value=='link'){

				if (!preg_match("/<sina\:link.*?src=\"http:\/\/(t.sina.com.cn|weibo.com)([\/a-zA-Z0-9])*/i",$ora[$key]))
				$urlsPrefix = SHORTURL_DOMAIN;

				$p2 = preg_match_all("/([a-zA-Z0-9_]+)\s*\=\s*[\'\"]([^\'\"]*)[\'\"]/", $ora[$key], $out2);
				$urls = $urlsPrefix . $out2[2][0];
				$text = str_replace($ora[$key], $urls, $text);
			}
		}
		return $text;
	}

	/*
	 * 格式化时间
	 */
	public function timeFormat($time){
		$objTimeFormat =  clsFactory::create(CLASS_PATH.'tools/formatter','TimeFormatter','service'); //创建日期工具对象
		return $objTimeFormat->timeFormat($time);
	}


 	/* 直接执行sql语句	数据库读操作
	 * @param string $table_name	数据表名
	 * @param string$table_field	查询字段
	 * @param array $where		约束条件
	 * @param array $order		排序字段
	 * @array bool or array
	 */
	protected function _dbReadBySql($sql){
		$db = $this->_connectDb();
		if(false == $db) {
			return false;
		}

		$st = $db->prepare($sql);
		if(false == $st->execute()) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sql,'错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return false;
		}
		$result = $st->fetchALL(PDO::FETCH_ASSOC);
		if($result === false){
			$this->writeRadioErrLog(array('获取数据失败', 'SQL：' . $sql, '方法：fetchALL'), 'RADIO_ERR');
			return false;
		}
		return $result;
	}

	/* 直接执行sql语句	数据库写操作
	 * @param string $table_name	数据表名
	 * @param string$table_field	查询字段
	 * @param array $where		约束条件
	 * @param array $order		排序字段
	 * @array bool or array
	 */
	protected function _dbWriteBySql($sql){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return false;
		}

		$st = $db->prepare($sql);
		$result = $st->execute();
		if(false == $result) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sql,'错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return false;
		}
		return $result;
	}

	/**
	 * 数据库select操作
	 * @param string $table_name	数据表名
	 * @param string$table_field	查询字段
	 * @param array $where		约束条件
	 * @param array $order		排序字段
	 * @array bool or array
	 */
	protected function _dbRead($table_name,$table_field,$where = array(),$order = array()){
		$db = $this->_connectDb();
		if(false == $db) {
			return false;
		}
		$sql = "SELECT ".$table_field." FROM ".$table_name." WHERE 1";
		if(!empty($where) && is_array($where)){
			foreach($where as $key => $value){
				if(!empty($value) && is_array($value)){
					if(count($value) > 1){
						$sql .= " and `".$key."` IN (".mysql_escape_string(implode(',',$value)).")";
					}
					else{
						$sql .= " and `".$key."` = '".$value[0]."'";
					}
				}
				else{
					$sql .= " and `".$key."` = '".$value."'";
				}
			}
		}
		if(!empty($order) && is_array($order)){
			$sql .= " order by ".implode(',',$order);
		}
		$st = $db->prepare($sql);
		if(false == $st->execute()) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sql,'错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return false;
		}
		$result = $st->fetchALL(PDO::FETCH_ASSOC);
		if($result === false){
			$this->writeRadioErrLog(array('获取数据失败', 'SQL：' . $sql, '方法：fetchALL'), 'RADIO_ERR');
			return false;
		}
		return $result;
	}

	/**
	 * 数据库insert操作
	 * @param string $table_name	数据表名
	 * @param array $data	查询字段
	 * @array bool or array
	 */
	protected function _dbInsert($table_name,$data){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return false;
		}
		foreach($data as $key => $val){
			$keys[] = "`".$key."`";
			$vals[] = "'".$val."'";
		}

		$sql = "INSERT INTO ".$table_name." (" . implode(',', $keys) . ") VALUES (" . implode(',', $vals) . ")";
		$st = $db->prepare($sql);
		if(false == $st->execute()) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sql,'错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return false;
		}

		return true;
	}

	/**
	 * 数据库select操作
	 * @param string $table_name	数据表名
	 * @param array $data	查询字段
	 * @array bool or array
	 */
	protected function _dbUpdate($table_name,$data,$where){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return false;
		}
		$sql = "UPDATE ".$table_name." SET ";
		foreach($data as $key => $val){
			$set[] = "`".$key."` = '".$val."'";
		}
		$sql .= implode(',',$set)." WHERE 1 ";
		if(!empty($where) && is_array($where)){
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

		$st = $db->prepare($sql);
		if(false == $st->execute()) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sql,'错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return false;
		}

		return true;
	}


	/**
	 * 格式化微博数据
	 * @param array $search_rs
	 * @param array $mblog_rs
	 * @param array $user_rs
	 */
	public function formatFeed($data){
		$content = array();
		$objCheck = clsFactory::create(CLASS_PATH.'tools/check','Check','service');
		foreach($data as $key => $value){
			//格式化用户信息
			$value['user']['link_url'] = T_URL.'/'.$value['user']['profile_url'];
			$value['user']['user_type'] = 'user';
			if($value['user']['level'] == 2){
				$value['user']['user_type'] = $value['user']['verified_type'] == 0 ? 'vip' : 'vip_blue';
			}
			if($value['user']['level'] == 7){
				$value['user']['user_type'] = 'club_talent';
			}
			if(!empty($value['retweeted_status']['user'])){
				$value['retweeted_status']['user']['link_url'] = T_URL.'/'.$value['retweeted_status']['user']['profile_url'];
				$value['retweeted_status']['user']['user_type'] = 'user';
				if($value['retweeted_status']['user']['level'] == 2){
					$value['retweeted_status']['user']['user_type'] = $value['retweeted_status']['user']['verified_type'] == 0 ? 'vip' : 'vip_blue';
				}
				if($value['retweeted_status']['user']['level'] == 7){
					$value['retweeted_status']['user']['user_type'] = 'club_talent';
				}
			}

			$value['old_text'] = $value['text'];
			$args = $this->formatText(array('text' => $value['old_text']));
			$value['text'] = $args['text'];
			$value['encode_text'] = rawurlencode($value['text']);

			$content[$key]['id'] = $value['id'];
			$content[$key]['mid'] = $value['mid'];		//转换获得十进制 mid
			if(empty($value['base62_id'])){
				$mid_tmp = $objCheck->mblogMidConvert($value['mid']);
				$value['base62_id'] = $mid_tmp['mid62'];
			}
			$content[$key]['mblogid'] = $value['base62_id'];
			$content[$key]['time'] = strtotime($value['created_at']);
			$content[$key]['created_at'] = $this->timeFormat($content[$key]['time']);
			$content[$key]['rtnum'] = $value['reposts_count'];
			$content[$key]['cmtnum'] = $value['comments_count'];
			$content[$key]['content'] = array('text' => $value['text']
											,'old_text' => $value['old_text']
											,'encode_text' => $value['encode_text']
											,'pic' => !empty($value['thumbnail_pic']) ? $value['thumbnail_pic'] : ""
										);
			//用户信息
			$content[$key]['uid'] = $value['user']['id'];
			$content[$key]['icon'] = $value['user']['profile_image_url'];
			$content[$key]['nick'] = $value['user']['name'];

			$content[$key]['user'] = $value['user'];
			$content[$key]['user_type'] = $value['user']['user_type'];


			if(!empty($value['retweeted_status'])){
				$args = $this->formatText(array('text' => $value['retweeted_status']['text']));
				$value['retweeted_status']['text'] = $args['text'];
				if(empty($value['retweeted_status']['base62_id'])){
					$mid_tmp = $objCheck->mblogMidConvert($value['retweeted_status']['mid']);
					$value['retweeted_status']['base62_id'] = $mid_tmp['mid62'];
				}
				$content[$key]['rt'] = array('rootuid' => $value['retweeted_status']['user']['id']
										,'rootid' => $value['retweeted_status']['base62_id']
										,'rootmid' => $value['retweeted_status']['mid']
										,'rootrtnum' => $value['retweeted_status']['reposts_count']
										,'rootcmtnum' => $value['retweeted_status']['comments_count']
										,'text' => $value['retweeted_status']['text']
										,'encode_text' => rawurlencode($value['retweeted_status']['text'])
										,'rootuser' => $value['retweeted_status']['user']);

				if(!empty($value['retweeted_status']['thumbnail_pic'])){
					$content[$key]['content']['pic'] = $value['retweeted_status']['thumbnail_pic'];
				}
			}

			if (!empty($content[$key]['content']['pic'])) {
				preg_match('/\/{1}([^\/^\.]+)\.jpg/',$content[$key]['content']['pic'],$match);
				$content[$key] ['content'] ['pid'] = !empty($match[1]) ? $match[1] : "";
			}
			$content[$key]['source_link'] = $value['source'];

			// 2010-10-10 10:12:25 样式
			$content[$key]['date'] = date("Y-m-d H:i:s");
		}
		return $content;
	}

	/**
	 * 批量获取用户微博接口
	 * @param array $data
	 */
	public function getMblogsTimeLine($data){
		$url = sprintf(API_STATUSES_TIMELINE_BATCH,50,$data['page'],$data['uids']);
		$result = json_decode($this->curlGetDataUserPwd($url,3),true);
		return $result;
	}
   /* * 处理每个页面的顶部的数据，之后分配给scope
    * @param $params
    * @param $smarty
    */
function formatScope($cur_rid=0) {
	$dRadioClassification = clsFactory::create(CLASS_PATH.'data/radio','dRadioClassification','service');
	//电台分类信息
	$radio_classfication=$dRadioClassification->getClassificationList();
	$radio_classfication=$radio_classfication['result'];
	//强制添加 全部
	$temp = array(array('classification_id'=>0,'classification_name'=>'全部'));
	$radio_classfication = array_merge($temp,$radio_classfication);
	foreach ($radio_classfication as &$v){
		unset($v['sort']);
		$radio_classfication=json_encode($radio_classfication);
		$data['classification']=$radio_classfication;
	}
	unset($v);
	//获取节目分类信息 强制添加热门标签
	$mc_key = MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES;
	$tmp = $this->getCacheData($mc_key);
	$count = count($tmp);
	for($i=1;$i<=$count;$i++){
		$program_type[$i]['id'] = $tmp[$i]['id'];
		$program_type[$i]['program_type'] = $tmp[$i]['program_type'];
	}
	$program_type = array_merge(array(array('id'=>0,'program_type'=>'热门')),$program_type);
	$data['program_type']=json_encode($program_type);
	//获取节目分类信息 强制添加热门标签  当前正在播放的
	$mc_key = MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES_NOW;
	$program_type_now = array_values($this->getCacheData($mc_key));
	if(empty($program_type_now)){
		$program_type_now = array(array('id'=>0,'program_type'=>'热门'));
	}
	$data['program_type_now']=json_encode($program_type_now);
	//获取省份信息
	$objRadioArea = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioArea', 'service');
	$province = $objRadioArea->getAreaHasOnline();
	$i=0;
	unset($tmp);
	foreach ($province as $k => $v){
		$tmp[$i]=array('id'=>$v['province_id'],'name'=>$v['province_name']);
		$i++;
	}
	$tmp = array_merge(array(array('id'=>0,'name'=>'全部')),$tmp);
	$data['province']=json_encode($tmp);
	if($cur_rid!=0){
		//获取本电台信息
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$radio_info = $objRadioInfo->getRadioInfoByRid(array($cur_rid));
		$radio_info = $radio_info['result'][$cur_rid];
		$radio_info['right_picture']= unserialize($radio_info['right_picture']);
		//获取当前节目信息
		$day = date('N');
		$objRadioProgram = clsFactory::create(CLASS_PATH . "data/radio","dRadioProgramV2","service");
		$program_info = $objRadioProgram->getRadioProgram2($cur_rid,$day);
		$program_now = array();
		foreach($program_info as $v){
			if(strtotime($v['begintime'])<time()&&strtotime($v['endtime'])>time()&&$v['day']==$day){
				$program_now['program_name'] = $v['program_name'];
				break;
			}
		}
	}
	//获取获取本用户信息
	//登录检测
	$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
	$curruserinfo = $mPerson->currentUser();//取得当前登录用户的信息
	$curruserinfo= !empty($curruserinfo) ? $curruserinfo : array();	//当前登录用户信息
	$curruserinfo['power'] = 'visit';
	$cuid = !empty($curruserinfo) ? $curruserinfo['uid'] : 0;	//当前登录用户id
	$curruserinfo['province'] = !empty($curruserinfo['province']) ? $curruserinfo['province'] : 1;
	if($cuid>0){
		//当前用户身份
		$objPower = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioPower', 'service');
		$admin_id = $objPower->getAllPowerList();
		$admin_id = $admin_id['result'];
		if(($cuid > 0 && $cuid == $radio_info['admin_uid']) || in_array($cuid,$admin_id)){
			$curruserinfo['power'] = 'admin';
		}
		if($cur_rid!=0){
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$isCurrentDj = $mRadio->isCurrentDj($cuid,$cur_rid);
			if($isCurrentDj !== false && $radio_info['power'] == 'visit'){
				$curruserinfo['power'] = 'djonline';
			}
		}
	}	
		//过滤作用radio_info
		/*
		$tmp=array();
		$tmp['rid']=$radio_info['rid'];
		$tmp['domain']=$radio_info['domain'];
		$tmp['tag']=$radio_info['tag'];
		$tmp['intro']=$radio_info['intro'];
		$tmp['province_spell']=$radio_info['province_spell'];
		$tmp['right_picture']=$radio_info['right_picture'];
		$tmp['name']=$radio_info['name'];
		$tmp['program_visible']=$radio_info['program_visible'];
		$radio_info=$tmp;
		
		$tmp=array();//过滤curruserinfo
		$tmp['uid']=$curruserinfo['uid'];
		$tmp['name']=$curruserinfo['name'];
		$tmp['power']=$curruserinfo['power'];
		$curruserinfo=$tmp;
		*/
        $data['servertime'] = time();
        $data['rid'] = $cur_rid;
        $data['radio_info'] = $radio_info;
        $data['program_now'] = $program_now;
        $data['curruserinfo'] = $curruserinfo;
        $radio_info['radio_url'] = RADIO_URL."/".$radio_info['province_spell'].'/'.$radio_info['domain'];
        $data['cuid'] = $cuid;
        if($cuid==0) {
            $islogin = 0;
        } else {
			$islogin = 1;
        }
        $data['islogin'] = $islogin;
        return $data;
    }

}
?>
