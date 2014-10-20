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
class CheckNewProgramFeed extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['time'] = request::post('time', 'int');
		$this->para['pgname'] = intval(request::post('pgname', 'str'));
		
//		$this->para['time'] = 1405341494;
//		$this->para['pgname'] = "早安音乐";
//		$this->para['pgid'] = 77066;
//		$this->para['type'] = 'seek_feed';
		
		//参数检测处理
		if(empty($this->para['time']) || empty($this->para['pgname'])) {
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
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $mRadio->checkNewProgramFeed($this->para['time'],$this->para['pgname']);

		if(!empty($result['result'])){
			$jsonArray['data']['hasnewfeed'] = true;
		}else{
			$jsonArray['data']['hasnewfeed'] = false;
		}
		//@test
		//$jsonArray['data']['hasnewfeed'] = true;
		$this->display($jsonArray, 'json');
	}
}

new CheckNewProgramFeed(RADIO_APP_SOURCE);
?>