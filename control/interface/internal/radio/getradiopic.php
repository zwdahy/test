<?php
/**
 * Project:     电台管理后台接口
 * File:        getradiopic.php
 * 
 * 获取分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradiopic.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
include_once SERVER_ROOT . 'dagger/libs/extern.php';
class getRadioPic extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['type'] = request::post('type', 'STR');	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		

		$type=array('type'=>$this->para['type']);
		$result = $obj->getRecommendFromDB($type);
		$data = array();
		if($result) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',				
				'result' => $result
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getRadioPic(RADIO_APP_SOURCE);
?>