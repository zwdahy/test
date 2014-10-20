<?php

/**
 * Project:     radio
 * File:        ajax_getnewfeedlist.php
 * 
 * 获取feed列表html
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class NewProgramFeedList extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		//$this->para['mid'] = request::post('mid', 'str');
		$this->para['time'] = request::post('time', 'int');
		$this->para['pgname'] = intval(request::post('pgname', 'str'));

//		//@test
//		$this->para['time'] = 1396341139;
//		$this->para['pgname'] = "早安音乐";
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
		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $mRadio->getNewProgramFeed($this->para['time'],$this->para['pgname']);
		$jsonArray['code'] = 'A00006';
		if($result['errorno'] == 1 && count($result['result']) > 0){
//			$display = clsFactory::create('framework/tools/display','DisplaySmarty');
//        	$smarty = $display->getSmartyObj();
			$jsonArray['data']['result']=$result['result'];
		}
		else{
			$jsonArray['code'] = 'E00001';
		}		
		$this->display($jsonArray, 'json');
	}
}

new NewProgramFeedList(RADIO_APP_SOURCE);
?>