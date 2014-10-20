<?php
/**
 * Project:     电台管理后台接口
 * File:        getradio.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradio.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getRadio extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['rid'] = request::post('rid', 'STR');		//排序字段
		if(empty($this->para['rid'])){
			$this->display(array('errno'=>-4,'errmsg'=>'rid参数错误'), 'json');
			exit();
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$args = array(
			'order_field' => "",
			'order' => "",
			'search_key' => "rid",
			'search_value' => $this->para['rid'],			
			'page' => "",
			'pagesize' => ""
		);
		if(preg_match('/,/',$this->para['rid'])){
			$args['search_type'] = "IN";
		}
		else{
			$args['search_type'] = "=";
		}
		
		$result = $obj->getRadio($args);		
		$data = array();
		if($result['errorno'] == 1) {
			//添加判定
			if($args['search_type'] == "IN"){
				$data = array(
					'errno' => 1,
					'errmsg' => '成功',
					'count'  => $result['result']['count'],
					'result' => $result['result']['content']
				);
			}
			else{
				$data = array(
					'errno' => 1,
					'errmsg' => '成功',
					'count'  => count($result['result']),
					'result' => $result['result']
				);
			}
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
new getRadio(RADIO_APP_SOURCE);
?>
