<?php
/**
 * Project:     电台管理后台接口
 * File:        getarea.php
 * 
 * 获取分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradionotice.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getRadioNotice extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$result = $obj->getRadioNotice();
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
new getRadioNotice(RADIO_APP_SOURCE);
?>