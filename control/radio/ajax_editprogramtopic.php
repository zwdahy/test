<?php

/**
 * Project:     radio
 * File:        ajax_editprogramtopic.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class EditProgramTopic extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));
		$this->para['topic'] = request::post('topic', 'STR');

		//测试数据
/*
		$this->para['rid'] = '31';		
		$this->para['begintime'] = '8:00';
		$this->para['endtime'] = '9:00';
		$this->para['topic'] = '测试话题';
*/		
		//参数检测处理
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		
		//身份校验
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
		$cuserInfo = $person->currentUser();
		$cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
		if($cuid > 0){
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$radioInfo = $mRadio->getRadioInfoByRid(array($this->para['rid']));
			$radioInfo = $radioInfo['result'][$this->para['rid']];
			$isCurrentDj = $mRadio->isCurrentDj($cuid,$this->para['rid']);
			global $RADIO_ADMIN;
			if($cuid != $radioInfo['admin_uid'] && !in_array($cuid,$RADIO_ADMIN) && $isCurrentDj == false){
				$this->setCError('M00009', '参数错误');
				return false;
			}
		}
		else{
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
		//验证名称是否合法
		if($this->para['topic'] != strip_tags(html_entity_decode($this->para['topic'])) || $mRadio->checkKeyWord($this->para['topic']) == false){
			$jsonArray['code'] = 'M01103';
			$this->display($jsonArray, 'json');
			exit;
		}
		$today = getdate();
		if($today['wday'] == 0){
			$today['wday'] = 7;
		}
		$programs = $mRadio->getRadioProgram($this->para['rid'],$today['wday']);
		$program_today = $mRadio->getProgramInfo(unserialize($programs['program_info']));
		if(!empty($program_today)){
			$topic_endtime = 0;
			foreach($program_today as $value){
				$curr_time = time();
				$begintime = strtotime($value['begintime']);
				$endtime = strtotime($value['endtime']);					
				if($curr_time >= $begintime && $curr_time <= $endtime){
					$topic_endtime = date('Y-m-d H:i:s',$endtime);
					$topic_begintime = date('Y-m-d H:i:s',$curr_time);
					break;
				}
			}			
		}
			
		if(empty($this->para['topic'])){
			$topic = array();
		}
		else{
			$topic = array('begintime' => !empty($topic_begintime) ? $topic_begintime :  $topic_begintime = date('Y-m-d H:i:s',time())
						,'endtime' => !empty($topic_endtime) ? $topic_endtime : date('Y-m-d H:i:s',time())
						,'topic' => $this->para['topic']);
		}		
		$today = getdate();
		$args = array('rid' => $this->para['rid']
					,'day' => $today['wday']
					,'topic' => serialize($topic));		
		
		$result = $mRadio->addRadioProgram($args);
		if($result['errorno'] == 1){        	
			$jsonArray['code'] = 'A00006';
		}
		else{
			$jsonArray['code'] = 'RDO002';
		}
		
		$this->display($jsonArray, 'json');
	}
}

new EditProgramTopic(RADIO_APP_SOURCE);
?>