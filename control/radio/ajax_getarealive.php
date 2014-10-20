<?php

/**
 * Project:     radio
 * File:        ajax_getarealive.php
 * 
 * 获取地区首页正在直播区域
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetAreaLive extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['pid'] = request::post('pid', 'str');
		$this->para['type'] = request::post('type', 'str');

		//参数检测处理
		if(empty($this->para['pid']) || ($this->para['type'] != 'hot' && $this->para['type'] != 'live')) {
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
		
		$pid = $this->para['pid'];
		$type = $this->para['type'];
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();        

        $params = array('pid' => $pid,'type' => $type);
        
		if($type == 'live'){
			$html[] = insert_radio_area_live($params,$smarty);			
			$params = array('pid' => $pid,'from' => 1);
			$html[] = insert_radio_djinfo($params,$smarty);
		}
		else{
			$html[] = insert_radio_area_live($params,$smarty);	
		}
		
				
		$jsonArray['data']['html'] = $html;

		$this->display($jsonArray, 'json');
	}
}

new GetAreaLive(RADIO_APP_SOURCE);
?>