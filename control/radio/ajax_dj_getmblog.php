<?php

/**
 * Project:     radio
 * File:        ajax_dj_getmblog.php
 * 
 * 获取在线dj微博展示区
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
class DjGetMblog extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));		
		//$this->para['rid'] = 99;
		//参数检测处理
		if(empty($this->para['rid'])) {
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
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$show = false;
		if($mPerson->isLogined()) {			
			$currUid = $mPerson->getCurrentUserUid();
		}
		if($currUid>0){
			$isCurrentDj = $mRadio->isCurrentDj($currUid,$this->para['rid']);
			if($isCurrentDj != false){
				$show = true;
			}
		}
		$data = $mRadio->getDjFeed($this->para['rid']);
		//print_r($data);exit;
		if(!empty($data['result'])){
			foreach($data['result'] as &$v){
				$v['show'] = $show;
			}
			unset($v);
		}
		if($data['errorno']==1){
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = array_values($data['result']);
		}else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = '获取失败';
		}

		$this->display($jsonArray, 'json');
	}
}

new DjGetMblog(RADIO_APP_SOURCE);
?>