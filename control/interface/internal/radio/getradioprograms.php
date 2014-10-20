<?php
/**
 * Project:     获取某电台节目单
 * File:        getradioprograms.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/getradioprograms.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class GetRadioPrograms extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		
		$this->para['rid'] = request::get('rid', 'INT');					//电台ID
		$this->para['day'] = request::get('day', 'INT');					//日期
		
		if(empty($this->para['rid']) || 0 == (int)$this->para['rid']){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}
	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$rid = $this->para['rid'];
		if(empty($this->para['day'])){
			$today = getdate();
			if($today['wday'] == 0){
				$today['wday'] = 7;
			}
			$wday = $today['wday'];
		}else{
			$wday = $this->para['day'];
		}
		$result = $obj->getRadioProgram($rid,$wday);
		
		$data = array();
		if((int)$result['rid'] > 0) {
			$result['program_info'] = unserialize($result['program_info']);		
			$data = $result;
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
		
	}
}
new GetRadioPrograms(RADIO_APP_SOURCE);
?>