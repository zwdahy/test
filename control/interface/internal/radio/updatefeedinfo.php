<?php
/**
 * Project:     电台管理后台接口
 * File:        updatefeedinfo.php
 * 
 * 编辑主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setdj.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class updateFeedInfo extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['rid'] = request::post('rid', 'INT');		// 电台ID
		if(empty($this->para['rid'])){			
			return false;
		}
		return true;
	}
	protected function action() {
		if($this->hasCError()) {
			$data = array('errno' => -4,
						'errmsg' => "参数错误！");
			$this->display($data, 'json');
			return true;
		}
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->updateFeedListByRid($this->para['rid'],RADIO_FEEDLIST_MAXPAGE);
		$data = array();
		if($result['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result']			
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => "更新feed失败！"
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new updateFeedInfo(RADIO_APP_SOURCE);
?>