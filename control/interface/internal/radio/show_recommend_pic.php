<?php
/**
 * Project:     电台管理后台接口
 * File:        show_recommend_pic.php
 * 
 * 获取无线端电台推荐图
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_recommend_pic.php
 * @copyright sina.com
 * @author  张旭<zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class showRecommendPic extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		return true;
	}
	protected function action() {
	
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$result = $obj->getAllRecommend();
		
		$data = array();
		if($result['errorno']==1){
				$data = array(
					'errno' => 1,
					'errmsg' => '成功',
					'count'  => count($result['result']),
					'result' => $result['result']
				);
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'errno' => $result['errorno'],
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new showRecommendPic(RADIO_APP_SOURCE);
?>
