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
class FeedList extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['page'] = intval(request::post('pageNum', 'str'));
		//$this->para['keyword'] = request::post('keyword', 'str');		
		$this->para['rid']  = intval(request::post('rid', 'str'));
		$this->para['type']  = request::post('type', 'str');
		//$this->para['type']  = 'dj_feed';
		//$this->para['rid']  = 99;

		//参数检测处理
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		$this->para['page'] = !empty($this->para['page']) ? $this->para['page'] : 1;
		$this->para['type'] = !empty($this->para['type']) ? $this->para['type'] : 'user_feed';
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		//根据type类型进行feed流的查询
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['type']=='user_feed'){
			$aFeedList= $mRadio->getFeedListByRid($this->para['rid'],$this->para['page']);
			//获取最新feed的信息
			$first_feedinfo = $mRadio->getFirstFeedInfo($this->para['rid']);
			$first_feedinfo = $first_feedinfo['result'];
			$jsonArray['code'] = 'A00006';
		}else{
			$aFeedList = $mRadio->getDjFeedListByRid($this->para['rid'],$this->para['page']);
			$first_feedinfo = $mRadio->getFirstDjFeedInfo($this->para['rid']);
			$first_feedinfo = $first_feedinfo['result'];
			$jsonArray['code'] = 'A00006';
		}
		if(!empty($first_feedinfo)){
			$jsonArray['time'] = $first_feedinfo['time'];
			$jsonArray['mid'] = $first_feedinfo['mid'];
		}
		else{
			$jsonArray['time'] = time();
			$jsonArray['mid'] = -1;
		}
		$jsonArray['data'] = $aFeedList;
		$this->display($jsonArray, 'json');
	}
}

new FeedList(RADIO_APP_SOURCE);
?>