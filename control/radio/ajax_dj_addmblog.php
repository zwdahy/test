<?php

/**
 * Project:     radio
 * File:        ajax_dj_addmblog.php
 * 
 * 在线dj通过输入微博链接地址添加微博到在线dj微博展示区
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class DjAddMblog extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		$this->para['url'] = request::post('url', 'STR');
		$this->para['rid'] = intval(request::post('rid', 'STR'));		
		//$this->para['url'] = "http://weibo.com/1890926607/B3XGbt7xL";
//		$this->para['rid'] = 10;
//		$this->para['url'] = "http://weibo.com/3970089458/B3BMdgpDn";
		//参数检测处理
		if(empty($this->para['url'])) {
			$this->setCError('M01127', '请输入正确的微博地址');
			return false;
		}
		$tmp = explode('/',$this->para['url']);
		if(!is_numeric($tmp[3])||$tmp[0]!='http:'||$tmp[2]!='weibo.com'){
			$this->setCError('M01127', '请输入正确的微博地址');
			return false;
		}
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		//用户验证是否登录
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {			
			$currUid = $mPerson->getCurrentUserUid();
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$isCurrentDj = $mRadio->isCurrentDj($currUid,$this->para['rid']);
		if($isCurrentDj == false){
			$this->setCError('A00014','非当前dj');
			return false;
		}
		if(!preg_match('/^(http:\/\/){0,1}weibo.com\/.+\/.+$/',$this->para['url'])){
			$this->setCError ('MR0047','参数错误');//
			return false;
		}
		//转换成mid
		//$tmp = explode('/',$this->para['url']);
		//$mid62 = $tmp[count($tmp)-1];
		$tmp = parse_url($this->para['url']);
		$tmp = $tmp['path'];
		$tmp = explode('/',$tmp);
		$mid62 = $tmp[2];
//		print '<pre>';
//		print_r($mid62);
//		exit;
		$midarr = Check::mblogMidConvert($mid62);		
//		print '<pre>';
//		print_r($midarr);
//		exit;
		if(empty($midarr) || !is_array($midarr) || !isset($midarr['mid']) || !is_numeric($midarr['mid'])){
			$this->setCError ('E00002','参数错误');//
			return false;
		}
		$this->para['mid'] = $midarr['mid'];
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mid = $this->para['mid'];
//		print '<pre>';
//		print_r($mid);
//		exit;
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		//获取当天节目单确定mc生命周期
		//获取当天的节目单用来确定mc生命周期
		$today = date('N');
		$programList = $mRadio->getRadioProgram2($this->para['rid'],$today);
		$time = time();
		foreach($programList as &$v){
			if( strtotime( $v['begintime'] )<=$time&&strtotime( $v['endtime'] )>$time ){
				$liveTime = strtotime($v['endtime'])-strtotime($v['begintime']);
					break;//找到一个就ok啦
			}
		}
		unset($v);
		//print_r($liveTime);
		//exit;
//		print '<pre>';
//		print_r($programList);
//		exit;

		//dRadioProgram 中的方法 不建议使用
//		$today = getdate();
//		$today['wday'] = $today['wday'] == 0 ? 7 : $today['wday']; 
//		$programs = $mRadio->getRadioProgram($this->para['rid'],$today['wday']);
//		$program_today = $mRadio->getProgramInfo(unserialize($programs['program_info']));
//		if(!empty($program_today)){
//			foreach($program_today as $value){
//				$begintime = strtotime($value['begintime']);
//				$endtime = strtotime($value['endtime']);
//				if(time() >= $begintime && time() <= $endtime){					
//					break;
//				}
//			}
//		}
		$result = $mRadio->addDjFeed(array($mid),$this->para['rid'],$liveTime);
		if($result['errorno']==1&&$result['result'] ==true){
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = '添加成功';
		}
		else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = $result;
		}

		$this->display($jsonArray, 'json');
	}
}

new DjAddMblog(RADIO_APP_SOURCE);
?>