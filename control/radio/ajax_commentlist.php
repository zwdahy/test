<?php

/**
 * Project:     Fangtan
 * File:        ajax_commentlist.php
 * 
 * 获取评论列表
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author wangchao <wangchao@staff.sina.com.cn>
 * @package Fangtan
 * @date 2010-9-28
 * @version 1.1
 */
//ini_set('display_errors',1);
//error_reporting(E_ALL||E_NOTICE);
include_once SERVER_ROOT . "config/radioconf.php";
class CommentList extends control {
	protected function checkPara(){
	    //判断来源合法性
	    if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
	    
		//检测用户是否登录，并获取到用户信息
		$mPerson = clsFactory::create(CLASS_PATH . 'model','mPerson','service');
		$currPerson = $mPerson->currentUser(true);
		//判断是否开通微博
		if($currPerson === false){
			$this->setCError('M00003','未登录');
			return false;
		}

		$this->para['uid'] = isset($currPerson['uid'])?$currPerson['uid']:0;		
		if(empty($this->para['uid']) || !is_numeric($this->para['uid'])){
			$this->setCError('M00003','未登录');
			return false;
		}
		//参数接收
		$this->para['ownerUid'] = request::post('ownerUid','STR'); //微博所属者ID
		$this->para['resId']    = request::post('resId','STR');    //微博客ID
		
		//@test
		//$this->para['ownerUid'] = "3305260484"; //微博所属者ID
		//$this->para['resId']    = "3697389615698308";    //微博客ID
		
		//参数检测
		if(empty($this->para['resId']) || !is_string($this->para['resId'])){
			$this->setCError ('E00002','参数错误');
			return false;
		}
	    if(empty($this->para['ownerUid']) || !is_string($this->para['ownerUid'])){
	    	$this->setCError ('E00002','参数错误');
			return false;
		}
		//转换成mid
		$midarr = Check::mblogMidConvert($this->para['resId']);
		if(empty($midarr) || !is_array($midarr) || !isset($midarr['mid']) || !is_numeric($midarr['mid'])){
			$this->setCError ('E00002','参数错误');//
			return false;
		}
		$this->para['resId'] = $midarr['mid'];
		$this->para['res62Id'] = $midarr['mid62'];
	}
	
	protected function action(){
		if(2 == COMMENT_SWITCH){
            $html = $this->display(array('tpl'=>array('radio/comment_close.html'),'data'=>''),'html',true);
            $data = array(
                'code'=>'J00331',
                'data'=>$html
            );
            $this->display($data, 'json');
            return false;
        }
		
		//判断是否包含出错信息
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
	    
		//初始化M层对象
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');	    		
		
		//调用M层获取微博评论信息
		$commentlist = $mRadio->getCommentList($this->para['resId']);
		if(empty($commentlist['error_code'])){			
			$data['ownerUid'] = $this->para['ownerUid'];	//微博所属用户id
			$data['mid'] = $this->para['resId'];			//微博id
			$data['mid62'] = $this->para['res62Id'];			//微博62编码id
			$data['cuid'] = $this->para['uid'];				//当前用户id
			if(count($commentlist['comments']) > 3){
				$commentlist['comments'] = array_slice($commentlist['comments'],0,3);
				$other_comment_count = $commentlist['total_number'] - 3;
			}
			foreach($commentlist['comments'] as &$value){
				$userInfo = $mRadio->getUserInfoByUid(array($value['user']['id']));
				$value['user'] = $userInfo[$value['user']['id']];
			}
			foreach($commentlist['comments'] as &$v){
			$text = ereg_replace("<a [^>]*>|<\/a>","",$v['text']);
			$text = preg_replace('/<(img([^>]*src[^>]*title[^>]*))\/>/iU','[[[img$1/img]]]',$text);
			$text=	preg_replace("(\'|\")","abcdefg$1",$text); 
			$text = htmlspecialchars($text);
			$text=str_replace("abcdefg","\"",$text); 
			$text=str_replace(array('[[[img','/img]]]'), array('<','>'), $text);
			$v['text'] = $text;	
			}
			$data['comment'] = $mRadio->formatFeed($commentlist['comments']);
			$data['other_comment_count'] = $other_comment_count > 0 ? $other_comment_count : 0;
			$data['t_url'] = T_URL;
			//$html = $this->display(array('tpl'=>array('radio/mblogcomment.html'),'data'=>$data),'html',true);
			//处理反馈信息
			$jsonResult = array(
				'code'=>'A00006',
				'data'=>$data
				//'other_comment_count'=>$data['other_comment_count'],
				//'t_url'=>$data['t_url']
			);
		}
		else{
			$jsonResult['code'] = 'R01404'; //系统繁忙
			$jsonResult['data'] = array();
		}
		$this->display($jsonResult, 'json');
	}
}
new CommentList(RADIO_APP_SOURCE);
?>