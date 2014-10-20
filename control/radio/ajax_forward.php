<?php

/**
 * Project:     webcast
 * File:        ajax_forward.php
 * 
 * 转发微博
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author wangchao <wangchao@staff.sina.com.cn>
 * @package webcast
 * @date 2010-9-27
 * @version 1.1
 */
include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class Forward extends control {
protected function checkPara(){
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
	    //检测用户是否登录，并获取到用户信息
		$mPerson = clsFactory::create(CLASS_PATH . 'model','mPerson','service');
		$currPerson = $mPerson->currentUser(true);
				error_log(strip_tags(print_r($this->para['mid'], true))."\n", 3, "/tmp/err.log");

		//判断是否开通微博
		if($currPerson === false){
			$this->setCError('M00003','未登录');
			return false;
		}
		$this->para['currUserInfo'] = $currPerson;
		
		$this->para['uid'] = isset($currPerson['uid'])?$currPerson['uid']:0;
		if(empty($this->para['uid']) || !is_numeric($this->para['uid'])){
			$this->setCError('M00003','未登录');
			return false;
		}
	    //获取参数
		$this->para['mid']    = request::post('mid','STR');      //微博客ID
		$this->para['reason'] = trim(request::post('reason','STR'));   //转发内容
		$this->para['isRoot'] = request::post('isRoot','STR');   //同时评论给原文作者
		$this->para['isLast'] = request::post('isLast','STR');   //同时评论给转发作者
		$this->para['rid']    = request::post('rid','STR');   //电台id

		error_log(strip_tags(print_r('ads', true))."\n", 3, "/tmp/err.log");
		//检测参数的合法性
		if(empty($this->para['mid'])){
			$this->setCError('E00002', '参数错误');
			return false;
		}
		//如果未填写转发理由，默认加上转发微博。
		if(is_null($this->para['reason']) || strlen($this->para['reason'])<=0){
			$this->para['reason'] = '转发微博。';
		}
	}
	
	protected function action(){
		
		//判断是否包含信息
		if($this->hasCError()){
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']),'json');
			return false;
		}
				
		//调用微博转发方法
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');
		$strReason = $this->para['reason'];
		if($strReason == ''){
			$strReason = '转发微薄';
		}				
		
		$is_comment = 0;
		if(!empty($this->para['isRoot'])){
			$is_comment = 2;
		}
		if(!empty($this->para['isLast'])){
			$is_comment = 1;
		}
		if(!empty($this->para['isRoot']) && !empty($this->para['isLast'])){
			$is_comment = 3;
		}
		
		if(2 == FORWARD_SWITCH){
			//强制评论的参数为0，不让评论
			$is_comment = 0;
		}

		
		$annotations = $mRadio->getRadioAnnotations($this->para['rid']);
		$mid = Check::mblogMidConvert($this->para['mid']);
		$repostArr = array('status' => $strReason
						,'id' => $mid['mid']
						,'is_comment' => $is_comment
						,'annotations' => $annotations
						,'is_encoded' => 0
					);
		
		//转发微博

		$result = $mRadio->repostMblog($repostArr);
		//$result = array('mblogid' => 'zF0vtAtMTW'); 
		if(empty($result['error_code'])){
			$data['code'] = 'A00006';
//			$data['data']['html'] = "";			
//  						
//			$contentArr = $mRadio->formatFeed(array($result));
//			if(!empty($contentArr)){
//				$display = clsFactory::create('framework/tools/display','DisplaySmarty');
//	            $smarty = $display->getSmartyObj();
//				$params = array();
//				$params['data'] = $contentArr;
				//判断是否在线dj
//				$program_endtime = $mRadio->isCurrentDj($this->para['currUserInfo']['uid'],$this->para['rid']);
//				if($program_endtime !== false){				
//					$mRadio->addDjFeed($contentArr[0],$this->para['rid'],$program_endtime);
//				}
//				$params['currUserInfo'] = $this->para['currUserInfo'];
//				$html = insert_radio_feedlist($params, $smarty);
//				$data['data']['html'] = $html;
//			}			
		}
		elseif($result['error_code'] == '20101'){
			$data['code'] = 'M01160';
			$data['data'] = '微博不存在！';
		}
		else{			
			if($GLOBALS ['SUB_ERROR_NO'] != false){
				$data['code'] = $GLOBALS ['SUB_ERROR_NO'];
			}else{
				$data['code'] = 'R01404';
			}
			$data['data'] = '转发失败';			
		}
		$this->display($data, 'json');
	}
}

new Forward(RADIO_APP_SOURCE);
?>