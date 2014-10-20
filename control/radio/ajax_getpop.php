<?php

/**
 * Project:     radio
 * File:        ajax_getpop.php
 * 
 * 获取节目单弹出层
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetPop extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'str'));
		$this->para['type'] = request::post('type', 'int');

		//参数检测处理
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		
		$this->para['type'] = !empty($this->para['type']) ? $this->para['type'] : '1';
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}

		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = array();		
		$jsonArray['code'] = 'A00006';
		
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();
        $params = array();
        $html = array();
        
		//获取当前电台节目单
		$week = array('周一','周二','周三','周四','周五','周六','周日');
		$today = getdate();
		if($today['wday'] == 0){
			$today['wday'] = 7;
        }
		for($n=0;$n<7;$n++){
			$result[$n]['rid'] = $this->para['rid'];
			$result[$n]['wday'] = $week[$n];
			$result[$n]['today'] = ($today['wday'] == $n+1) ? true : false;
		}
		$programs = $mRadio->getProgramList($this->para['rid']);		
		foreach($programs as $k => $v){			
			$result[$v['day']-1]['program_info'] = $mRadio->getProgramInfo(unserialize($v['program_info']));
			foreach($result[$v['day']-1]['program_info'] as &$val){
				if(!empty($val['begintime']) && !empty($val['endtime'])){
					if(time() >= strtotime($val['begintime']) && time() <= strtotime($val['endtime']) && $today['wday'] == $v['day']){
						$val['now'] = true;
					}
					$val['begintime'] = date('H:i',strtotime($val['begintime']));
					$val['endtime'] = date('H:i',strtotime($val['endtime']));
				}				
			}
		}		
		
        $params['type'] = $this->para['type'];
        $params['data'] = $result;        		
				
		$jsonArray['data']['html'] = insert_radio_pop($params,$smarty);		

		$this->display($jsonArray, 'json');
	}
}

new GetPop(RADIO_APP_SOURCE);
?>