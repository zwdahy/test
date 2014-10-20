<?php

/**
 * Project:     radio
 * File:        ajax_getprogramtopic.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetProgramTopic extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));		

		//测试数据
/*
		$this->para['rid'] = '31';		
*/		
		//参数检测处理
		if(empty($this->para['rid'])) {
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
		
		$radioinfo = $mRadio->getRadioInfoByRid(array($this->para['rid']));
		$radioinfo = $radioinfo['result'][$this->para['rid']];
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = "";
		$jsonArray['dj_feed'] = "";
		$jsonArray['mids'] = array();
		$jsonArray['hide'] = true;
		
		$today = getdate();
		if($today['wday'] == 0){
			$today['wday'] = 7;
		}
		$wday = $today['wday'];			
		
		$programs = $mRadio->getRadioProgram($this->para['rid'],$wday);
		//节目话题
		$topic = array();
		$topic = unserialize($programs['topic']);
		
		if(!empty($topic)){			
			if(time() < strtotime($topic['endtime'])){					
				$jsonArray['data'] = $topic['topic'];
			}
		}
					
		//获取在线dj微博展示区
		$program_today = $mRadio->getProgramInfo(unserialize($programs['program_info']));
		if(!empty($program_today)){
			foreach($program_today as $value){
				$begintime = strtotime($value['begintime']);
				$endtime = strtotime($value['endtime']);
				if(time() >= $begintime && time() <= $endtime){
					$program_now = $value;
					$program_endtime = $endtime;
					break;
				}					
			}
		}
		if(!empty($program_endtime)){
			$djinfo = $mRadio->getDjFeed($this->para['rid'],$program_endtime);
		}
		else{
			$djinfo = array();
		}
					
		if(!empty($djinfo)){
			foreach($djinfo as $value){
				$jsonArray['mids'][] = $value['mid'];
			}				
		}
		if(!empty($program_now)){
			//登录用户身份判断
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$cuserInfo = $person->currentUser();
			$power = $cuserInfo['uid'] == $radioinfo['admin_uid'] ? 'admin' : 'visit';
			if($power == 'admin'){
				$jsonArray['hide'] = false;
			}
			$display = clsFactory::create('framework/tools/display','DisplaySmarty');
	        $smarty = $display->getSmartyObj();
	        $params = array();
	        $params['new'] = true;
			$params['rid'] = $radioinfo['rid'];
			$params['program_now'] = $program_now;
			$params['program_visible'] = true;
			
			$params['power'] = $power;
			$params['cuid'] = $cuserInfo['uid'] > 0 ? $cuserInfo['uid'] : 0;
			
			$jsonArray['dj_feed'] = insert_radio_dj_feedlist($params,$smarty);
			if(empty($jsonArray['mids']) && $cuserInfo['uid'] > 0){
				$isDj = $mRadio->isCurrentDj($cuserInfo['uid'],$this->para['rid']);
				if($isDj> 0){
					$jsonArray['hide'] = false;
				}				
			}
		}		
		
		$this->display($jsonArray, 'json');
	}
}

new GetProgramTopic(RADIO_APP_SOURCE);
?>