<?php
/**
 * Project:     电台管理后台接口
 * File:        delclassification.php
 * 
 * 删除黑名单信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/delclassification.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class delClassification extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['classification_id'] = request::post('classification_id', 'INT');
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');	
		$result = $obj->delRadioClassification($this->para['classification_id']);
		$data = array();
		if($result['errorno'] == 1) {
			$tmp = array('classification_id'=>$this->para['classification_id']);
			$obj->setRadioClassification($tmp);
			unset($data0);
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => $result['count'],
				'result' => $result['result']
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
new delClassification(RADIO_APP_SOURCE);
?>