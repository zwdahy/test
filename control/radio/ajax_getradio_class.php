<?php

/**
 * Project:     radio
 * File:        ajax_getradio_class.php
 *
 * 获取电台分类
 * 
 * @copyright sina.com
 * @author wenda <wenda@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetRadioCut extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
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
        $radio_classification=$mRadio->getClassificationList();
        $radio_classification=$radio_classification['result'];
        foreach($radio_classification as &$v){
            unset($v['sort']);
        }
        $jsonArray['code']='A00006';		
        $jsonArray['data']=$radio_classification;
		$this->display($jsonArray, 'json');
	}

	
}
new GetRadioCut(RADIO_APP_SOURCE);
?>
