<?php
/**
 * 微博日报-名人推荐函数块
 * 
 * @copyright	(c) 2010, 新浪网 MiniBlog All rights reserved.
 * @author 		王江丰 <jiangfeng3@staff.sina.com.cn>
 * @version		1.0 - 2010-09-29
 * @package		control
 */

/**
 * 获取两个用户关系的详细情况
 */
function smarty_block_recommendstar_show($params, $content, &$smarty, &$repeat){
	//输出参数名称
	$ouput_name = isset($params['name']) ? $params['name'] : 'user';
	$userinfo = array();
	$at_re = array();
	//没有内容时数据初始化处理
	if(is_null($content)){
		//查询条件定义
		$where = array();
		foreach($params as $k=>$v){
			switch ($k){
				case 'name' :
					break;
					//目标uid
				case 'uid' :
					$where['uid'] = $v;
					break;
				default :
					$smarty->trigger_error("smarty_plugins: unknown attribute '$k'");
					break;
			}
		}
		//获取数据
		$mPerson = clsFactory::create(CLASS_PATH.'model', 'mPerson', 'service');
		$mMblog  = clsFactory::create(CLASS_PATH.'model', 'mMblog', 'service');
		$curruser = $mPerson->currentUser(false);
		//数据存放标记
		if($curruser !== false){
			$at_re = $mMblog->newGetUserRelation($curruser['uid'],array($where['uid']));
		}
		//获取用户信息
		$userinfo = $mMblog->newGetUserList(array($where['uid']),50);
		//print_r($userinfo);
		//渲染数据
		$output_data = array(
			'uid'=>$where['uid'],
			'following'=>$at_re['one2many'][$where['uid']],
		    'name'=>$userinfo[$where['uid']]['name'],
		    'viptitle'=>$userinfo[$where['uid']]['viptitle'],
		    'level'=>$userinfo[$where['uid']]['level'],
		    'icon'=>$userinfo[$where['uid']]['icon'],
		    'attnum'=>$userinfo[$where['uid']]['attnum'],
		);
		$smarty->assign($ouput_name, $output_data);
	}
	
	return $content;
}
?>