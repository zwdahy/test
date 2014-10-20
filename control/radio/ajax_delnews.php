<?php

/**
 * Project:     radio
 * File:        ajax_delnews.php
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
class delNews extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['newsid'] = request::post('newsid', 'INT');
		//参数检测处理
		if(empty($this->para['newsid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		//身份校验
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
		$cuserInfo = $person->currentUser();
		$cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
		if($cuid > 0){
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$admin_id = $mRadio->getAllPowerList();
			$admin_id = $admin_id['result'];
			if(!in_array($cuid,$admin_id)){
				$this->setCError('M00009', '权限错误');
				return false;
			}
		}
		else{
			$this->setCError('M00009', '权限错误');
			return false;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$newsid = $this->para['newsid'];
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取所需页新闻
		$data = array('field'=>'id','value'=>$newsid);
		$result = $mRadio->delRadioNews($data);
		if(1==$result['errorno']){
				$jsonArray['code'] = 'A00006';
				$jsonArray['data'] = '删除新闻成功';
		}
		else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = '删除新闻失败';
		}		
		
		$this->display($jsonArray, 'json');
	}	
}

new delNews(RADIO_APP_SOURCE);
?>