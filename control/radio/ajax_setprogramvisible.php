<?php

/**
 * Project:     radio
 * File:        ajax_editprogram.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class SetProgramVisible extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));
		$this->para['visible'] = request::post('visible', 'STR');				//显示/隐藏节目单，1=>隐藏，2=>显示

		//参数检测处理
		if(empty($this->para['rid']) || empty($this->para['visible'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		
		//身份校验
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
		$cuserInfo = $person->currentUser();
		$cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
		if($cuid > 0){
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$radioInfo = $mRadio->getRadioInfoByRid(array($this->para['rid']));
			$radioInfo = $radioInfo['result'][$this->para['rid']];
		//	global $RADIO_ADMIN;
		$admin_id = $mRadio->getAllPowerList();
		$admin_id = $admin_id['result'];
			if($cuid != $radioInfo['admin_uid'] && !in_array($cuid,$admin_id)){
				$this->setCError('M00009', '参数错误');
				return false;
			}
		}
		else{
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
		
		$args = array('rid' => $this->para['rid']
					,'program_visible' => $this->para['visible']);
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$result = $mRadio->setRadio($args);
		if($result['errorno'] == 1){
			$jsonArray['code'] = 'A00006';
		}
		else{
			$jsonArray['code'] = 'E00001';
		}
		
		$this->display($jsonArray, 'json');
	}
}

new SetProgramVisible(RADIO_APP_SOURCE);
?>