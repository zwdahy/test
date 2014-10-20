<?php
/**
 * Project:     电台管理后台接口
 * File:        getpicinfo.php
 * 
 * 获取轮播图片的信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getpicinfo.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getPicInfo extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['pic_id'] = request::post('pic_id', 'INT');		// 电台ID
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if(!empty($this->para['pic_id'])) {		// 使支持获取单个或多个主持人信息以及列表
			$args = array(
				'pic_id' => $this->para['pic_id']
			);
		} else {		// 支持查询全部
			$args = array();
		}
		
		$result = $obj->getPicInfo($args);
		
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
new getPicInfo(RADIO_APP_SOURCE);
?>