<?php

/**
 * Project:     radio
 * File:        ajax_switch_area.php
 * 
 * 按照地区换台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class SwitchArea extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['pid'] = request::get('pid', 'STR');
		$this->para['from'] = request::get('from', 'STR');		
	
		//参数检测处理
		if(empty($this->para['pid']) || ($this->para['from']!=1 && $this->para['from']!=0)) {
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

		$data['pid'] = $this->para['pid'];
		$data['from'] = $this->para['from'];
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();
		$html = insert_radio_switch_area($data,$smarty);
		//处理反馈信息
		$jsonArray = array(
			'code'=>'A00006',
			'html'=>$html
		);
		
		$this->display($jsonArray, 'json');
	}
}

new SwitchArea(RADIO_APP_SOURCE);
?>