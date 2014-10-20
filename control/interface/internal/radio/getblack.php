<?php
/**
 * Project:     电台管理后台接口
 * File:        getblack.php
 * 
 * 获取分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getblack.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getBlack extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['uid'] = request::post('uid', 'INT');
		$this->para['page'] = request::post('page', 'INT');		// 分页编号
		$this->para['pagesize'] = request::post('pagesize', 'INT');		// 每页显示多少条
		if($this->para['pagesize'] > 50) {
			$this->para['pagesize'] = 50;		// 每页最大为50条，大于50的话取50条
		}
		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		if(!empty($this->para['page']) && !empty($this->para['pagesize'])) {
			$args = array(
				'page' => $this->para['page'],
				'pagesize' => $this->para['pagesize']
			);
		} else {		// 支持查询全部
			$args = array("uid"=>$this->para['uid']);
		}

		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getBlack($args);
		
		$data = array();
		if($result['errorno'] == 1) {
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
new getBlack(RADIO_APP_SOURCE);
?>