<?php
/**
 * Project:     电台管理后台接口
 * File:        addRadioPage.php
 * 
 * 添加，修改，删除电台页面上某区域信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getRadioPage.php
 * @copyright sina.com
 * @author  wenda<wenda@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class manageRadioPage extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['id'] = request::get('id', 'INT');		
		return true;
		
	}
	protected function action() {
		//参数的处理
		if(empty($this->para['id'])){
			$error = array('errmsg' => '参数错误');
			$this->display($error, 'json');
			return true;
		}
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getRadioPageById($this->para['id']);
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

new manageRadioPage(RADIO_APP_SOURCE);
?>