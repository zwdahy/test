<?php
/**
 * Project:     检测微博是否有更新
 * File:        check_new_weibo.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/check_new_weibo.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class checkNewWeibo extends control {
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
		
		if(empty($this->para['city']) || empty($this->para['domain']) || empty($this->para['id'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}
	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$province_spell = $this->para['city'];
		$domain = $this->para['domain'];
		$id = $this->para['id'];	
		
		$result = $obj->getNewsfeedByCrontab($province_spell,$domain);
		
		$data = array();
		if($result['errorno'] == 1) {
			$tem_arr_weibo = $result['result']['record'];
			foreach($tem_arr_weibo as $key=>$value){
				if((int)$value['mid'] == (int)$id) {
					//有这个id的话，就记住id的key值，把它之前的信息返回
					//-------
					$flag_key = $key;
				}
			}
		
			//判断微博更新的flag
			if(0 === $flag_key){
				$status = 0;//无更新
			}else{
				$status = $flag_key -1;//有更新
			}
			
			/*
			if(isset($flag_key)){
				if($flag_key > 0){
					//返回最新的微博信息
					$need_result = array_slice($tem_arr_weibo, 0, $flag_key);
				}
			}else{
				//已经更新了好久的微博信息了，至返回这最新的50条微博
				$need_result = $tem_arr_weibo;
			}
			*/	
			$data = array(
				'status' => $status
			);		
		} else {
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
new checkNewWeibo(RADIO_APP_SOURCE);
?>