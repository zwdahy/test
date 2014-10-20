<?php
/**
 * Project:     某电台正在收听用户列表
 * File:        listeners.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/listeners.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class Listeners extends control {
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
		$this->para['page'] = request::get('page', 'INT');				//起始位置
		$this->para['count'] = request::get('count', 'INT');				//获取个数
		
		if(empty($this->para['page'])) $this->para['page'] = 1;
		if(empty($this->para['count']) || $this->para['count'] > 30) $this->para['count'] = 5;
		//计算起始位置，用于数组截取
		$this->para['start'] = ($this->para['page'] - 1) * $this->para['count'];
		
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
		
		if ((int)$rs['result']['rid'] > 0){
			$cuid = RADIO_ADMIN_UID;
			$rid = $rs['result']['rid'];		
			$result = $obj->getAllListenersByMc($rid);
		
			$tem_uid_arr = array();
			foreach($result['result'] as $key=>$value){
				$tem_uid_arr[] = $value['uid'];	
			}
			
			//调用批量用户信息的API接口
			$uids = implode(',',$tem_uid_arr);
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$userList = $person->getUsersByShowBatch($uids);
			
			$userNeedList = $userList['result']['users'];	
		}
				
		if(count($userNeedList) > 5){
			$userNeedList = array_slice($userNeedList, $this->para['start'], $this->para['count']);
		}
		
		$data = array();
		if($userList['flag'] == 1) {
			$data = array(
					'statuses' => isset($userNeedList)? $userNeedList : '',
					"total_number" => isset($userList['result']['users'])?count($userList['result']['users']):0
				);
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
new Listeners(RADIO_APP_SOURCE);
?>