<?php

/**
 * Project:     radio
 * File:        ajax_getprogramdjinfo.php
 * 
 * 获取节目在线dj html
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetProgramDjInfo extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['djinfo'] = request::post('djinfo', 'str');		

		//测试数据
/*
		$this->para['djinfo'] = "1660386667#http://weibo.com/1660386667#小飞,1661558660#http://weibo.com/1661558660#喻舟";
*/		
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$cuserInfo = $person->currentUser();
		//global $RADIO_ADMIN;
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$admin_id = $mRadio->getAllPowerList();
			$admin_id = $admin_id['result'];
		//参数检测处理
		if(empty($this->para['djinfo'])) {
			$jsonArray['code'] = 'A00006';
			$jsonArray['data']['html'] = "";
			if(in_array($cuserInfo['uid'],$admin_id)){
				$jsonArray['data']['hidetopic'] = false;
			}
			else{
				$jsonArray['data']['hidetopic'] = true;
			}
			$this->display($jsonArray, 'json');
			return false;
		}
		
		
		$tmp = explode(',',$this->para['djinfo']);
		$dj_info = array();
		$djuids = array();
		foreach ($tmp as $value){
			$tmp_dj_info = explode('#',$value);
			$dj_info[$tmp_dj_info[0]] = array('uid' => intval($tmp_dj_info[0]),'url' => $tmp_dj_info[1],'screen_name' => $tmp_dj_info[2]);
			if(!in_array($tmp_dj_info[0],$djuids)){
				$djuids[] = intval($tmp_dj_info[0]);
			}			
		}
		$userInfo = $mRadio->getUserInfoByUid($djuids);
				
		if($userInfo){
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$cuserInfo = $person->currentUser();
			$aRelation = $mRadio->checkattrelation($cuserInfo['uid'],$djuids);
			foreach($dj_info as $key => &$value){
				$value['screen_name'] = !empty($value['screen_name']) ? $value['screen_name'] : $userInfo[$key]['name'];
				$value['userinfo'] = $userInfo[$key];
				$value['attention'] = !empty($aRelation['one2many'][$key]) ? $aRelation['one2many'][$key] : false;
				
			}
			$jsonArray['code'] = 'A00006';
		
			$display = clsFactory::create('framework/tools/display','DisplaySmarty');
	        $smarty = $display->getSmartyObj();
	        $params = array();
	        $html = array();
			
	        $params['data'] = $dj_info;
	        $html = insert_radio_program_djinfo($params,$smarty);
			$jsonArray['data']['html'] = $html;
			//判断当前用户是否在线dj			
			if(in_array($cuserInfo['uid'],$djuids) || in_array($cuserInfo['uid'],$admin_id)){
				$jsonArray['data']['hidetopic'] = false;
			}
			else{
				$jsonArray['data']['hidetopic'] = true;
			}
		}
		else{
			$jsonArray['code'] = 'E00001';
		}
							
		$this->display($jsonArray, 'json');
	}
}

new GetProgramDjInfo(RADIO_APP_SOURCE);
?>