<?php

/**
 * Project:     radio
 * File:        ajax_getnamecard.php
 * 
 * 获取电台dj名片
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetNameCard extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['uid'] = request::post('uid', 'str');
		//$this->para['from'] = request::post('from', 'str');
		//$this->para['pname'] = request::post('pname', 'str');
		//@test
		//$this->para['uid'] = 1852612541;

		//参数检测处理
		if(empty($this->para['uid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		
		//$this->para['from'] = !empty($this->para['from']) ? $this->para['from'] : '0';
		//$this->para['pname'] = !empty($this->para['pname']) ? $this->para['pname'] : '';
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}

		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$name_card = $mRadio->getNameCard($this->para['uid']); 
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = $name_card;
		$this->display($jsonArray, 'json');
	}
}

new GetNameCard(RADIO_APP_SOURCE);
?>