<?php

/**
 * Project:     webcast
 * File:        ajax_collect.php
 * 
 * 收藏微博
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author 张倚弛<yichi@staff.sina.com.cn>
 * @package radio control
 * @date 2010-9-27
 * @version 1.1
 */
 
class Collect extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['mid'] 	= request::post('mid', 'str');
		//参数检测处理
		if(empty($this->para['mid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}

		//登录检测
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {
			$currPerson = $mPerson->currentUser();
			$this->para['currUserInfo'] = $currPerson;
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		$mMblog = clsFactory::create(CLASS_PATH . 'model', 'mMblog', 'service');
		$mblogid = $this->para['mid']; 
		
		$args = array(
			'uid'     => $this->para['currUserInfo']['uid'],
			'mblogid' => $mblogid,
		);
		
		$result = $mMblog->addFavMblog2($args);

		$jsonArray = array('code'=>'M00004','data'=>'');
		if($result['flag']) {
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = $result['result'];
		} else {
			$jsonArray['code'] = 'B20006';
			$jsonArray['data'] = '收藏失败';
		}
		$this->display($jsonArray, 'json');
	}
}
new Collect(RADIO_APP_SOURCE);
?>