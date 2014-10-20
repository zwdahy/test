<?php
/**
 * Project:     电台管理后台接口
 * File:        getminfobyuid.php
 * 
 * 获取主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getminfobyuid.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getMinfoByUid extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['uid'] = request::post('uid', 'STR');		// 用户id	
		return true;		
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');

		$uids = explode(',',$this->para['uid']);
		$result = $obj->getUserInfoByUid($uids);
		
		foreach($uids as $val){
			$result[$val]['icon'] = $result[$val]['profile_image_url'];
		}
		$data = array();
		if($result !== false) {
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
new getMinfoByUid(RADIO_APP_SOURCE);
?>