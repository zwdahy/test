<?php

/**
 * Project:     radio
 * File:        ajax_getuserinfobyUrl.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetUserInfoByUrl extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['url'] = request::post('url', 'STR');				

		//参数检测处理
		if(empty($this->para['url'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}				
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//判断官方微博url是否存在
		if(!empty($this->para['url'])){			
			$tmp = explode('/',$this->para['url']);
			foreach($tmp as $k => $v){
				if($v == 't.sina.com.cn' || $v == 'weibo.com'){
					if($tmp[$k+1] == 'u'){
						$uid = trim($tmp[$k+2]);
					}
					else{
						$domain = $tmp[$k+1];
					}
					break;
				}
			}
			if(empty($uid)){
				$domain = trim($domain);
				if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
					$uid = $match[1];
				}
				else{
					$uid_result = $mRadio->getUserInfoByDomain($domain);
					if($uid_result['id'] > 0){					
						$uid = $uid_result['id'];
					}
					else{
						$jsonArray['code'] = 'E00001';
					}
				}				
			}
		}
		$aMinfo = $mRadio->getUserInfoByUid(array($uid));
		if(!empty($aMinfo) && $jsonArray['code'] != 'E00001'){
			$aMinfo = $aMinfo[$uid];
			$name = $aMinfo['name'];
			$uid = $aMinfo['id'];
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = array('uid' => $uid,'name'=>$name,'screen_name'=>$name);
		}
		else{
			$jsonArray['code'] = 'E00001';
		}
		
		$this->display($jsonArray, 'json');
	}
}

new GetUserInfoByUrl(RADIO_APP_SOURCE);
?>