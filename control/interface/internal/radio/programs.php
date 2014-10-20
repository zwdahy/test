<?php
/**
 * Project:     获取某电台节目单
 * File:        programs.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/programs.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class Programs extends control {
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
		$this->para['city'] = request::get('city', 'STR');					//城市名称
		$this->para['domain'] = request::get('domain', 'STR');				//fm数字
		$this->para['day'] = request::get('day', 'STR');					//日期
		
		if(empty($this->para['city']) || empty($this->para['domain'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}
	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$province_spell = $this->para['city'];
		$domain = $this->para['domain'];		
	
		$rs = $obj->getRadioByDomainAndPro($domain,$province_spell);
		if(1 == (int)$rs['result']['online']){
			if ((int)$rs['result']['rid'] > 0){
				$rid = $rs['result']['rid'];
				if(empty($this->para['day'])){
					$today = getdate();
					if($today['wday'] == 0){
						$today['wday'] = 7;
					}
					$wday = $today['wday'];
				}
				else{
					$wday = $this->para['day'];
				}
				$result = $obj->getRadioProgram($rid,$wday);
			}
		}
		$data = array();
		if(2 == (int)$rs['result']['online']){
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => '此电台已下线'
			);
		}else if((int)$result['rid'] > 0 && 1 == (int)$rs['result']['online']) {
			$result['program_info'] = unserialize($result['program_info']);
			foreach($result['program_info'] as $key=>$val){
				$result['program_info'][$key]['dj_info'] = array_merge($val['dj_info']);
			}
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
new Programs(RADIO_APP_SOURCE);
?>
