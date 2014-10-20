<?php

/**
 * Project:     radio
 * File:        ajax_cancelcollect.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author <qibing@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class CancelCollect extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));
		
		//登录检测
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {
			$currPerson = $mPerson->currentUser();
			$this->para['currUserInfo'] = $currPerson;
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}

		//参数检测处理
		if(empty($this->para['rid']) || !$this->para['currUserInfo']['uid']) {
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
	
		$uid = intval($this->para['currUserInfo']['uid']);
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$collection = $mRadio->getRadioCollection(array($uid));
		$rids_array = $collection[$uid]['rids'];
	
		//判断要取消收藏的rid是否在以前的收藏列表中
		foreach($rids_array as $key=>$value){
			if($this->para['rid'] == $value['rid']){
				$rid_flag =  $value['rid'];
			}
		}
		if(isset($rid_flag)){
			//把rid从列表中去除，然后在update收藏列表
			unset($rids_array[$rid_flag]);
	
			$rids = serialize($rids_array);
			$args = array('uid' => $uid,'rids' => $rids);
			$result = $mRadio->updateRadioCollection($args);		
			if($result['errorno'] == 1){        	
				$jsonArray['code'] = 'A00006';
			}else{
				$jsonArray['code'] = 'RDO004';
			}
		}else{
			//返回rid已经取消了
			$jsonArray['code'] = 'RDO004';
		}
		
		$this->display($jsonArray, 'json');
	}
}

new CancelCollect(RADIO_APP_SOURCE);
?>