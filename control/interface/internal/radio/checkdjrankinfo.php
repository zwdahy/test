<?php
/**
 * Project:     电台管理后台接口
 * File:        getradio.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradio.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getRadio extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/		
		$this->para['url'] = request::post('url', 'STR');		//用户微博地址
		$this->para['rid'] = request::post('rid', 'STR');					//电台id
		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		
		if(empty($this->para['url'])){
			$this->display(array('errno' => -9,'errmsg' => "请输入用户url！"));
		}
		if(empty($this->para['rid'])){
			$this->display(array('errno' => -9,'errmsg' => "请输入电台id！"));
		}
		
		//验证用户url
		$tmp = explode('/',$this->para['url']);
		$domain = '';
		$uid = 0;
		foreach($tmp as $k => $v){
			if($v == 't.sina.com.cn' || $v == 'weibo.com'){
				if($tmp[$k+1] == 'u'){
					$uid = $tmp[$k+2];
				}
				else{
					$domain = $tmp[$k+1];
				}
				break;
			}
		}
		if(empty($uid)){
			if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
				$uid = $match[1];
			}
			else{
				$rs = $obj->getUserInfoByDomain($domain);
				if($rs['id'] > 0){
					$uid = $rs['id'];
				}					
			}
		}
		
		//验证新添加的dj是否已经存在于20名单
		$result = $obj->getActiveDjRank(20);
		$rank_uids = array();
		foreach ($result as $key=>$val){
			$rank_uids[] = $val['userinfo']['id'];
		}
		if(in_array($uid,$rank_uids)){
			$this->display(array('errmsg' => "此用户已添加，不要重复操作"),'json');
			return true;
		}
		
		$userinfo = $obj->getUserInfoByUid(array($uid));
		if(empty($userinfo[$uid])){
			$this->display(array('errmsg' => "用户url不存在！"),'json');
			return true;
		}
		$radioinfo = $obj->getRadioInfoByRid(array($this->para['rid']));	
		if(empty($radioinfo['result'][$this->para['rid']])){
			$this->display(array('errmsg' => "电台id不存在！"),'json');
			return true;
		}
		if($radioinfo['result'][$this->para['rid']]['online'] == '2'){
			$this->display(array('errmsg' => "电台处于下线状态！"),'json');
			return true;
		}
		
		$result = array('uid' => $uid,'user_name' => $userinfo[$uid]['name'],'radio_name' => $radioinfo['result'][$this->para['rid']]['name']);			
		$data = array(
			'errno' => 1,
			'errmsg' => '成功',			
			'result' => $result
		);		
		$this->display($data, 'json');
		return true;
	}
}
new getRadio(RADIO_APP_SOURCE);
?>