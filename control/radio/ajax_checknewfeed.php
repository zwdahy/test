<?php

/**
 * Project:     radio
 * File:        ajax_getfeedlist.php
 * 
 * 获取feed列表html
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class CheckNewFeed extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['time'] = intval($_POST['time']);
		$this->para['rid'] = intval(request::post('rid', 'STR'));
		$this->para['type'] = request::post('type', 'STR');
		//print_r($this->para);exit;
		
		//参数检测处理
		if(empty($this->para['time']) || empty($this->para['rid']) || empty($this->para['type'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}				
		$jsonArray['code'] = 'A00006';
		$jsonArray['data']['hasnewfeed'] = false;
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['type'] == 'user_feed'){
			$result = $mRadio->checkNewFeed($this->para['time'],$this->para['rid']);
		}
		else{
			$result = $mRadio->checkNewDjFeed($this->para['time'],$this->para['rid']);
		}				
		if($result['errorno'] == 1){
			$jsonArray['data']['hasnewfeed'] = true;
			$jsonArray['data']['time'] = $result['result'];
		}				
		$this->display($jsonArray, 'json');
	}
}

new CheckNewFeed(RADIO_APP_SOURCE);
?>