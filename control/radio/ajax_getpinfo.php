<?php

/**
 * Project:     radio
 * File:        ajax_getpinfo.php
 * 
 * 电台结果页节目单区域
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetPinfo extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'str'));
		$this->para['pname'] = urldecode(request::post('pname', 'str'));

		//参数检测处理
		if(empty($this->para['rid'])) {
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

		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$result = array();		
		$jsonArray['code'] = 'A00006';
				
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();
        $params = array('rid' => $this->para['rid'],'pname' => $this->para['pname']);
		$html = insert_radio_program($params,$smarty);		
				
		$jsonArray['data']['html'] = $html;

		$this->display($jsonArray, 'json');
	}
}

new GetPinfo(RADIO_APP_SOURCE);
?>