<?php
/**
 * Project:     电台管理后台接口
 * File:        radios_by_city.php
 * 
 * 获取某城市电台列表
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/radios_by_city.php
 * @copyright sina.com
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioareaspell.php';
class getRadioInfo extends control {
	protected function checkPara() {
		$this->para['city'] = request::get('city', 'STR');
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
	    if(empty($this->para['city'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}	
		return true;
	}
	protected function action() {
		
		global $CONF_PROVINCE_SPELL;
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$flipped = array_flip($CONF_PROVINCE_SPELL);
		$city = $this->para['city'];
		$aRadioInfo = $obj->getRadioInfoByPid(array($flipped[$city]));
		foreach($aRadioInfo['result'][$flipped[$city]] as $key=>$val){
			if(2 == (int)$val['online']){
				unset($aRadioInfo['result'][$flipped[$city]][$key]);
			}
		}
		$data = array();
		if(count(array_filter($aRadioInfo['result'])) > 0 && $aRadioInfo['errorno'] == 1) {			
			$data = array(
				'radios' => array_merge($aRadioInfo['result'][$flipped[$city]]),
				"total_number" => count($aRadioInfo['result'][$flipped[$city]])
			);
		}else if(0 == count(array_filter($aRadioInfo['result']))){
			global $_LANG;
            $data = array(
                'request' => $_SERVER['SCRIPT_URI'],
                'error_code' => -9,
                'error' => '电台不存在，请核实输入参数'
            );
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$aRadioInfo['errorno']] != '' ? $_LANG[$aRadioInfo['errorno']] : $aRadioInfo['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getRadioInfo(RADIO_APP_SOURCE);
?>
