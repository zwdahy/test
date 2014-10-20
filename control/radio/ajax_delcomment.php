<?php
/**
 * 微博日报- 删除评论（ajax控制层）
 * 
 * @copyright	(c) 2010, 新浪网 MiniBlog All rights reserved.
 * @author 		王江丰 <jiangfeng3@staff.sina.com.cn>
 * @version		1.0 - 2010-09-27
 * @package		control
 */
class DelComment extends control{
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
		//获取参数
		$this->para['commentId'] = request::post('commentId','STR');//评论者ID
		$this->para['id']        = request::post('id','STR');       //评论ID
		$this->para['resId']     = request::post('resId','STR');    //微博客ID
		$this->para['resUid']    = request::post('resUid','STR');   //微博客所属者UID
	    
	    //参数检测
	    if(empty($this->para['commentId']) || !is_numeric($this->para['commentId'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}
	    if(empty($this->para['id']) || !is_numeric($this->para['id'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}
	    if(empty($this->para['resId']) || !is_numeric($this->para['resId'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}
	    if(empty($this->para['resUid']) || !is_numeric($this->para['resUid'])){
			$this->setCError('M00009', '参数错误');
			return false;
		}
	}
	
	protected function action(){
	    //判断是否包含信息
		if($this->hasCError()){
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']),'json');
			return false;
		}
		
		//调用delComment方法
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio','mRadio','service');
		
		$updArr = array('cid' => $this->para['id']
						,'is_encoded' => 0);
		$result = $mRadio->delComment($updArr);
		
		if(empty($result['error_code'])){
			$data['code'] = 'A00006';
		}else{
			if($GLOBALS ['SUB_ERROR_NO'] != false){
				$data['code'] = $GLOBALS ['SUB_ERROR_NO'];
			}else{
				$data['code'] = 'R01404';
			}
			$data['data'] = $result;
		}
		$this->display($data, 'json');
	}
}
new DelComment(RADIO_APP_SOURCE);
?>