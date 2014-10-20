<?php
/**
 * Project:     获取电台服务器时间
 * File:        gettime.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/gettime.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getTime extends control {
	protected function checkPara() {
		return true;
	}
	protected function action() {
		$data = array();
		$current_time = time();
		if($current_time){
			$data = array(
				'time' => $current_time
			);
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => '获取时间失败'
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getTime(RADIO_APP_SOURCE);
?>
