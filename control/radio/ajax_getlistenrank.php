<?php

/**
 * Project:     radio
 * File:        ajax_getlistenrank.php
 * 
 * 获取电台排行榜页收听榜
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetListenRank extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = request::post('rid', 'str');		
		//$this->para['rid'] = 435;		

		//参数检测处理
//		if(empty($this->para['pid'])) {
//			$this->setCError('M00009', '参数错误');
//			return false;
//		}		
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$rankList=$mRadio->getListenRank(20);
		foreach($rankList as &$v){
			if(!empty($this->para['rid'])&&$v['info']['rid']==$this->para['pid']){
				$v['info']['now']=1;
				continue;
			}
			$v['info']['now']=0;
		}
		unset($v);
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = $rankList;		
		$this->display($jsonArray, 'json');
	}
}

new GetListenRank(RADIO_APP_SOURCE);
?>
