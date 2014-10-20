<?php

/**
 * Project:     radio
 * File:        ajax_dj_delmblog.php
 * 
 * 隐藏在线dj微博展示区的微博
 * 
 * @date	2014/5/13
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class DjDelMblog extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['mid'] = request::post('mid', 'STR');
		$this->para['rid'] = intval(request::post('rid', 'STR'));		
		//$this->para['hide'] = request::post('hide', 'int');	//隐藏功能 传递1表示隐藏该条微博		
		//$this->para['url'] = "http://weibo.com/1890926607/B3XGbt7xL";
//		$this->para['rid'] = 10;
//		$this->para['mid'] = "3708602013911789";
		//参数检测处理
		if(empty($this->para['mid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
//		if(empty($this->para['hide'])) {
//			$this->setCError('M00009', '参数错误');
//			return false;
//		}

		//用户验证是否登录 加强的话 需要验证是否是在线dj或者管理员
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {			
			$currUid = $mPerson->getCurrentUserUid();
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}
	}

	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mid = $this->para['mid'];
//		print '<pre>';
//		print_r($mid);
//		exit;
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		//获取当天节目单确定mc生命周期
		//获取当天的节目单用来确定mc生命周期
		$today = date('N');
		$programList = $mRadio->getRadioProgram2($this->para['rid'],$today);
		$time = time();
		foreach($programList as &$v){
			if( strtotime( $v['begintime'] )<=$time&&strtotime( $v['endtime'] )>$time ){
				$liveTime = strtotime($v['endtime'])-strtotime($v['begintime']);
					break;//找到一个就ok啦
			}
		}
		unset($v);
		//print_r($liveTime);
		//exit;
//		print '<pre>';
//		print_r($programList);
//		exit;

		$result = $mRadio->delDjFeed(array($mid),$this->para['rid'],$liveTime);
		if(!empty($result)){
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = '删除成功';
		}
		else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = '删除失败';
		}

		$this->display($jsonArray, 'json');
	}
}

new DjDelMblog(RADIO_APP_SOURCE);
?>