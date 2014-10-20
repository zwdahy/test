<?php
/**
 * Project:     电台管理后台接口 -- open API
 * File:        show_batch.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_batch.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
include_once SERVER_ROOT . 'config/radiostream.php';
class getRadio extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			//log
            $ip = Check::getIp();  
            $objLog = clsFactory::create ('framework/tools/log/', 'ftLogs', 'service' );
            $objLog->switchs (1); //1 开    0 关闭
            $objLog->write ('radio', array('ip'=>$ip,'ip_url'=>$_SERVER['REQUEST_URI']), 'radio_data_open_api_error');
	
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/
		$this->para['channels'] = request::get('channels', 'STR');		//排序字段
		if(empty($this->para['channels'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$temArr = explode(',',$this->para['channels']);
		foreach ($temArr as $value){
			$temArTwo[] = explode('_',$value);
		}
		
		global $RADIO_STREAM;
		$newRadioArr = array();
		$result['errorno'] = 1;
		foreach ($temArTwo as $val){
			$domain = $val[1];
			$province_spell = $val[0];
			$args = array(
				'search_key' => "domain&province_spell",
				'search_value' => $domain.'&'.$province_spell,
				'search_type' => "=&="
			);
			$rs = $obj->getRadio($args);

			if($rs['errorno'] != 1){
				$result['errorno'] = $rs['errorno'];
			}else{
				//radio存在，写入新的数组
				$rid = $rs['result']['content'][0]['rid'];
				$rs['result']['content'][0]['http'] = $RADIO_STREAM[$rid]['http'];
				$rs['result']['content'][0]['mu'] = $RADIO_STREAM[$rid]['mu'];
				$newRadioArr[] = $rs['result']['content'][0];
			}
		}
		
		$data = array();
		if($result['errorno'] == 1) {			
			$data = $newRadioArr;
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$newRadioArr['errorno']] != '' ? $_LANG[$newRadioArr['errorno']] : $newRadioArr['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getRadio(RADIO_APP_SOURCE);
?>
