<?php
/**
 * 电台播放页(control层)
 *
 * @date 2010-09-01
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";
require_once(SERVER_ROOT.'dagger/libs/extern.php');



//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioIndex extends control{
	protected function checkPara(){
		//判断来源合法性
//		if(!Check::checkReferer()){
//			$this->setCError('M00004','Refer来源错误');
//			return false;
//		}

	}

	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		//获取二级域名
		$page = $GLOBALS['URL_PATH'];
		if(!preg_match('#^/[a-z]+/[a-zA-Z]+(([0-9]+)|([0-9]+\.[0-9]+))(_[a-zA-Z]*[0-9]+)*(\.html)?$#',$page)){
			header("Location:" . T_URL . "/sorry");
			exit;
		}
		$tmp = explode('/',$page);
		$province_spell = $tmp[1];
		$domain_tmp = explode('_', str_replace('.html','',$tmp[2]));
		$domain = $domain_tmp[0];
//		print_r($domain_tmp);
//		exit;

		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取当前电台信息
		$cur_radioInfo = $mRadio->getRadioByDomainAndPro($domain,$province_spell);
		$cur_radioInfo = $cur_radioInfo['result'];
		if(empty($cur_radioInfo) || $cur_radioInfo['online'] == '2'){
			header("Location:" . T_URL . "/sorry");
			exit;
		}
//		echo '<pre>';
//		print_r($cur_radioInfo);
//		exit;
		//路径为 /beijing/fm1039_11400(_r12345678(.html))时跳转
		if(!empty($domain_tmp[1])){
			header('HTTP/1.1 301 Moved Permanently');//发出301头部 
			header("Location:" .RADIO_URL .'/' .$province_spell .'/' .$domain);
			exit;
		}
		//种cookie
		if(!empty($cur_radioInfo['rid'])){
			//取出收听历史
			$rids = $_COOKIE['rid'];
			if(!empty($rids)){
				$rids = explode('|',$rids);
				$rids = array_merge(array($cur_radioInfo['rid']),$rids);
				$rids = array_unique($rids);
				$rids = array_slice($rids,0,3);
				$rids = implode('|',$rids);
			}else{
				$rids = $cur_radioInfo['rid'];
			}
			setcookie('rid', $rids, time() + 60*60*24*30,'/');
		}
//		echo '<pre>';
//		print_r($_COOKIE);
//		exit;
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$cuid=$mPerson->getCurrentUserUid();
		$data=$mRadio->formatScope($cur_radioInfo['rid']);
		//print_r($data);exit;
//		$data['radio_info']['intro_old'] = htmlspecialchars_decode($data['radio_info']['intro_old']);
		$data['radio_info']['intro_old'] = $data['radio_info']['intro_old'];
//		echo '<pre>';
//		print_r($data);
//		exit;
		//获取热点预告图片
		$hot_preview_pic = $mRadio->getRadioPageInfoByBlockName("hot_preview_pic",1);
		if($hot_preview_pic['errorno'] == 1){
			$hot_preview_pic = $hot_preview_pic['result'];
			foreach($hot_preview_pic as $v){
				if(date("Ymd")>date("Ymd",strtotime($v['end_time'])) || date("Ymd")<date("Ymd",strtotime($v['start_time']))){
					unset($hot_preview_pic[$k]);
					continue;
				}
				if($v['rid'] == $cur_radioInfo['rid']){
					$data['hot_preview_pic']=$v;
				}
			}
		}else{
			$data['hot_preview_pic'] = array();
		}
//		echo "<pre>";
//		print_r($cur_radioInfo['rid']);
//		exit;
		//获取热点预告电台
		$hot_preview_radio = $mRadio->getRadioPageInfoByBlockName("hot_preview",1);
//		echo "<pre>";
//		print_r($hot_preview_radio);
//		exit;
		if($hot_preview_radio['errorno'] == 1){
			unset($temp);
			foreach($hot_preview_radio['result'] as $k=>$v){
				if($v['rid'] != $cur_radioInfo['rid'] || date("Ymd")>date("Ymd",strtotime($v['end_time'])) || date("Ymd")<date("Ymd",strtotime($v['start_time']))){
					continue;
				}
				$temp = $mRadio->getRadioInfoByRid(array($v['rid']));
//				echo "<pre>";
//				print_r($temp);
//				exit;
				if($temp['errorno'] == 1){
					$temp = $temp['result'][$v['rid']];
					$data['hot_preview_radio_info'][$k] = $temp;
					$data['hot_preview_radio_info'][$k]['introduce'] = $v['block_text'];
				}
			}
		}else{
			$data['hot_preview_radio_info'] = array();
		}
		$name = $cur_radioInfo['name'];
		$data['page_title'] = sprintf(RADIO_TITLE, $name);
		$data['backurl'] = RADIO_URL.'/' .$province_spell .'/' .$domain;
		$admin = $mRadio->getAllPowerList();
		$admin = $admin['result'];
//		echo '<pre>';
//		print_r($admin);
//		exit;
		//官方账号和管理员有编辑节目单的权限
		$editProgramUser = $admin;
		array_push($editProgramUser,$cur_radioInfo['uid']);
		if(in_array($cuid,$editProgramUser)){
			$data['editprogram'] = 1;
		}else{
			$data['editprogram'] = 0;
		}


		//判断是否为当前dj
		$isCurrentDj = $mRadio->isCurrentDj($cuid,$cur_radioInfo['rid']);
		if($isCurrentDj === false && !in_array($cuid,$admin)){
			$data['isCurrentDj'] = 0;
		}else{
			$data['isCurrentDj'] = 1;
		}

		if($isCurrentDj == false){
			$data['show'] = false;//是否显示在线dj编辑模块
		}else{
			$data['show'] = true;
		}
		
//		echo '<pre>';
//		var_dump($isCurrentDj);
//		exit;
		//电台热点预告 此处依赖后台@test
//		$day = date('N');
//		$program_list = $mRadio->getRadioProgram2($cur_radioInfo['rid'],$day);
//		$preview_program = array();
//		//获取多少个预告
//		$preview_num = 3;
//		//获取当前时间
//		$now = time();
//		foreach($program_list as $v){
//			if(strtotime($v['begintime'])>$now){
//				$preview_program[] = $v;
//				if(count($preview_program)>=$preview_num){
//					break;
//				}
//			}
//		}
//		if(!empty($preview_program)){
//			$data['preview_program'] = $preview_program;
//		}else{
//			$data['preview_program'] = array();
//		}
		//电台主播
		$dj_info = $mRadio->getDjInfoByRid(array($cur_radioInfo['rid']));
		$dj_uids = $dj_info['result'][$cur_radioInfo['rid']]['uids'];
		//生成dj简单信息
		$dj_uids = explode(',',$dj_uids);
		//die($count);
		unset($dj_info);
		foreach($dj_uids as $v){
			$dj_info[] = $mRadio->getSimpleNameCard($v);
		}
		//做成二维数组前台显示用
		$data['dj_info'] = array_chunk($dj_info,10);
//		echo '<pre>';
//		print_r($data);
//		exit;
//		正在听用户
//		$current_listeners = $mRadio->getListeners($cur_radioInfo['rid'],$cuid);
//		$current_listeners = $current_listeners['result'];
//		echo '<pre>';
//		print_r($current_listeners);
//		exit;
//		$data['current_listeners'] = $current_listeners;
//		exit;
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		if($data['curruserinfo']['power'] == 'visit'){
			$this->display ( array ('tpl' => array ('radio/index.html' ), 'data' => $data ), 'html' );
		}else{
			$this->display ( array ('tpl' => array ('radio/djindex.html' ), 'data' => $data ), 'html' );
		}
	}
}

new RadioIndex(RADIO_APP_SOURCE);
?>
