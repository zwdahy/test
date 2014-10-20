<?php
/**
 * Project:     电台管理后台接口
 * File:        areas.php
 * 
 * 获取电台地区列表
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/areas.php
 * @copyright sina.com
 * @author qibing@staff.sina.com.cn
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class Areas extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$result = $obj->getAreaHasOnline();
		$data = array();
		if(count($result) > 0) {
			$data = array(
				'total_number'  => count($result),
				'area_list' => array_merge($result)
			);
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new Areas(RADIO_APP_SOURCE);
?>
