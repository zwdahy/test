<?php
/**
 * Project:     电台管理后台接口
 * File:        gethotradio.php
 * 
 * 获取热门电台
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/gethotradio.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getHotRadio extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['rid'] = request::post('rid', 'INT');		// 电台ID
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getHotRadio();
		if(!empty($this->para['rid'])) {		// 使支持获取单个或多个主持人信息以及列表
			$tmp = $result['result'][$this->para['rid']];
			unset($result['result']);
			$result['result'][$this->para['rid']] = $tmp;
		}
		
		$data = array();
		if($result['errorno'] == 1) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
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
new getHotRadio(RADIO_APP_SOURCE);
?>