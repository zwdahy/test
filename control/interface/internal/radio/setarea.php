<?php
/**
 * Project:     电台管理后台接口
 * File:        setradio.php
 * 
 * 编辑电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setradio.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setArea extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['infos'] = request::post('infos', 'STR');		// 电台ID		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$temp1 = explode(',',$this->para['infos']);
		$args = array();
		foreach ($temp1 as $k => $v){
			$temp2 = explode('|',$v);
			$args[$k]['sort'] = $temp2[0];
			$args[$k]['province_id'] = $temp2[1];
		}
		$result['errorno'] = 1;
		$count = count($args);
		foreach ($args as $k => $v){
			if($k != $count-1){
				$rs = $obj->setArea($v,false);
			}
			else{
				$rs = $obj->setArea($v);
			}
			if($rs['errorno'] != 1){
				$result['errorno'] = $rs['errorno'];
			}
		}		
		$data = array();
		if($result['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result'],
			);
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
new setArea(RADIO_APP_SOURCE);
?>