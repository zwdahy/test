<?php
/**
 * Project:     电台管理后台接口
 * File:        radios_by_pid.php
 * 
 * 通过province_id获取某城市电台列表
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_by_pid.php
 * @copyright sina.com
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radiostream.php';
class showByPid extends control {
	protected function checkPara() {
		$this->para['province_id'] = request::get('province_id', 'STR');
		
		if((int)$this->para['province_id'] <= 0){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'errno'=>-4,'errmsg'=>'province_id参数错误'), 'json');
			exit();
		}
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
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$aRadioInfo = $obj->getRadioInfoByPid(array($this->para['province_id']));
		//过滤掉  下线的电台
		if(isset($aRadioInfo['result'])){
			foreach($aRadioInfo['result'][$this->para['province_id']] as $key=>$val){
				if(2 == (int)$val['online']){
					unset($aRadioInfo['result'][$this->para['province_id']][$key]);
				}
			}		
		}
		
		$data = array();
		if($aRadioInfo['errorno'] == 1) {
			global $RADIO_STREAM;
			foreach($aRadioInfo['result'][$this->para['province_id']] as $key=>$val){
				$rid = $val['rid'];
				$aRadioInfo['result'][$this->para['province_id']][$key]['epg_id'] = $RADIO_STREAM[$rid]['epg_id'];
				$aRadioInfo['result'][$this->para['province_id']][$key]['radio_fm'] = $RADIO_STREAM[$rid]['radio_fm'];
				$aRadioInfo['result'][$this->para['province_id']][$key]['http'] = $RADIO_STREAM[$rid]['http'];
				$aRadioInfo['result'][$this->para['province_id']][$key]['mu'] = $RADIO_STREAM[$rid]['mu'];
			}
			$tem_arr = $aRadioInfo['result'][$this->para['province_id']];


			$aRadioInfo = array_merge($tem_arr);	
			$data = array(
				'radios' => $aRadioInfo,
				"total_number" => count($aRadioInfo)
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
new showByPid(RADIO_APP_SOURCE);
?>
