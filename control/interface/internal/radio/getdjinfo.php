<?php
/**
 * Project:     电台管理后台接口
 * File:        getdj.php
 * 
 * 获取主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getdjinfo.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getDjInfo extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['rid'] = request::post('rid', 'INT');		// 电台ID
		if($this->para['rid'] > 0){
			return true;
		}		
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$result = $obj->getDjInfoByRid(array($this->para['rid']));
		$data = array();
		if($result['errorno'] == 1) {
			$tmp[0] = $result['result'][$this->para['rid']]['djinfo'];			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => count($tmp),
				'result' => $tmp
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
new getDjInfo(RADIO_APP_SOURCE);
?>