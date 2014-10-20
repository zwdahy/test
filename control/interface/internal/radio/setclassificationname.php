<?php
/**
 * Project:     电台管理后台接口
 * File:       setclassificationname.php
 * 
 * 编辑分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setclassificationname.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setClassificationName extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['classification_name'] = request::post('classification_name', 'STR');
		$this->para['classification_id'] = request::post('classification_id', 'STR');			// 电台ID		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$arg = array('classification_name'=>$this->para['classification_name'],
						'classification_id'=>$this->para['classification_id'],
				);
		$result = $obj->setClassification($arg);	
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
new setClassificationName(RADIO_APP_SOURCE);
?>