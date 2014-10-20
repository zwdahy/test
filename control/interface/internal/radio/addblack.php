<?php
/**
 * Project:     电台管理后台接口
 * File:        addblack.php
 * 
 * 添加分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/addblack.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class addBlack extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['url'] = request::post('url', 'STR');		// 微博url
		$this->para['upuid'] = request::post('upuid', 'STR');		// 更新人UID
		// $this->para['uptime'] = request::post('uptime', 'STR');		// 更新时间
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//需要根据url得到weibo用户的uid,然后入库
		$black_url = $this->para['url'];
		$tmp = explode('/',$black_url);
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
		
		//通过$uid 查询是否已经在黑名单中了
		$args = array('uid' => $uid);
		$res = $obj->getBlack($args);
	
		if($res['result'][0]['uid'] > 0) {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => '用户已经在黑名单中，不需重复添加'
			);
		}else{
			$args = array(
				'uid' => $uid,			
				'url' => $this->para['url'],
				'upuid' => $this->para['upuid'],
				// 'uptime' => $this->para['uptime']
			);
			$result = $obj->addBlack($args);
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
		}

		
		$this->display($data, 'json');
		return true;
	}
}
new addBlack(RADIO_APP_SOURCE);
?>