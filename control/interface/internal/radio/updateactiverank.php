<?php
/**
 * Project:     电台管理后台接口
 * File:        updateactiverank.php
 * 
 * 更新线上活跃dj榜和活跃用户榜
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/updateactiverank.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class updateActiveRank extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//更新DJ活跃榜
		$active_dj = $obj->updateActiveDjRank(); 
		//更新用户活跃榜
		$active_user = $obj->updateActiveUserRank();
		$data = array();
		if(1==$active_user&&1==$active_dj) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => "更新在线活跃榜失败！"
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new updateActiveRank(RADIO_APP_SOURCE);
?>