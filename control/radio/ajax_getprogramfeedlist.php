<?php

/**
 * Project:     radio
 * File:        ajax_getfeedlist.php
 * 
 * 获取回听页面用户feed列表html
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
		$this->para['page'] = intval(request::post('pageNum', 'INT'));
		//$this->para['keyword'] = request::post('keyword', 'str');		
		$this->para['pgname']  = request::post('pgname', 'STR');
		//$this->para['type']  = '';
//		$this->para['pgname']  = '早安音乐';

		//参数检测处理
		if(empty($this->para['pgname'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		$this->para['page'] = !empty($this->para['page']) ? $this->para['page'] : 1;
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		//根据type类型进行feed流的查询
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$aFeedList= $mRadio->getFeedListByProgramName($this->para['pgname'],$this->para['page']);
		//获取最新feed的信息
		$first_feedinfo = $mRadio->getFirstProgramFeedInfo($this->para['pgname']);
		$first_feedinfo = $first_feedinfo['result'];
		$jsonArray['code'] = 'A00006';
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