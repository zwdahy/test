<?php
/**
 * mAttention model
 * 
 * 
 * @package model
 * @author wangchao<wangchao@staff.sina.com.cn>
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */
class mAttention extends model {
	/*
	 * 判断当前用户与目标用户的关注关系
	 * 
	 * $param string
	 * $param string
	 */
	public function getFriendship1($target_id, $source_id = NULL) {
		$cuid = $target_id;
		$uid = $source_id;
		if(empty($cuid) || !is_numeric($cuid)) return false;
		$dBlog = clsFactory::create(CLASS_PATH . 'data', 'dAttention', 'service');
		if(empty($uid)) {
			$param = array('target_id'=>$cuid);
		} else {
		    $param = array('source_id'=>$uid, 'target_id'=>$cuid);
		}
		return $dBlog->getFriendship($param);
	}
	public function getFriendship($uid, $fuids) {
		$dBlog = clsFactory::create(CLASS_PATH . 'data', 'dAttention', 'service');
		return $dBlog->getFriendship($uid, $fuids);
	}
	/*
	 * 添加关注
	 * 
	 * $param array
	 */
	public function createFriendships($args) {
		$dBlog = clsFactory::create(CLASS_PATH . 'data', 'dAttention', 'service');
		return $dBlog->createFriendship($args);
	}
}
?>