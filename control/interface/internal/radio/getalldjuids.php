<?php
/**
 * Project:    全部电台信息的接口
 * File:        getallradio.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/getalldjuids.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getAllDjUids extends control {
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
				if(!in_array($val['admin_uid'],$uids) && $val['admin_uid'] > 0){
					$uids[] = $val['admin_uid'];
				}
			}
			$rids = array_keys($radioList);
			$djinfo = $mRadio->getDjInfoByRid($rids);
			if(!empty($djinfo['result'])){
				$djinfo = $djinfo['result'];
				foreach ($djinfo as $val){
					if(!empty($val['uids'])){
						$djinfouids = explode(',',$val['uids']);
						$uids = array_merge_recursive($uids,$djinfouids);
					}						
				}							
			}
		}
		
		$data = array();
		if(!empty($uids)) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $uids
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
new getAllDjUids(RADIO_APP_SOURCE);
?>