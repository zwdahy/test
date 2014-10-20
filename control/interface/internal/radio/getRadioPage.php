<?php
/**
 * Project:     电台管理后台接口
 * File:        addRadioPage.php
 * 
 * 添加，修改，删除电台页面上某区域信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getRadioPage.php
 * @copyright sina.com
 * @author  wenda<wenda@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class manageRadioPage extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['type'] = request::post('type', 'INT');					//页面id 获取到哪个页面 首页为1
		$this->para['block_name'] = request::post('block_name', 'STR');		//对应的区域名称
		//$this->para['page'] = request::post('page', 'INT');		//页码
		//$this->para['rid'] = intval(request::post('rid', 'INT'));		//获取外部推荐
//		$this->para['type'] = 1;					//页面id 获取哪个页面 首页为1
//		$this->para['block_name'] = 'hot_preview_pic';		//对应的区域名称
		return true;
	}
	protected function action() {
		//参数的处理
		$this->para['page'] = !empty($this->para['page'])?$this->para['page']:1;
		if(empty($this->para['type']) || empty($this->para['block_name'])){
			$error = array('errmsg' => '参数错误');
			$this->display($error, 'json');
			return false;
		}
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getRadioPageInfoByBlockName2($this->para['block_name'],$this->para['type']);
//		$result = $obj->getRadioPageInfoByBlockName2($this->para['block_name'],$this->para['type'],$this->para['page']);
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
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
	
	
}

new manageRadioPage(RADIO_APP_SOURCE);
?>