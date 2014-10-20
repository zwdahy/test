<?php
/**
 * Project:     电台管理后台接口
 * File:        addradio.php
 * 
 * 添加电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/addradio.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
include_once SERVER_ROOT . 'config/radioareaspell.php';
class addRadio extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/
		$this->para['domain'] = request::post('domain', 'STR');		// 电台域名
		$this->para['info'] = request::post('info', 'STR');		// 电台说明
		$this->para['tag'] = request::post('tag', 'STR');		// 电台话题
		$this->para['source'] = request::post('source', 'STR');		// 音频源标识
		$this->para['recommend'] = request::post('recommend', 'STR');		// 推荐排位
		$this->para['upuid'] = request::post('upuid', 'STR');		// 更新人UID	
		$this->para['classification_id'] = request::post('classification_id', 'INT');
		$this->para['is_feed'] = request::post('is_feed', 'INT');	
		$this->para['province_id'] = request::post('province_id', 'INT');	//省级id
		$this->para['city_id'] = request::post('city_id', 'INT');			//市级id
		// $this->para['uptime'] = request::post('uptime', 'STR');		// 更新时间
		$this->para['chk_faceicons'] = request::post('chk_faceicons', 'STR');		//多选框上传头像
		$this->para['chk_bindphone'] = request::post('chk_bindphone', 'STR');		//多选框绑定手机
		$this->para['chk_reg_time'] = request::post('chk_reg_time', 'STR');			//多选框注册时间
		$this->para['chk_weibo_count'] = request::post('chk_weibo_count', 'STR');	//多选框微博数
		$this->para['chk_fans_count'] = request::post('chk_fans_count', 'STR');		//多选框粉丝数
		$this->para['rtime'] = request::post('rtime', 'STR');						//注册天数
		$this->para['mblogs'] = request::post('mblogs', 'STR');						//微博数
		$this->para['myfans'] = request::post('myfans', 'STR');						//粉丝数
		$this->para['radio_right'] = request::post('radio_right', 'STR');			//右侧推荐图是否显示，1为不显示，2为显示
		$this->para['right_img_path'] = request::post('right_img_path', 'STR'); 	//右侧推荐图片地址
		$this->para['right_link_path'] = request::post('right_link_path', 'STR');	//右侧推荐图片链接地址
		//---------合并电台官方信息到编辑电台信息--------------
		$this->para['url'] = request::post('url', 'STR');
		$this->para['intro'] = request::post('intro', 'STR');
		$this->para['img_path'] = request::post('img_path', 'STR');
		$this->para['admin_url'] = request::post('admin_url', 'STR');
		
		return true;
	}
	protected function action() {		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['province_id'] == 0 || $this->para['city_id'] == 0){
			$error = array('errmsg' => '请选择电台地区');
			$this->display($error, 'json');
			return true;
		}
		if(!preg_match('/^[a-zA-Z]+[0-9]+\.{0,1}[0-9]+$/',$this->para['domain'])){
			$error = array('errmsg' => '域名输入有误');
			$this->display($error, 'json');
			return true;
		}
		//判断输入的字符长度不要超过200汉字
		if (strlen($this->para['intro']) > 600) {
			$error = array('errmsg' => '电台介绍不可超过200个汉字');
			$this->display($error, 'json');
			return true;
		}	
		
		
		//多选框验证及序列化的处理
		$temSerArray = array();
		if ('true' == $this->para['chk_faceicons']) {
			$temSerArray['faceicons'] = 'true';
		}
		if ('true' == $this->para['chk_bindphone']) {
			$temSerArray['bindphone'] = 'true';
		}
		if ('true' == $this->para['chk_reg_time'] && isset($this->para['rtime'])) {
			$temSerArray['rtime'] = $this->para['rtime'];
		}
		if ('true' == $this->para['chk_weibo_count'] && isset($this->para['mblogs'])) {
			$temSerArray['mblogs'] = $this->para['mblogs'];
		}
		if ('true' == $this->para['chk_fans_count'] && isset($this->para['myfans'])) {
			$temSerArray['myfans'] = $this->para['myfans'];
		}
		//组成数组，序列化后存储
		$feed_require = serialize($temSerArray);
		
		global $CONF_PROVINCE_SPELL;
		$args = array(
			'domain' => $this->para['domain'],
			'info' => $this->para['info'],
			'tag' => $this->para['tag'],
			'source' => $this->para['source'],
			'recommend' => $this->para['recommend'],
			'upuid' => $this->para['upuid'],
			'classification_id' => $this->para['classification_id'],
			'is_feed' => $this->para['is_feed'],
			'province_id' => $this->para['province_id'],
			'province_spell' => !empty($CONF_PROVINCE_SPELL[$this->para['province_id']]) ? $CONF_PROVINCE_SPELL[$this->para['province_id']] : '',
			'city_id' => $this->para['city_id'],
			// 'uptime' => $this->para['uptime']
			'feed_require' => $feed_require,
			'online' => '2',
			'intro' => $this->para['intro'],
			'img_path' => $this->para['img_path']
		);
		
		//-----合并官方信息到电台信息
		//判断官方微博url是否存在
		if(!empty($this->para['url'])){
			$tmp = explode('/',$this->para['url']);
			foreach($tmp as $k => $v){
				if($v == 't.sina.com.cn' || $v == 'weibo.com'){
					if($tmp[$k+1] == 'u'){
						$args['uid'] = $tmp[$k+2];
					}
					else{
						$domain = $tmp[$k+1];
					}
					break;
				}
			}
			if(empty($args['uid'])){
				$domain = trim($domain);				
				if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
					$args['uid'] = $match[1];
				}
				else{
					$uid_result = $obj->getUserInfoByDomain($domain);
					if($uid_result['id'] > 0){					
						$args['uid'] = $uid_result['id'];
					}
					else{
						$data = array(
							'errno' => -9,
							'errmsg' => "官方微博url无效"
						);
						$this->display($data, 'json');
						return true;
					}
				}
			}			
			$args['url'] = $this->para['url'];
		}
		
		//判断管理权限微博url是否存在
		if(!empty($this->para['admin_url'])){
			$tmp = explode('/',$this->para['admin_url']);
			foreach($tmp as $k => $v){
				if($v == 't.sina.com.cn' || $v == 'weibo.com'){
					if($tmp[$k+1] == 'u'){
						$args['admin_uid'] = $tmp[$k+2];
					}
					else{
						$domain = $tmp[$k+1];
					}
					break;
				}
			}
			if(empty($args['admin_uid'])){
				$domain = trim($domain);
				if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
					$args['admin_uid'] = $match[1];
				}
				else{
					$uid_result = $obj->getUserInfoByDomain($domain);				
					if($uid_result['id'] > 0){
						$args['admin_uid'] = $uid_result['id'];
					}
					else{
						$data = array(
							'errno' => -9,
							'errmsg' => "管理权限url无效"
						);
						$this->display($data, 'json');
						return true;
					}
				}			
			}	
			$args['admin_url'] = $this->para['admin_url'];
		}else{
			$args['admin_uid'] = 0;
			$args['admin_url'] = '';
		}
		
		
		
		if($this->para['radio_right'] == '2'){
			//输入的右侧的地址和链接
			$temRightArr = array();
			$temRightArr['right_pic_url'] = $this->para['right_img_path'];
			$temRightArr['right_link_url'] = $this->para['right_link_path'];
							
			$args['right_picture'] = serialize($temRightArr);
		}
		else{
			$args['right_picture'] = serialize(array());
		}
		$result = $obj->addRadio($args);
		$data = array();
		if($result['errorno'] == 1) {
			$obj->addRadioArea($this->para['province_id']);
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result']
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new addRadio(RADIO_APP_SOURCE);
?>