<?php
/**
 * 用户模型层
 * 
 * @date  2010-8-30 
 * @package model
 * @author  刘勇刚<yonggang@staff.sina.com.cn>
 * @copyright (c) 2010, 新浪网 MiniBlog All rights reserved.
 */
class mPerson extends model{
		
	/**
	 * 检测用户是否已经登录
	 *
	 * @return 未登录：false
	 * 		        已登录：用户id
	 */
	public function isLogined() {
		//include_once("SSOClient.php");
		$sso = new SSOClient(); //统一注册cookie检测
		$sso->setConfig("use_vf", true); 
		$noRedirect = 1;//跳转检测
		if ($sso->isLogined($noRedirect)) {
			return true;
		} else {
		  	return false;
		}
	}
	
	/**
	 * 判断用户是否登录
	 *
	 * @return 未登录：false  登录：array
	 * 			array(
	 * 					uid		=>,	//用户id
	 * 					isOpen	=>	//是否开通微博客
	 * 					level	=>	//用户等级 1：普通用户 2：vip用户
	 * 					guide	=>	//是否通过引导流程
	 * 					name	=>	//用户昵称
	 * 					domain	=>	//个性域名
	 * 					icon	=>	//用户头像
	 * 					serviceList	=>	//用户开通服务信息
	 * 
	 */
	public static function currentUser($isGetInfo=true, $isGetCookie=false) {
		//include_once("SSOClient.php");
		// 统一注册cookie检测
		$sso = new SSOClient();
		$sso->setConfig("use_vf", true);
		$noRedirect = 0;	// 跳转检测
		if(!$sso->isLogined($noRedirect)) return false;
		$uid = $sso->getUniqueid();
		$userInfo = array();
		if($isGetInfo) {
			//通过openapi获得用户信息
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');
			$userInfo = $mRadio->getUserInfoByUid(array($uid));
			$userInfo = $userInfo[$uid];
		} else {
			$userInfo['uid'] = $uid;
		}
		$isGetCookie=true;
		if($isGetCookie) {
			$userInfo['cookie'] = $sso->getUserInfo();
		}
//		echo '<pre>';
//		print_r($userInfo);
//		exit;
		//echo '<pre>';
		//print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		//exit;
		return $userInfo;
	}

	/**
	 * 获取登陆用户的uid
	 *
	 * @return 未登录：false  登录：array
	 * 			array(
	 * 					uid		=>,	//用户id
	 * 
	 */
	public static function getCurrentUserUid($isGetInfo=true, $isGetCookie=false) {
		//include_once("SSOClient.php");
		// 统一注册cookie检测
		$sso = new SSOClient();
		$sso->setConfig("use_vf", true); 
		$noRedirect = 0;	// 跳转检测
		if(!$sso->isLogined($noRedirect)) return false;
		$uid = $sso->getUniqueid();
		$isGetCookie=1;
		if($isGetCookie) {
			$userInfo = $sso->getUserInfo();
			$uid=$userInfo['uniqueid'];
		}
		return $uid;
	}
	
	public function getCurrentUserInfo($uid){
		$pmc = clsFactory::create(CLASS_PATH.'data', 'dPMc','service');
		$key = sprintf(MC_PUBLIC_LOGINED_USERINFO, $uid);
		$userInfo = $pmc->get($key);
		if($userInfo == false){
			$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
			$args = array(
						'uid'=>$uid,
						'fuid'=>$uid,
						'size'=>30
			);
			$userInfo = $o_person->getUserInfo($args);
			if(is_array($userInfo) && !empty($userInfo)){
				if(empty($userInfo['province']) && empty($userInfo['city'])){
					return false;
				}else{
					$pmc->set($key, $userInfo, MC_TO_PUBLIC_LOGINED_USERINFO);
				}
			}	
		}
		return $userInfo;
	}
	
	/**
	 * 根据uid取得用户的信息
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							’uid'   => $uid // 当前登录uid
							'fuid'    =>  对方用户UID
							'detail'  =>  是否获取详细资料  
							            默认为0，不获取；1为获取详细资料
							'size'    =>  头像尺寸,30/50/120
							)
	  * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array(用户详细信息);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function getUserInfo($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$userInfo = $o_person->getUserInfo($args);
		return $this->returnStyle($userInfo);
	}
	
	/**
	 * 退出登录
	 *
	 */
	public function logout() {
		include_once(PATH_ROOT."/apps/sso/SSOConfig.php");
		//include_once("SSOClient.php");
		$sso = new SSOClient();
		$sso->setConfig("use_vf", true); 
		$sso->logout();
		$cookieKeys = array_keys($_COOKIE);
		
		$urlPrefix = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
		$urlPrefix = $urlPrefix."://".$_SERVER['HTTP_HOST'];
		$returnURL = urlencode($urlPrefix.$_SERVER['REQUEST_URI']);
		$url = LOGOUT_URL.'?r='.$returnURL;
		header("Location: $url");
		exit;
		foreach($cookieKeys as $key) {
			if($key=="loginname" or $key=="SPHPSESSID" or $key=="PHPSESSID" or $key=="spaceuser") continue;
			header("Set-Cookie: {$key}=; domain=sina.com.cn;  expires='1990-01-01';path=/\n", false);
		}
	}	
	
	/**
	 * 添加关注
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuid'  => //增加关注的用户UID，多个UID之间用逗号间隔
							)
	  * @return array(	
		   			'flag'  => true or false,
	 			)
	 */
	public function addAttention($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$addInfo = $o_person->addAttention($args);
		$result = array();
		if($addInfo){
			$result = array(
							'flag'   => true
						);
		}
		else{
			$result = array(
							'flag'  => false
						);			
		}
		return $result;
	}
	
	/**
	 * 新内部接口，支持新增参数
	 * @author chengliang1
	 *
	 * @param unknown_type $args
	 */
	public function newAddAttention($getdata,$postdata){
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$addInfo = $o_person->newAddAttention($getdata,$postdata);
		$result = array();
		if($addInfo["errno"]){
			$result = array(
							'flag'   => true
						);
		}
		else{
			$result = array(
							'flag'  => false
						);			
		}
		return $result;
	}
	public function base_addAttention($args) {
		//设置公共参数
		$paras    = array('uid'=>ADMIN_UID, 'appid'=>DEFAULT_APP_ID, 'appkey'=>OPENAPI_APP_KEY, 'cip'=>Check::getIp());
		$basicObj = clsFactory::create('libs/basic/model','bmAttention');//创建类对象
		$basicObj->setParas($paras);
		//调用业务方法
		$result   = $basicObj->addAttention($args);
		return $result;
	}
	/**
	 * 取得用户的关注关系(批量)
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuids'  => //要取得关系的用户uid,批量用逗号分隔
							)
	  * @return array(	
		   			'flag'  => true or false,
		   			'result' => 如果flag为true,返回array('uid' => array{"relation"=>1});
		   						1为已关注，0为未关注，4为黑名单
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function getRelation($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$relationInfo = $o_person->getRelation($args);
		return $this->returnStyle($relationInfo);
	}
	
	/**
	 * 检查用户是否绑定手机
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuid'  => //要查询的用户uid
							)
	  * @return array(
	  * 			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 							'code' => 返回码M06001 待绑定,含手机号，M06002 成功,含手机号，M06000 手机未绑定
		 							'data' => array(
		 											 'mobile'   =>  手机号，
		 											 'isnotice'   =>  是否提醒
		 											)
		 							)
	 			)
	 */
	public function checkUidBind($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$relationInfo = $o_person->checkUidBind($args);
		return $this->returnStyle($relationInfo);
	}
	
	
	/**
	 * 将要返回前台的数组格式化
	 * 
	 * @param array $array
	 * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('uid' => 用户uid);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	private function returnStyle($array){
		$result = array();
		if($array){
			$result = array(
							'flag'   => true,
							'result' => $array
							);
		}
		else{
			$result = array(
							'flag'   => false,
							'errorno' => '-1'
							);
		}
		return $result;
	}		
	
	/**
	 * getIconvers
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-09-15
	 * 
	 * @param 	$args = array(
							'uid'   => //当前用户uid
							'fuids'  => //要查询的用户的UID，支持批量
							)
	  * @return array(
	  * 			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 								［uid］=> 头像版本
		 							)
	 			)
	 */
	public function getIconvers($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$relationInfo = $o_person->getIconvers($args);
		return $this->returnStyle($relationInfo);
	}
	
	/**
	 * 批量获取用户信息，调用开放平台的接口
	 * 
	 */
	public function getUsersByShowBatch($uids,$trim_status=1,$has_extend=0,$simplify=0,$is_encode=0){
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$userListInfo = $o_person->getUsersByShowBatch($uids,$trim_status,$has_extend,$simplify,$is_encode);
		return $this->returnStyle($userListInfo);
	}

	/**此接口ms不能用 2014/4/26
	 * 获取用户关注关系
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * @param $args参数数组包含如下参数项
	 * 
	 * uid:用户uid(POST方式，必选)
     * fuids:被检察用户uid(POST方式，必选，支持批量，以逗号隔开) 
	 * @return array(2) {["one2many"]=>array(1) {[1821046267]=> bool(true)}
     *                   ["many2one"]=>array(1) {[1821046267]=>bool(true)}}
	 */
	public function newGetUserRelation($args) {
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$relationInfo = $o_person->newGetUserRelation($args);
		return $this->returnStyle($relationInfo);
	}

	/* 获取用户关注关系
	 * @author wenda(wenda@staff.sina.com.cn)
	 * @param $args参数数组包含如下参数项
	 * url	:http://wiki.intra.weibo.com/1/friendships/exists_batch_internal
	 *	source	true	string	申请应用时分配的AppKey，调用接口时候代表应用的唯一身份W
	 *	uids	true	int64	指定需要判断是否已经关注的用户id列表
	 *	uid	true	int64	需要判断关注关系的源ID。
	 * @return array(2) {["one2many"]=>array(1) {[1821046267]=> bool(true)}
     *                   ["many2one"]=>array(1) {[1821046267]=>bool(true)}}
	 */
	 public function getRelation2($uid,$uids){
		if(empty($uid)||empty($uids)||!is_array($uids)){
			return $this->returnStyle(FALSE);
		}
		$o_person = clsFactory::create(CLASS_PATH.'data', 'dPerson','service');
		$relationInfo = $o_person->getRelation2($uid,$uids);
		return $this->returnStyle($relationInfo);
	}



	
}
?>