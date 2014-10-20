<?php
/**
 * Project:     最近时间发表的weibo条目及发送者信息
 * File:        statuses.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/statuses.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
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
		$this->para['city'] = request::get('city', 'STR');					//城市名称
		$this->para['domain'] = request::get('domain', 'STR');				//fm数字
		
		$this->para['id'] = request::get('id', 'STR');					//微博Id
		
		$this->para['page'] = request::get('page', 'INT');				//起始位置
		$this->para['count'] = request::get('count', 'INT');				//获取个数
		
		if(empty($this->para['page'])) $this->para['page'] = 1;
		if(empty($this->para['count']) || $this->para['count'] > 50) $this->para['count'] = 5;
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

		$result = $obj->getNewsfeedByCrontab($province_spell,$domain);
		
		
		$id = $this->para['id'];
		
		$data = array();
	
		//$id = 3360124790518407;	
		if($result['errorno'] == 1) {
			$tem_arr_weibo = $result['result']['record'];
			foreach($tem_arr_weibo as $key=>$value){
				if((int)$value['mid'] == (int)$id) {
					//有这个id的话，就记住id的key值，把它之前的信息返回
					$flag_key = $key;
				}
			}
			if(0 === $flag_key){
				$need_result = 0; //没有最新的微博信息
			}else if($flag_key > 0){
				//返回最新的微博信息
				$need_result = array_slice($tem_arr_weibo, 0, $flag_key);
				$total_number = is_array($need_result) ? count($need_result) : 0;
			}else{
				$need_result = array_slice($tem_arr_weibo, $this->para['start'], $this->para['count']);
				$total_number = is_array($result['result']['record']) ? count($result['result']['record']) : 0;
			}
		
			$data = array(
				'statuses' => isset($need_result)?$need_result:'',
				"total_number" => isset($total_number) ? $total_number :0
			);
		}else{
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => $_LANG[$result['errno']] != '' ? $_LANG[$result['errno']] : $result['errno']
			);
		}

		$this->display($data, 'json');
		return true;
		
	}
}
new getRadio(RADIO_APP_SOURCE);
?>