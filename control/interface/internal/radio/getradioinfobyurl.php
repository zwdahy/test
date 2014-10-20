<?php
/**
 * Project:     电台管理后台接口
 * File:        getradioinfobyurl.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradioinfobyurl.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
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
		$this->para['channel'] = request::get('channel', 'STR');		//排序字段
		if(empty($this->para['channel'])){
			$this->display(array('errno'=>-4,'errmsg'=>'channel参数错误'), 'json');
			exit();
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$temArr = explode(',',$this->para['channel']);
		foreach ($temArr as $value){
			$temArTwo[] = explode('_',$value);
		}
		
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
				$newRadioArr[] = $rs['result']['content'][0];
			}
		}
		
		$data = array();
		if($result['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $newRadioArr,
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
new getRadio(RADIO_APP_SOURCE);
?>