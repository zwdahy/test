<?php
/**
 * Project:     电台管理后台接口
 * File:        updatefeedinfo.php
 * 
 * 编辑主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setdj.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class updateFeedInfo extends control {
	protected function checkPara() {	
		$this->para['rid'] = request::get('rid', 'INT');		// 电台ID
		if(empty($this->para['rid'])){			
			return false;
		}
		return true;
	}
	protected function action() {
		if($this->hasCError()) {
			var_dump("参数错误！");
		}
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->updateFeedListByRid($this->para['rid'],RADIO_FEEDLIST_MAXPAGE);
		if($result['result'] == -1 || $result['result'] == -2){
			var_dump("update feed successfully!");
		}
		else if($result['result'] == -3){
			var_dump("update feed failed!reason:get Mblog info failed!");
		}
		else if($result['result'] == -4){
			var_dump("update feed failed!reason:get user info failed!");
		}
		else if($result['result'] == -9){
			var_dump("update feed failed!reason:set memcached failed!");
		}
	}
}
new updateFeedInfo(RADIO_APP_SOURCE);
?>