<?php
/**
 * Project:     电台管理后台接口
 * File:        getblack.php
 * 
 * 获取分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getblack.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getPower extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		//$this->para['uid'] = request::post('uid', 'INT');
		$this->para['page'] = request::post('page', 'INT');		// 分页编号
		$this->para['pagesize'] = request::post('pagesize', 'INT');		// 每页显示多少条
		$this->para['power'] = request::post('power', 'INT');
		$this->para['url'] = request::post('url', 'STR');
		if($this->para['pagesize'] > 50) {
			$this->para['pagesize'] = 50;		// 每页最大为50条，大于50的话取50条
		}
		
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$power_url = $this->para['url'];
		if($power_url){
		$tmp = explode('/',$power_url);
		$domain = '';
		foreach($tmp as $k => $v){
			if($v == 't.sina.com.cn' || $v == 'weibo.com'){
				if($tmp[$k+1] == 'u'){
					$uid = $tmp[$k+2];
				}
				else{
					$domain = $tmp[$k+1];
				}
				break;
			}
		}
		if(empty($uid)){		
			if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
				$uid = $match[1];
			}
			else{
				$rs = $obj->getUserInfoByDomain($domain);
				if($rs['id'] > 0){
					$uid = $rs['id'];
				}
				else{
					$this->display(array('errmsg' => "用户的uid不存在"),'json');
					return true;
				}
			}
		}		
		}
		
		if(!empty($this->para['page']) && !empty($this->para['pagesize']) && empty($this->para['url'])) {
			$args = array(
				'page' => $this->para['page'],
				'pagesize' => $this->para['pagesize'],
				'power' => $this->para['power']
			);
		} else {		// 支持查询全部
			/*$args = array('uid'=>$uid,
						  'power' => $this->para['power']
				);
				$args = array(
				'page' => $this->para['page'],
				'pagesize' => $this->para['pagesize'],
				'power' => 2
			);*/
			$args = array(
				'uid'=>$uid,
				'power' => $this->para['power']
			);
		}

		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getPower($args);
		
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
new getPower(RADIO_APP_SOURCE);
?>