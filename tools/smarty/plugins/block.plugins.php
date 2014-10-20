<?php 
/**
 *	@param search_term 搜索条件数组
 *	@param listName 循环模块的名称
 * @author 翟健雯6982<jianwen@staff.sina.com.cn>
 * @copyright (c) 2010, 新浪网 MiniBlog All rights reserved.
 */
function smarty_block_feedlist($params, $content, &$smarty, &$repeat){
	$search_term = $params['search_term'];
	$filter_ori = $params['filter_ori'];
	$count = $params['count'];
	if(!$content){
		$search_term = (null != $search_term) ? $search_term : array('q' => ''); //默认搜索条件为空
		$filter_ori = (null != $filter_ori) ? $filter_ori : 5; //默认为原创
		$count = (null != $count) ? $count : 20; //默认返回20条
		$array_temp = array(
				'q' => $search_term,
				'filter_ori' => $filter_ori,
				'count' => $count
				);
		$listname = (null != $listname) ? $listname : "feedlist"; //默认的section名称
		$o_search = clsFactory::create(CLASS_PATH.'model','mPlugins','service');
		$result = $o_search -> getFeedList($array_temp);
		//echo count($result);
		$smarty -> assign($listname, $result);
		unset($result);
	}
	if(!$repeat)
		return $content;
}
?>