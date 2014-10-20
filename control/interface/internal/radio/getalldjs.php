<?php
/**
 * 
 * 获取所有的DJ统计信息
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/getalldjs.php
 * @copyright sina.com
 * @author qibing@staff.sina.com.cn
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getAllDjs extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}	
		return true;
	}
	protected function action() {
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$radioList = $mRadio->getAllOnlineRadio();
		$uids = array();
		if(!empty($radioList['result'])){
			$radioList = $radioList['result'];
			foreach($radioList as $key => $val){
				if(isset($val['rid'])){
					$rids[] = $val['rid'];					
				}
			}		
			$djinfo = $mRadio->getAllDjUids($rids);
		}
		
		$data = array();
		if(!empty($djinfo)) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $djinfo
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => '失败'
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getAllDjs(RADIO_APP_SOURCE);
?>