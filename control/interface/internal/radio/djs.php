<?php
/**
 * Project:     某电台的所有DJ或者在线的DJ信息
 * File:        djs_by_radio.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/djs.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class Djs extends control {
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
		$this->para['online'] = request::get('online', 'STR');				//在线DJ的参数条件
		
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
			$rid = (int)$rs['result']['rid'];
			
			//查询在线DJ
			if (1 == $this->para['online']){
				//获取当前电台播放的节目中的在线DJ
				$program_today = array();
				$dj_uids = array();
				$today = getdate();
				if($today['wday'] == 0){
					$today['wday'] = 7;
				}
				$programs = $obj->getRadioProgram($rid,$today['wday']);
				$program_today = $obj->getProgramInfo(unserialize($programs['program_info']));
			
				if(!empty($program_today)){
					foreach($program_today as $value){
						$begintime = strtotime($value['begintime']);
						$endtime = strtotime($value['endtime']);
						if(time() >= $begintime && time() <= $endtime){
							$program_now = $value;
							$program_now['topic'] = $programs['topic'];
							foreach($value['dj_info'] as $val){
								if(!in_array($val['uid'],$dj_uids)){
									$dj_uids[] = $val['uid'];
								}
							}
						}
					}
				}
				
			}else{
				$result = $obj->getDjDetail($rid, $cuid);
				foreach($result['result']['userinfo'] as $dj_val){
					$dj_uids[] = $dj_val['uid'];
				}				
			}
			
			//调用批量用户信息的API接口
			$uids = implode(',',$dj_uids);
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$djList = $person->getUsersByShowBatch($uids);
			
			$djNeedList = $djList['result']['users'];			

		}		
		
		$data = array();
		if($rs['errorno'] == 1) {			
			$data = isset($djNeedList)?$djNeedList:'';	
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$rs['errorno']] != '' ? $_LANG[$rs['errorno']] : $rs['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
		
	}
}
new Djs(RADIO_APP_SOURCE);
?>