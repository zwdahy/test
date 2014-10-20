<?php
/**
 * Project:     电台管理后台接口
 * File:        setradio.php
 * 
 * 编辑电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setradio.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setMinfo extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['rid'] = request::post('rid', 'INT');					// 电台ID
		$this->para['url'] = request::post('url', 'STR');					// 官方微博url
		$this->para['intro'] = request::post('intro', 'STR');				// 电台简介
		$this->para['img_path'] = request::post('img_path', 'STR');			// 推荐图片
		$this->para['admin_url'] = request::post('admin_url', 'STR');		// 管理权限微博url
		
		//判断输入的字符长度不要超过200汉字
		if (strlen($this->para['intro']) > 600) {
			$data = array(
					'errno' => -9,
					'errmsg' => "简介文字不可超过200个汉字"
				);
			$this->display($data, 'json');
			return true;
		}
		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$args = array(
			'rid' => $this->para['rid'],			
			'intro' => $this->para['intro'],
			'img_path' => $this->para['img_path'],
		);
		
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
		}
		else{
			$args['admin_uid'] = 0;
			$args['admin_url'] = '';
		}
		
		$result = $obj->setMinfo($args);
		$data = array();
		if($result['errorno'] == 1) {
			$aMinfo = $obj->getUserInfoByUid(array($args['uid'],$args['admin_uid']));
			$Minfo = $aMinfo[$args['uid']];			
			$adminMinfo = $aMinfo[$args['admin_uid']];
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result'],
				'nick' => $Minfo['name'],
				'portrait' => $Minfo['profile_image_url'],
				'admin_nick' => $adminMinfo['name'],
				'admin_portrait' => $adminMinfo['profile_image_url']
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
new setMinfo(RADIO_APP_SOURCE);
?>