<?php

/**
 * Project:     webcast
 * File:        ajax_comment.php
 * 
 * 评论
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author 张倚弛 <yichi@staff.sina.com.cn>
 * @package radio control
 * @version 1.1
 */
include_once SERVER_ROOT . "dagger/libs/extern.php";
class Comment extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//登录检测
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {
			$currPerson = $mPerson->currentUser();
			$this->para['currUserInfo'] = $currPerson;
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}
		//获取参数
		$this->para['content']	  = request::post('content','STR');//评论内容
		$this->para['cid']	      = request::post('cid','STR');  //评论ID 传值则表示对该评论进行回复 不传则表示评论微博
		$this->para['ownerUid']	  = request::post('ownerUid','STR');  //资源所有者id
		$this->para['resourceId'] = request::post('resourceId','STR');//微博id
		$this->para['replyUid']	  = request::post('replyUid','STR');  //所要评论的评论所属者UID
		$this->para['forward']    = request::post('forward','STR');   //判断是否同时转发一条微博
		//$this->para['ccontent']   = request::post('ccontent','STR');  //回复转发
		$this->para['rid'] = intval(request::post('rid','STR'));			//电台id

		if(is_null($this->para['content']) || strlen($this->para['content'])<=0){
			$this->setCError('M07001', '参你还没有填写内容，请填写后提交数错误');
			return false;
		}
	    if(!empty($this->para['ownerUid']) && !is_numeric($this->para['ownerUid'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}
	    if(!empty($this->para['resourceId']) && !is_numeric($this->para['resourceId'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}		
	}
	protected function action() {
		if($this->hasCError()){
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']),'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');				
		if($this->para['cid'] > 0){
			//对某条评论进行回复
			$cmArr = array('comment' => $this->para['content']
							,'cid' => $this->para['cid']
							,'without_mention' => 1
							,'id' => $this->para['resourceId']//微博id
							,'is_encode' => 0);//返回结果不转义
			$result = $mRadio->replyComment($cmArr); //成功返回评论信息
			$res['replay']=$result;
		}
		else{
			//对某条微博进行评论
			$cmArr = array('comment' => $this->para['content']
							,'id' => $this->para['resourceId']
							,'is_encode' => 0);
			$result = $mRadio->addComment($cmArr); //成功返回评论信息
			$res['create']=$result;
		}
		//判断是否在线dj
		$is_dj = $mRadio->isCurrentDj($this->para['currUid']['uid'],$this->para['rid']);
		if($is_dj !== false){
			$is_dj = 1;
		}else{
			$is_dj = 0;
		}
		//记录 评论日志
		$args = array(
						'time'	=>	date("Y-m-d H:i:s", time()), 
						'serviceip'	=>	$_SERVER['SERVER_ADDR'],
						'typeid'	=>	RADIO_USER_ADDCOMMENT,
						'clientip'	=>	check::getIp(),
						'cuid'    =>	$this->para['currUserInfo']['uid'],
						'source'    =>	RADIO_SOURCE_APP_ID,
						'radioid'  =>	$this->para['rid']
					);
		$args['extra'] = "from=>0,mid=>".$this->para['resourceId'].",is_dj=>".$is_dj;
		$mRadio->writeUserActionLog($args);
		//BaseModelCommon::debug($result['error_code'],'result');
		if(empty($result['error_code'])){
			$comment = $result;
			if($this->para['forward']==1 || $this->para['forward']==true){//是否转发
				$repost_content = $this->para['content'];
				if(!empty($comment['reply_comment'])){
					$repost_content .= "//@".$comment['reply_comment']['user']['name'].":".$comment['reply_comment']['text'];
				}
				//转发微博
				$annotations = $mRadio->getRadioAnnotations($this->para['rid']);
				$repostArr = array('status' => $repost_content
								,'id' => $this->para['resourceId']
								,'is_comment' => 0
								,'annotations' => $annotations
								,'is_encoded' => 0
							);
				
				//转发微博
				$result = $mRadio->repostMblog($repostArr);
				$mid = $result['mid'];
				$res['repost']=$result;
				if(empty($result['error_code'])){
					//记录用户行为记录
					$args = array(
						'time'	=>	date("Y-m-d H:i:s", time()), 
						'serviceip'	=>	$_SERVER['SERVER_ADDR'],
						'typeid'	=>	RADIO_USER_ADDMBLOG,
						'clientip'	=>	check::getIp(),
						'cuid'    =>	$this->para['currUserInfo']['uid'],
						'source'    =>	RADIO_SOURCE_APP_ID,
						'radioid'  =>	$this->para['rid']
					);
					$args['extra'] = "from=>0,mid=>".$mid.",isTransmit=>1,is_dj=>".$is_dj;
					$mRadio->writeUserActionLog($args);
					$contentArr = $mRadio->formatFeed(array($result));
					//判断是否在线dj
					$program_endtime = $mRadio->isCurrentDj($this->para['currUserInfo']['uid'],$this->para['rid']);
					if($program_endtime !== false){	
						//添加在线djfeed
						$mRadio->addDjFeed($contentArr[0],$this->para['rid'],$program_endtime);
					}
				}
				else{
					if($GLOBALS ['SUB_ERROR_NO'] != false){
						$data['code'] = $GLOBALS ['SUB_ERROR_NO'];
					}else{
						$data['code'] = 'R01404';
					}
					$data['data'] = '转发失败！';
					$this->display($data, 'json');
				}
			}
			//$comment['user'] = $this->para['currUserInfo'];
			//$comment = $mRadio->formatFeed(array($comment));			
			//$data['comment'] = $comment[0];
			//$data['mid'] = $this->para['resourceId'];			
			
			//$html = $this->display(array('tpl'=>array('radio/singlecomment.html'),'data'=>$data),'html',true);
			$data = array(
				'code'=>'A00006',
				'data'=>$res
			);
		}elseif ($result['error_code'] == '20101'){
			$data['code'] = 'M01160';
			$data['data'] = '微博不存在！';
		}elseif('20021'==$result['error_code']){
			//BaseModelCommon::debug($result['error_code'],'result');
				$data['code'] = 'R10004';
				$data['data'] = '含有违禁字符！';
			}elseif ($result['error_code'] == '20032'){
			$data['code'] = 'M02006';
			$data['data'] = '评论已经提交，请耐心等待管理员审核，谢谢！';
		}elseif ($result['error_code'] == '20019'){
			$data['code'] = 'M02021';
			$data['data'] = '请不要重复发类同内容！';
		}
		 else{
			if($GLOBALS ['SUB_ERROR_NO'] != false){
				$data['code'] = $GLOBALS ['SUB_ERROR_NO'];
			}else{
				$data['code'] = 'R01404';
			}
			$data['data'] = '评论失败';
			
		}
		$this->display($data, 'json');
	}
}

new Comment(RADIO_APP_SOURCE);
?>