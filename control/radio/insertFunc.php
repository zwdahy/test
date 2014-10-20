<?php
/**
 * Project:     微博电台频道
 * File:        insertFunc.php
 *
 * @package
 * @author 张倚弛6570<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * @date 2010-11-25
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "config/area.php";
include_once SERVER_ROOT . "dagger/libs/extern.php";
include_once SERVER_ROOT."data/radio/dRadio.php";
include_once SERVER_ROOT."model/mPerson.php";
include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';

DisplaySmarty::getSmartyObj();
DisplaySmarty::$smarty->left_delimiter = '{=';
DisplaySmarty::$smarty->right_delimiter = '=}';

/**
 * 顶部js，css导入
 * @param $params
 * @param $smarty
 */
 //此处smarty自动调用该方法
function insert_radio_globaljs($params, &$smarty) {
	//获取用户语言环境
	global $LANGUAGE;
	$LANGUAGE = in_array($LANGUAGE, array('zh-cn','zh-tw')) ? $LANGUAGE : 'zh-cn';
	$GLOBALS['lang'] = $LANGUAGE;
//	print_r($params);
//	exit;
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$scope = $mRadio->formatScope($params['rid']);
//	echo '<pre>';
//	print_r($scope);
//	exit;
	$smarty->assign('scope', $scope);
	$html = $smarty->fetch("radio/module/module_radio_globaljs.html");
	return $html;
//	$shareContent = sprintf(RADIO_SHARE_CONTENT, $radioInfo['name'], $radioInfo['province_spell'], $radioInfo['domain']);
//	$province_id = !empty($scope['curruserinfo']) ? $params['curruserinfo']['province'] : 2;
//	if(empty($params['cuid'])) {
//		$islogin = 0;
//	} else {
//		$islogin = 1;
//	}
//	$from = $params['from'];
//	//当前用户信息
//	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
//	$currentUser = $mRadio->getUserInfoByUid( array($params['cuid']) );
//
//
//	if($radioInfo['rid']==1052){
//	$dRadio=new dRadio;
//	$url="http://zhiyuan.edu.sina.com.cn/index.php?p=zhiyuan&s=api&a=have_i_applied_the_activity&activity_id=0&testdb=0&uid=".$params['cuid'];
//	$res = json_decode($dRadio->GetWithCookie($url),true);
//		if($res['result']['status']['code']!=0||$res['result']['data']==0){
//			header("Location:http://radio.weibo.com/error.html");
//		}
//	}
//
//	//顶部导航的判断
//	$_wv = 5;
//
//	$smarty->assign('severtime' , time());
//    $smarty->assign('global_css',RADIO_CSS_PATH);
//    $smarty->assign('global_js',RADIO_JS_PATH);
//    $smarty->assign('js_css_version',JS_CSS_VERSION);
//    $smarty->assign('radioinfo', $radioInfo);
//    $smarty->assign('cuid',$params['cuid']);
//    $smarty->assign('_wv',$_wv);
//    $smarty->assign('cur_user', $currentUser);    
//    $smarty->assign('from', $from);
//    $smarty->assign('province_id', $province_id);
//	$smarty->assign('lang', $LANGUAGE);
//	$smarty->assign('pic', RADIO_ICON_PATH);
//	$smarty->assign('pid', RADIO_ICON_PID);
//	$smarty->assign('content', $shareContent);
//	$smarty->assign('islogin', $islogin);
//
//	/*水印*/
//	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
//	$markinfo = $mRadio->getWaterMark($params['cuid']);
//	$smarty->assign('markinfo',$markinfo);//水印信息
//
//	/*表情*/
//    $emotions = $sources = array();
//    if (is_array($GLOBALS['EMOTION_TXT2ID'][$LANGUAGE])){
//        foreach($GLOBALS['EMOTION_TXT2ID'][$LANGUAGE] as $text => $src) {
//        	if(in_array($src, array('green','dumpling'))) {
//				continue;
//			}
//        	if(empty($sources[$src])) {
//                $emotions[] = array('icon'=>$text, 'value'=>"[{$text}]", 'src'=>"basic/{$src}.gif");
//                $sources[$src] = true;
//            }
//        }
//    }
//
//	switch($from){
//		case '0':
//			$s_pid = '15002';
//			break;
//		case '2':
//			$s_pid = '15001';
//			break;
//		default:
//			$s_pid = '15009';
//			break;
//	}
//	$smarty->assign('s_pid',$s_pid);
//    $r = $params['backurl'];
//	if(empty($r)) $r = RADIO_URL .$_SERVER['SCRIPT_NAME'];
//	//global $CONF_PROVINCE;
//	//$smarty->assign('provinces' , json_encode(array($CONF_PROVINCE)));
//	$smarty->assign('logoutUrl',T_URL . '/logout.php?backurl=');
//	$smarty->assign('r',urlencode($r));
//	$smarty->assign('timestamp', time());
//    $smarty->assign('jsonIcons',json_encode($emotions));
//	$html = $smarty->fetch("radio/module/module_radio_globaljs.html");
	/*
	//scope
	//电台分类信息
	$radio_classfication=$mRadio->getClassificationList();
	$radio_classfication=$radio_classfication['result'];
	foreach ($radio_classfication as &$v){
		unset($v['sort']);
	$radio_classfication=json_encode($radio_classfication);
	}
	if($cur_rid!=0){
		//获取本电台信息
		$radio_info = $mRadio->getRadioInfoByRid(array($cur_rid));
		$radio_info = $radio_info['result'][$cur_rid];
		//获取获取本用户信息
		//登录检测
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$curruserinfo = $mPerson->currentUser();//取得当前登录用户的信息
		$curruserinfo= !empty($curruserinfo) ? $curruserinfo : array();	//当前登录用户信息
		$cuid = !empty($curruserinfo) ? $curruserinfo['uid'] : 0;	//当前登录用户id
		//当前用户身份
		$admin_id = $mRadio->getAllPowerList();
		$admin_id = $admin_id['result'];
		$curruserinfo['power'] = 'visit';
		$isCurrentDj = $mRadio->isCurrentDj($cuid,$cur_rid);
		if(($cuid > 0 && $cuid == $radio_info['admin_uid']) || in_array($cuid,$admin_id)){
			$curruserinfo['power'] = 'admin';
		}
		if($isCurrentDj !== false && $radio_info['power'] == 'visit'){
			$curruserinfo['power'] = 'djonline';
		}
	}
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
        DisplaySmarty::$smarty->left_delimiter = '{=';
        DisplaySmarty::$smarty->right_delimiter = '=}';

        $data['servertime'] = time();
        $data['rid'] = $cur_rid;
        $data['radio_info'] = $radio_info;
        $data['curruserinfo'] = $curruserinfo;
        $radio_info['radio_url'] = RADIO_URL."/".$radio_info['province_spell'].'/'.$radio_info['domain'];
        $data['radioInfo'] = $radio_info;
        $data['cuid'] = $cuid;
        if(empty($cuid)) {
            $islogin = 0;
        } else {
        $islogin = 1;
        }
        $data['islogin'] = $islogin;
		$smarty->assign('data',$data);
		$this->display ( array ('tpl' => array ('radio/module/module_radio_scope.html' ), 'data' => $data ), 'html' );
		*/
		//return $html;
}

/**
 * 顶导托盘，修复IE8下，重新写了放到body下面
 * @param $params
 * @param $smarty
 */
function insert_radio_topjs($params, &$smarty) {	
	//获取用户语言环境
	global $LANGUAGE;
	$LANGUAGE = in_array($LANGUAGE, array('zh-cn','zh-tw')) ? $LANGUAGE : 'zh-cn';
	$GLOBALS['lang'] = $LANGUAGE;
	$radioInfo = $params['radioInfo'];
	if(empty($params['cuid'])) {
		$islogin = 0;
	} else {
		$islogin = 1;
	}
	$from = $params['from'];
	
	//当前用户信息
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$currentUser = $mRadio->getUserInfoByUid( array($params['cuid']) );
	//顶部导航的判断
	$_wv = 5;
	$smarty->assign('global_css',RADIO_CSS_PATH);
	$smarty->assign('global_js',RADIO_JS_PATH);
	$smarty->assign('js_css_version',JS_CSS_VERSION);
	$smarty->assign('radioinfo', $radioInfo);
	$smarty->assign('cuid',$params['cuid']);
	$smarty->assign('_wv',$_wv);
	$smarty->assign('cur_user', $currentUser);
	$smarty->assign('from', $from);
	$smarty->assign('lang', $LANGUAGE);
	$smarty->assign('islogin', $islogin);
	$html = $smarty->fetch("radio/module/module_radio_topjs.html");
	return $html;
}


/**
 * 导航栏
 * @param $params
 * @param $smarty
 */
function insert_radio_nav($params, &$smarty){
	$from = $params['from'];
	switch($from){
		case '0':
			$pid = '15002';
			break;
		case '2':
			$pid = '15001';
			break;
		default:
			$pid = '15009';
			break;
	}
	if(empty($params['userinfo']['uid'])) {
		$islogin = 0;
	} else {
		$islogin = 1;
		$smarty->assign('username',$params['userinfo']['nick']);
		$smarty->assign('useruid',$params['userinfo']['uid']);
		$smarty->assign('portrait',$params['userinfo']['portrait']);
		if($params['userinfo']['level'] == 2){
			$smarty->assign('verify',1);
		}else{
			$smarty->assign('verify',0);
		}
	}

	$r = $params['backurl'];
	if(empty($r)) $r = RADIO_URL .$_SERVER['SCRIPT_NAME'];
	$smarty->assign('pid',$pid);
	$smarty->assign('js_css_version',JS_CSS_VERSION);
	$smarty->assign('islogin',$islogin);
	$smarty->assign('logoutUrl',T_URL . '/logout.php?backurl=');
	$smarty->assign('r',urlencode($r));
	$smarty->assign('timestamp', time());
	$html = $smarty->fetch("radio/module/module_radio_nav.html");
	return $html;
}

/**
 * 底部导航
 */
function insert_radio_footer ($params, &$smarty) {
	$params['version'] = $params['version'] == 'V4' ? 'V4' : 'V3';
	$smarty->assign('version',$params['version']);
	$html = $smarty->fetch("radio/module/module_radio_footer.html");
	return $html;
}

/**
 * 电台feed列表
 * @param $params
 * @param $smarty
 */
function insert_radio_feedlist($params, &$smarty){
	$rid = $params ['rid'];
	if (!empty ( $rid )) {
		$type = !empty($params['type']) ? $params['type']: 'user_feed';
		$page = !empty($params['page']) ? $params['page'] : 1;
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($type == 'user_feed'){
			$aFeedList = $mRadio->getFeedListByRid($rid,$page);
			$radioInfo = $mRadio->getRadioInfoByRid(array($rid));
			$radioInfo = $radioInfo['result'][$rid];
			$keyword = str_replace('#','',$radioInfo['tag']);
			$keyword = rawurlencode('#'.$keyword.'#');
		}
		else{
			$aFeedList = $mRadio->getDjFeedListByRid($rid,$page);
		}

		$aList = $aFeedList['result'];

		if($page == ceil($aFeedList['count']/RADIO_FEEDLIST_PAGESIZE) ){
			$more = 1;
		}
		else{
			$more = 0;
		}
		$pageData = '';
		$pagelist ='';
		$p = clsFactory::create(CLASS_PATH.'tools/page', 'Page','service');
		$p->page(RADIO_FEEDLIST_PAGESIZE, $page, '?page=', $aFeedList['count'], $pageData, 'ajax_getfeedlist?page=');
		$p->getpagelist_v3($pagelist);
	}else{
		$aList = $params['data'];
	}
	if($aList){
		$format = new dRadio();
		foreach($aList as $val){
		    $text = ereg_replace("<a [^>]*>|<\/a>","",$val['content']['text']);
			$text = preg_replace('/<(img([^>]*src[^>]*title[^>]*))\/>/iU','[[[img$1/img]]]',$text);
			$text=preg_replace("(\'|\")","abcdefg$1",$text); 
			$text = htmlspecialchars($text);
			$text=str_replace("abcdefg","\"",$text); 
			$text=str_replace(array('[[[img','/img]]]'), array('<','>'), $text);
			$val['content']['text'] = $text;
			$tmp = $format->formatText($val['content']);
			$val['content']['text'] = $tmp['text'];
			if(!empty($val['rt'])){
				$rt = $format->formatText($val['rt']);
				$val['rt']['text'] = $rt['text'];
			}
			$aListtmp[] = $val;
		}
	}
	$aList = $aListtmp;

	$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
	$cuserInfo = $person->currentUser();
	//var_dump($aList);
	$smarty->assign ('currUserInfo',$cuserInfo);
	$smarty->assign ( 'more', $more);
	$smarty->assign	( 'rid', $params['rid']);
	$smarty->assign	( 'type', $type);
	$smarty->assign	( 'urlkeyword', $keyword);
	$smarty->assign ( 'list', $aList );
	$smarty->assign ( 'pageinfo', $pageData );
	$smarty->assign ( 'pagelist', $pagelist );
	//$html = $smarty->fetch ( "radio/module/module_radio_feedlist.html" );
	//return $html;
	return array(
		'user'=>$cuserInfo,
		'content'=>$aList
		);
}

/**
 * 新微博html元素
 */
function insert_radio_newfeed($params, &$smarty) {
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$aList = $params['data'];

	if(empty($aList)){
		return false;
	}

	//var_dump($aList);
	$smarty->assign ( 'list', $aList );
	$html = $smarty->fetch ( "radio/module/module_radio_newfeed.html" );
	//return $html;
}

/**
 * 电台dj元素
 * @param $params
 * @param $smarty
 */
function insert_radio_djinfo($params, &$smarty) {
	$rid = $params['rid'];
	$cuid = $params['cuid'];
	$pid = $params['pid'];
	$djinfo = array();
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	//获取电台主持人
	if(!empty($rid)){
		$aDj = $mRadio->getDjDetail($rid, $cuid);
		if($aDj['errorno'] == 1){
			$djinfo = $aDj['result'];
		}
	}
	if(!empty($pid)){
		$djinfo = $mRadio->getDjNowByPid($pid);
	}

	//根据页面不同判断dj显示隐藏个数
	$from = $params['from'];
	$more = false;
	$count = count($djinfo['userinfo']);
	$hide_djinfo = array();
	$showcount = $from == 0 ? 10 : 10;
	if(($count > $showcount && $from == 0) || ($count > $showcount && $from == 1)){
		$more = true;
		$tmp = $djinfo;
		if($from == 0){
			$djinfo['userinfo'] = array_slice($djinfo['userinfo'],0,$showcount,true);
			$hide_djinfo = 	array_slice($tmp['userinfo'],$showcount,$count-$showcount,true);
		}
		if($from == 1){
			$djinfo['userinfo'] = array_slice($djinfo['userinfo'],0,$showcount,true);
			$hide_djinfo = 	array_slice($tmp['userinfo'],$showcount,$count-$showcount,true);
		}
	}

	$islogin = $cuid > 0 ? true : false;

	$smarty->assign	('islogin',$islogin);
	$smarty->assign ('from', $params['from']);
	$smarty->assign ( 'radiodj', $djinfo );
	$smarty->assign ( 'hide_djinfo', $hide_djinfo );
	$smarty->assign ( 'more', $more );
	$html = $smarty->fetch ( "radio/module/module_radio_djinfo.html" );
	return $html;
}

/**
 * 微电台右下角“微电台合作咨询”公共模块
 * @param $params
 * @param $smarty
 */
function insert_radio_rigth_corner($params, &$smarty) {
	$cuid = $params['cuid'];
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$Minfo = $mRadio->getOfficialMinfo($cuid);
	$smarty->assign('Minfo',$Minfo);
	$html = $smarty->fetch ( "radio/module/module_radio_right_corner.html" );
	return $html;
}
/**
 *微电台右下角“微电台合作咨询”公共模块(帮助页特殊处理)
 * @param $params
 * @param $smarty
 */
function insert_radio_rigth_corner_help($params, &$smarty) {
	$cuid = $params['cuid'];
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$Minfo = $mRadio->getOfficialMinfo($cuid);
	$smarty->assign('Minfo',$Minfo);
	$html = $smarty->fetch ( "radio/module/module_radio_right_corner_help.html" );
	return $html;
}
/**
 * 微电台弹出层
 * @param $params
 * @param $smarty
 */
function insert_radio_pop($params, &$smarty) {
	$smarty->assign('type',$params['type']);
	$smarty->assign('data',$params['data']);
	$html = $smarty->fetch ( "radio/module/module_radio_pop.html" );
	return $html;
}

/**
 * 节目在线dj层
 * @param $params
 * @param $smarty
 */
function insert_radio_program_djinfo($params,&$smarty){
	$smarty->assign('data',$params['data']);
	$html = $smarty->fetch ( "radio/module/module_radio_program_djinfo.html" );
	return $html;
}

/**
 * 节目在线dj展示区
 */
function insert_radio_dj_feedlist($params,&$smarty){
	$new = !empty($params['new']) ? $params['new'] : false;
	$rid = $params['rid'];
	$program_now = $params['program_now'];
	$program_visible = $params['program_visible'];
	$power = $params['power'];
	$cuid = $params['cuid'] > 0 ? $params['cuid']: 0;
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$isCurrentDj = false;
	if($cuid > 0){
		$isCurrentDj = $mRadio->isCurrentDj($cuid,$rid);
	}
	$djfeed = array();
	if(time() >= strtotime($program_now['starttime']) && time() <= strtotime($program_now['endtime'])){
		$djfeed = $mRadio->getDjFeed($rid,strtotime($program_now['endtime']));
	}
	if(!empty($djfeed)){
		foreach($djfeed as &$value){
			$mids[] = $value['mid'];
			if($value['ctime']){
				$value['created_at'] = $mRadio->timeFormat($value['ctime']);
			}
			else{
				$value['created_at'] = $mRadio->timeFormat($value['time']);
			}
		}
	}

	$isCurrentDj = $isCurrentDj === false ? false : true;

	//去掉超级管理员的在线dj权限
	if($power == 'admin'){
	//	global $RADIO_ADMIN;
		$admin_id = $mRadio->getAllPowerList();
		$admin_id = $admin_id['result'];
		if(in_array($cuid,$admin_id)){
			$power = 'visit';
		}
	}

	$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
	$cuserInfo = $person->currentUser();

	$smarty->assign ('currUserInfo',$cuserInfo);
	$smarty->assign('rid',$rid);
	if(!empty($mids)){
		$smarty->assign('mids',implode(',',$mids));
	}
	$smarty->assign('new',$new);
	$smarty->assign('list',$djfeed);
	$smarty->assign('listcount',count($djfeed));
	$smarty->assign('program_visible',$program_visible);
	$smarty->assign('power',$power);
	$smarty->assign('isCurrentDj',$isCurrentDj);
	$html = $smarty->fetch ( "radio/module/module_radio_dj_feedlist.html" );
	return $html;
}

/**
 * 在线dj feed区评论列表
 * @param $params
 * @param $smarty
 */
function insert_radio_dj_commentlist($params,&$smarty){
	$smarty->assign('ownerUid',$params['ownerUid']);
	$smarty->assign('mid',$params['mid']);
	$smarty->assign('mid62',$params['mid62']);
	$smarty->assign('cuid',$params['cuid']);
	$smarty->assign('comment',$params['comment']);
	$smarty->assign('other_comment_count',$params['other_comment_count']);
	$smarty->assign('t_url',$params['t_url']);
	$html = $smarty->fetch ( "radio/module/module_radio_dj_commentlist.html" );
	return $html;
}

/**
 * 按地区换台
 */
function insert_radio_switch_area($params,&$smarty){
	$pid = $params['pid'];
	$rid = !empty($params['rid']) ? $params['rid'] : "";
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$radioList = $mRadio->getRadioInfoByPid(array($pid));
	$radiolistbypid['list'] = $radioList['result'][$pid];
	if(!empty($radiolistbypid['list'])){
		foreach($radiolistbypid['list'] as $key => $value){
			if($value['online'] == 2){
				unset($radiolistbypid['list'][$key]);
			}
			else{
				$radiolistbypid['list'][$key]['isnew'] = $mRadio->checkRadioIsNew($value['first_online_time']);
			}
		}
	}

	global $CONF_PROVINCE;
	$radiolistbypid['name'] = $CONF_PROVINCE[$pid];
	$radiolistbypid['count'] = count($radiolistbypid['list']);

	$smarty->assign('rid',$rid);
	$smarty->assign('radiolistbypid',$radiolistbypid);
	$smarty->assign('from',$params['from']);
	$html = $smarty->fetch ( "radio/module/module_radio_switch_area.html" );
	return $html;
}

/**
 * 按分类换台
 */
function insert_radio_switch_sort($params,&$smarty){
	$cid = $params['cid'];
	$cid = !empty($params['cid']) ? $params['cid'] : "";
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$sort = $mRadio->getAreaHasOnline();
	$radioInfo = $mRadio->getRadioInfoByClassificationids(array($cid));
	$radio_p = array();
	$radio_s = array();
	$radio_list = array();
	if(!empty($radioInfo['result'])){
		foreach($radioInfo['result'] as $v){
			foreach($v as $val){
					$radio_p[$val['province_id']][] = $val;
			}
	
			}
			foreach($radio_p as $value){
				$arg = array('radios'=>$value,'sort'=>$sort[$value[0]['province_id']]['sort']);
				$radio_s[] = $arg;
			}

			foreach($radio_s as $k=>$s){
				$p_sort[$k] = $s['sort'];
			}
			array_multisort($p_sort,SORT_ASC,$radio_s);
			
			foreach($radio_s as $v1){
				foreach($v1['radios'] as $v2){
					$radio_list[] = $v2;
				} 
			}
		}
	if(!empty($radio_list)){
		foreach($radio_list as $key => $value){
			if($value['online'] == 2){
				unset($radio_list[$key]);
			}
			else{
				$radio_list[$key]['isnew'] = $mRadio->checkRadioIsNew($value['first_online_time']);
			}
		}
	}
	$radiolistbycid['list'] = $radio_list;
	$radiolistbycid['count'] = count($radio_list);
	$smarty->assign('radiolistbycid',$radiolistbycid);
	$smarty->assign('from',$params['from']);
	$html = $smarty->fetch ( "radio/module/module_radio_switch_sort.html" );
	return $html;
}
/**
 * 地区首页正在播出区域（本地节目，本地热门节目）
 */
function insert_radio_area_live($params,&$smarty){
	$pid = $params['pid'];
	$type = $params['type'];
	$data = $params['data'];
	if(!empty($data)){
		$live_pinfo = $data;
	}
	else{
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($type == 'live'){
			//获取正在播出的本地节目
			$live_pinfo = $mRadio->getProgramNowByPid($pid);
			$live_pinfo = !empty($live_pinfo) ? $live_pinfo:array();
		}
		else{
			//获取正在播出的热门节目
			$live_pinfo = $mRadio->getHotProgramByDay();
			$live_pinfo = !empty($live_pinfo) ? $live_pinfo:array();
		}
	}

	$smarty->assign('area_live_pinfo',$live_pinfo['program_info']);
	$smarty->assign('area_live_pinfo_count',count($live_pinfo['program_info']));
	$smarty->assign('changetime',($live_pinfo['min_endtime']+2)*1000);
	$smarty->assign('area_live_type',$type);
	$html = $smarty->fetch ( "radio/module/module_radio_area_live.html" );
	return $html;
}

/**
 * 电台结果页正在播出区域（本地节目，本地热门节目）
 */
function insert_radio_hot_live($params,&$smarty){
	$rid = $params['rid'];
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	//获取正在播出的热门节目
	$live_pinfo = $mRadio->getHotProgramByDay();
	$live_pinfo = !empty($live_pinfo) ? $live_pinfo:array();

	$smarty->assign('rid',$rid);
	$smarty->assign('live_pinfo',$live_pinfo['program_info']);
	$smarty->assign('live_pinfo_count',count($live_pinfo['program_info']));
	$smarty->assign('changetime',($live_pinfo['min_endtime']+2)*1000);
	$html = $smarty->fetch ( "radio/module/module_radio_hot_live.html" );
	return $html;
}

/**
 * 结果页节目信息
 */
function insert_radio_program($params,&$smarty){
	$rid = $params['rid'];
	$pname = $params['pname'];
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$program_info = $mRadio->getProgramForNameByRid($rid);
	//按天分组的节目单
	$week = array('��һ','�ܶ�','����','����','����','����','����');
	$today = getdate();
	if($today['wday'] == 0){
		$today['wday'] = 7;
	}
	for($n=0;$n<7;$n++){
		$result[$n]['rid'] = $rid;
		$result[$n]['wday'] = $week[$n];
		$result[$n]['today'] = ($today['wday'] == $n+1) ? true : false;
	}
	$programs = $mRadio->getProgramList($rid);	
	
	foreach($programs as $k => $v){			
		$result[$v['day']-1]['program_info'] = $mRadio->getProgramInfo(unserialize($v['program_info']));
		$n = 0;
		foreach($result[$v['day']-1]['program_info'] as &$val){
			if(!empty($val['begintime']) && !empty($val['endtime'])){
				if(time() >= strtotime($val['begintime']) && time() <= strtotime($val['endtime']) && $today['wday'] == $v['day']){
					$val['now'] = true;
				}
				$val['begintime'] = date('H:i',strtotime($val['begintime']));
				$val['endtime'] = date('H:i',strtotime($val['endtime']));
				$val['order'] = $program_info[$val['program_name']]['order'];
				$val['odd'] = $n;
			}	
			$n++;
		}
		$result[$v['day']-1]['count'] = $n;
	}	
	
	//新增节目单的显示状态，visible 2为显示，1为不显示节目单
	$radioinfo = $mRadio->getRadioInfoByRid(array($rid));	
	$program_visible = $radioinfo['result'][$rid]['program_visible'];
	$smarty->assign('program_visible',(int)$program_visible);
	$smarty->assign('program_week',$result);
	$smarty->assign('pname',$pname);
	$smarty->assign('today',$today['wday'] );
	$smarty->assign('program_info',$program_info);
	$smarty->assign('program_info_count',count($program_info));
	$html = $smarty->fetch ( "radio/module/module_radio_program.html" );
	return $html;
}

/**
 * 微电台用户名片（通用）
 */
function insert_radio_name_card($params,&$smarty){
	$uid = $params['uid'];
	$from = $params['from'];
	$program_name = $params['pname'];
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$name_card = $mRadio->getNameCard($uid,$program_name);
	$smarty->assign('name_card',$name_card);
	$smarty->assign('from',$from);
	$html = $smarty->fetch ( "radio/module/module_radio_name_card.html" );
	return $html;
}

/**
 * suda JS
 */
function insert_radio_sudajs($params,&$smarty){
	$smarty->assign('js_css_version',JS_CSS_VERSION);
	$html = $smarty->fetch ( "radio/module/module_radio_sudajs.html" );
	return $html;
}

///**
// * 排行榜页地区收听榜
// */
//function insert_radio_listen_rank($params, &$smarty) {
//	$cur_pid = $params['cur_pid'];
//	if($cur_pid == 1){
//		$cur_province_name = '全国';
//	}
//	elseif($cur_pid == 2){
//		$cur_province_name = '网络';
//	}
//	elseif($cur_pid == 'all'){
//		$cur_province_name = '全部地区';
//	}
//	else{
//		//当前地区名称
//		global $CONF_PROVINCE;
//		$cur_province_name = $CONF_PROVINCE[$cur_pid];
//	}
//
//	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
//	if($cur_pid == 'all'){
//		//获取全部上线电台
//		$radioList = $mRadio->getAllOnlineRadio();
//		$radioList = $radioList['result'];
//
//		//获取地区收听排行榜
//		$listen_rank_province = $mRadio->getListenRank();
//		//微电台排行榜增加new标签
//		if(!empty($listen_rank_province)){
//			foreach($listen_rank_province as $key => &$val){
//				$val['info']['isnew'] = $mRadio->checkRadioIsNew($val['info']['first_online_time']);
//				if($key == count($listen_rank_province)-1){
//					$val['end'] = true;
//				}
//			}
//		}
//		//获取热门节目单排行榜
//		$hot_program_rank = $mRadio->getHotProgramRank(10);
//		if(!empty($hot_program_rank)){
//			$hot_program_rank[count($hot_program_rank)-1]['end'] = true;
//		}
//	}
//	else{
//		//获取地区下电台
//		$radioList = $mRadio->getRadioInfoByPid(array($cur_pid));
//		$radioList = $radioList['result'][$cur_pid];
//		foreach($radioList as $key => $value){
//			if($value['online'] == '2'){
//				unset($radioList[$key]);
//			}
//		}
//
//		//获取地区收听排行榜
//		$listen_rank_province = $mRadio->getListenRankByPid($cur_pid);
//		//微电台排行榜增加new标签
//		if(!empty($listen_rank_province)){
//			foreach($listen_rank_province as $key => &$val){
//				$val['info']['isnew'] = $mRadio->checkRadioIsNew($val['info']['first_online_time']);
//				if($key == count($listen_rank_province)-1){
//					$val['end'] = true;
//				}
//			}
//		}
//		//获取热门节目单排行榜
//		$hot_program_rank = $mRadio->getHotProgramRankByPid($cur_pid);
//		if(!empty($hot_program_rank)){
//			$hot_program_rank[count($hot_program_rank)-1]['end'] = true;
//		}
//	}
//
//	$smarty->assign('cur_pid',$cur_pid);
//	$smarty->assign('cur_province_name',$cur_province_name);
//	$smarty->assign('radio_count',count($radioList));
//	$smarty->assign('listen_rank_province',$listen_rank_province);
//	$smarty->assign('hot_program_rank',$hot_program_rank);
//	$html = $smarty->fetch ( "radio/module/module_radio_listen_rank.html" );
//	return $html;
//}

/**
 * 电台公告
 * @param $params
 * @param $smarty
 */
function insert_radio_notice($params, &$smarty) {
	$smarty->assign('notice',$params['notice']);
	$smarty->assign('noticelong',$params['noticelong']);
	$html = $smarty->fetch ( "radio/module/module_radio_notice.html" );
	return $html;
}

/**
 * !--微电台导航条
 * @param $params
 * @param $smarty
 */
function insert_radio_head($params, &$smarty) {
	$smarty->assign('page_name',$params['page_name']);
	$smarty->assign('is_login',$params['is_login']);
	$html = $smarty->fetch ( "radio/module/module_radio_head.html" );
	return $html;
}

/**
 * !--微电台收听榜
 * @param $params
 * @param $smarty
 */
function insert_radio_listen_rank($params, &$smarty) {
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$radio_listen_rank = $mRadio->getListenRank();
	$smarty->assign('radio_listen_rank',$radio_listen_rank);
	$html = $smarty->fetch ( "radio/module/module_radio_listen_rank.html" );
	return $html;
}

/**
 * !--微电台播放页右侧
 * @param $params
 * @param $smarty
 */
function insert_radio_play_right($params, &$smarty) {
	//$cuid=$params['cuid'];
	$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
	$cuid=$mPerson->getCurrentUserUid();
	$radio_info=$params['radio_info'];
	$radio_info['intro_old'] = htmlspecialchars_decode($radio_info['intro_old']);
	//获取和官方电台的关注关系
	if($cuid>0){
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$res = $mPerson->getRelation2($cuid,array($radio_info['admin_uid']));
		$res = $res['result'];
		if(!empty($res['result'])){
			$radio_info['relation'] = 1;
		}else{
			$radio_info['relation'] = 0;
		}
	}else{
		$radio_info['relation'] = 0;
	}
//		echo '<pre>';
//		print_r($radio_info);
//		exit;	
	$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$admin = $mRadio->getAllPowerList();//@test 临时使用
	$admin = $admin['result'];
//	//判断是否为当前dj
	$isCurrentDj = $mRadio->isCurrentDj($cuid,$radio_info['rid']);
	if($isCurrentDj === false && !in_array($cuid,$admin)){
		$isCurrentDj = 0;
	}else{
		$isCurrentDj = 1;
	}
	//电台热点预告
//	$day = date('N');
//	$program_list = $mRadio->getRadioProgram2($radio_info['rid'],$day);
//	$preview_program = array();
//	//获取多少个预告
//	$preview_num = 3;
//	//获取当前时间
//	$now = time();
//	foreach($program_list as $v){
//		if(strtotime($v['begintime'])>$now){
//			$preview_program[] = $v;
//			if(count($preview_program)>=$preview_num){
//				break;
//			}
//		}
//	}
//	if(!empty($preview_program)){
//		$preview_program = $preview_program;
//	}else{
//		$preview_program = array();
//	}
	//电台主播
	$dj_info = $mRadio->getDjInfoByRid(array($radio_info['rid']));
	$dj_uids = $dj_info['result'][$radio_info['rid']]['uids'];
	//生成dj简单信息
	$dj_uids = explode(',',$dj_uids);
	unset($dj_info);
	foreach($dj_uids as $v){
		$dj_info[] = $mRadio->getSimpleNameCard($v);
	}
	$dj_info = array_chunk($dj_info,10);
//		echo '<pre>';
//		print_r($dj_info);
//		exit;
	//获取正在听该电台的用户信息
	$current_listeners = $mRadio->getListeners($radio_info['rid'],$cuid);
	$current_listeners = $current_listeners['result'];
//		echo '<pre>';
//		print_r($current_listeners[0]);
//		exit;

	$smarty->assign('cuid',$cuid);
	$smarty->assign('radio_info',$radio_info);
	$smarty->assign('isCurrentDj',$isCurrentDj);
	//$smarty->assign('preview_program',$preview_program);
	$smarty->assign('dj_info',$dj_info);
	$smarty->assign('current_listeners',$current_listeners);
	$html = $smarty->fetch ( "radio/module/module_radio_play_right.html" );
	return $html;
}

?>
