<?php

/**
 * Project:     Fangtan
 * File:        ajax_getcomment.php
 * 
 * 取得最新的三条评论
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author 刘勇刚 <yonggang@staff.sina.com.cn>
 * @package Fangtan
 * @version 1.1
 */
class DjDelComment extends control {
	protected function checkPara() {
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//检测用户是否登录，并获取到用户信息
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {
			$currPerson = $mPerson->currentUser();
			$this->para['currUserInfo'] = $currPerson;
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}
		$this->para['srcid'] = request::post('srcid', 'str');
		$this->para['cmtid'] = request::post('cmtid', 'str');
		$this->para['cmtuid'] = request::post('cmtuid', 'str');
	}

	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		
		if(!empty($this->para['srcid'])){
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');
			$args = array('cid' => $this->para['cmtid']
						,'is_encoded' => 0);
			$commentInfo = $mRadio->delComment($args);
			if(empty($commentInfo['error_code'])) {
				$data['code'] = 'A00006';
			} else {
				$data['code'] = 'M00004';
			}
		}
		else{
			$data['code'] = 'E00002'; //参数错误
		}
		if(!$data['html']){
			$data['html'] = '';
		}
		$this->display($data, 'json');
	}
}

new DjDelComment(RADIO_APP_SOURCE);
?>