<?php

/**
 * Project:     radio
 * File:        ajax_getnews.php
 * 
 * ajax获取新闻列表页
 * 
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "config/radiostream.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class getNEWS extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['pageid'] = request::get('pageid', 'int');
		//参数检测处理
		if(empty($this->para['pageid']) || !is_int($this->para['pageid'])) {
			$this->para['pageid'] = 1;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$pageid = $this->para['pageid'];
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取所需页新闻
		$newsList = $mRadio->getNewsForPage($pageid);
		$newsList = $newsList['result'];
		
		//获取总页面数
		$newsNum = $mRadio->getNewsNum();
		$newsNum = $newsNum['result'][0]['count'];
		$pageCount = ceil($newsNum/20);
		
		//身份校验
		$power = 'visit';
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
		$cuserInfo = $person->currentUser();
		$cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
		if($cuid > 0){
			$admin_id = $mRadio->getAllPowerList();
			$admin_id = $admin_id['result'];
			if(in_array($cuid,$admin_id)){
				$power = 'admin';
			}
		}
		if(!empty($newsList)){
				$jsonArray['code'] = 'A00006';
				$jsonArray['newsList'] = $newsList;			
				$jsonArray['pageCount'] = $pageCount;
				$jsonArray['power'] = $power;
				$jsonArray['totalNum'] = $newsNum;
		}
		else{
			$jsonArray['code'] = 'E00001';
		}		
		
		$this->display($jsonArray, 'json');
	}	
}

new getNEWS(RADIO_APP_SOURCE);
?>