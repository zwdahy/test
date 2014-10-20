<?php
/**
 * basic data
 *
 * @date 2010-08-30
 * @package
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */
class dRadioBasic extends data{
	/**
	 * 通过mid获取微博信息
	 * @param array $args
	 * @return array
	 */
	public function getMblogByMids($args,$mids){
		$objbmBlog = clsFactory::create ('libs/basic/model', 'bmBlog' );
		$objbmBlog->setPara('uid',$args['cuid']);
//		$objbmBlog->setPara('appid',$args['appid']);
		$objbmBlog->setPara('cip',$args['cip']);
		$objbmBlog->setPara('appkey',$args['appkey']);
		return $objbmBlog->getMblogByMids($mids);
	}
	
	/**
	 * 通过mid获取微博转发评论数
	 * @param array $args
	 * @return array
	 */
	public function getRtAndCmtNum($args,$mids){
		$objbmComment = clsFactory::create ('libs/basic/model', 'bmComment' );
		$objbmComment->setPara('uid',$args['cuid']);
		$objbmComment->setPara('appid',$args['appid']);
		$objbmComment->setPara('cip',$args['cip']);
		$objbmComment->setPara('appkey',$args['appkey']);
		//$obj->setPara('userpwd',$userpwd);
				
		return $objbmComment->getRtAndCmtNum($mids);		
	}
	
	/**
	 * 通过uid获取用户信息（批量）
	 * @param array $args
	 * @return array
	 */
	public function getUserInfo($args,$uids){
		$objbmUser = clsFactory::create ('libs/basic/model', 'bmUser' );
		$objbmUser->setPara('uid',$args['cuid']);
		$objbmUser->setPara('appid',$args['appid']);
		$objbmUser->setPara('cip',$args['cip']);
		$objbmUser->setPara('appkey',$args['appkey']);
		//$obj->setPara('userpwd',$userpwd);
				
		return $objbmUser->getUserMulti($uids);
	}
	
	/**
	 * 批量发私信
	 * @var array $args
	 */
	public function sendMessageMulti($args){
		$obj_sm = clsFactory::create ('libs/basic/model', 'bmMessage' );
		$obj_sm->setPara('uid',$args['cuid']);
		$obj_sm->setPara('appid',$args['appid']);
		$obj_sm->setPara('cip',$args['cip']);
		$obj_sm->setPara('appkey',$args['appkey']);
		return $obj_sm->sendMessageMulti($args['fromuid'], $args['content'], $args['touids']);
	}
}
?>