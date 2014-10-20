<?php
/**
 * Project:     电台管理后台接口
 * File:        delpower.php
 * 
 * 删除黑名单信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/delpower.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class delPower extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['uid'] = request::post('uid', 'INT');
		$this->para['power'] = request::post('power', 'INT');
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$args = array(
			'uid' => $this->para['uid'],
			'power' => $this->para['power']
		);
		$result = $obj->delPower($args);
		$data = array();
		if($result['errorno'] == 1) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => $result['count'],
				'result' => $result['result']
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
new delPower(RADIO_APP_SOURCE);
?>