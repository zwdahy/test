<?php
/**
 * Project:     电台管理后台接口
 * File:        getdjrank.php
 * 
 * 获取分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getdjrank.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getDjRank extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getActiveDjRank(20);				
		$data = array();	
		if(isset($result[0])) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => count($result),
				'result' => $result
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => '暂无活跃榜'
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getDjRank(RADIO_APP_SOURCE);
?>