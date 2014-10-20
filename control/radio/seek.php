<?php
/**
 * Project:     radio
 * File:        ajax_getradioseek.php
 * 
 * 获取seek电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radiostream.php";
include_once SERVER_ROOT . "config/radioareaspell.php";
include_once SERVER_ROOT . "config/area.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";
//date_default_timezone_set('Asia/Shanghai');

class RadioSeek extends control {
	protected function checkPara() {
		//判断来源合法性
//		if(!Check::checkReferer()){
//			$this->setCError('M00004','Refer来源错误');
//			return false;
//		}
		//获取参数
		//$this->para['rid'] = request::get('rid', 'str');//电台rid
		$this->para['pgid'] = request::get('pgid', 'str');//节目rid
		$this->para['day'] = request::get('day', 'str');//播放星期几的 //实际没用 只用来判断参数罢了
//		$this->para['ustart_time'] = request::get('ustart_time', 'str');//节目开始时间
//		$this->para['uend_time'] = request::get('uend_time', 'str');//节目结束时间
		//$this->para['rid'] = 10;
		//参数检测处理
		if(empty($this->para['pgid'])) {
			//$this->setCError('M00009', '参数错误');
			header("Location:" . T_URL . "/sorry");
			return false;
		}
}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		//知道要回看哪个节目
		$program_info = $mRadio->getRadioProgramByProgramId($this->para['pgid']);
		$program_info = $program_info[0];
		$data=$mRadio->formatScope($program_info['rid']);
//		echo "<pre>";
//		print_r($program_info);
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
				if($v['rid'] == $program_info['rid']){
					$data['hot_preview_pic']=$v;
				}
			}
		}else{
			$data['hot_preview_pic'] = array();
		}
//		echo "<pre>";
//		print_r($program_info['rid']);
//		exit;
		//获取热点预告电台
		$hot_preview_radio = $mRadio->getRadioPageInfoByBlockName("hot_preview",1);
//		echo "<pre>";
//		print_r($hot_preview_radio);
//		exit;
		if($hot_preview_radio['errorno'] == 1){
			unset($temp);
			foreach($hot_preview_radio['result'] as $k=>$v){
				if($v['rid'] != $program_info['rid'] || date("Ymd")>date("Ymd",strtotime($v['end_time'])) || date("Ymd")<date("Ymd",strtotime($v['start_time']))){
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
		unset($temp);
		$data['page_title'] = sprintf(RADIO_TITLE, $program_info['program_name']);
		$data['backurl'] = RADIO_URL.'/seek?pgid='.$this->para['pgid'].'&day='.$this->para['day'];
//		echo '<pre>';
//		print_r($program_info);
//		exit;
		$today = date('N');//今天是星期几
		$day = $program_info['day'];//节目在星期几播放
		//获取该日的节目单
		$programList = $mRadio->getRadioProgram2($program_info['rid'],$day);
		//生成该节目前后的节目
		$preProgram = array();
		$nexProgram = array();
		$count = count($programList);
		foreach($programList as $k=>$v){
			if($v['program_id'] == $program_info['program_id']){
				if($k==0){
					$nexProgram = $programList[$count-1];
				}else{
					$preProgram = $programList[$k-1];
				}
				if($k == ($count-1)){
					$nexProgram = $programList[0];
				}else{
					$nexProgram = $programList[$k+1];
				}
				break;
			}
		}

		if($preProgram){
			if(date('H',strtotime($preProgram['begintime']))<3){
				$data['preProgram'] = '';
			}else{
				$data['preProgram'] = RADIO_URL.'/seek?pgid='.$preProgram['program_id'].'&day='.$this->para['day'];
			}
		}else{
			$data['preProgram'] = '';
		}
		if($nexProgram){
			if(strtotime($nexProgram['endtime'])>time()){
				$data['nexProgram'] = '';
			}else{
				$data['nexProgram'] = RADIO_URL.'/seek?pgid='.$nexProgram['program_id'].'&day='.$this->para['day'];
			}
		}else{
			$data['nexProgram'] = '';
		}
		//获取对应天的流
		$flag = 0;
//		print_r(intval(date('H',strtotime($program_info['begintime']))));
//		exit;
		//三点为分界点
		if(intval(date('H',strtotime($program_info['begintime'])))<3){
			$flag = 1;
		}

		//天数的偏移量
		//获取当天的直播流
		if($today == $day){
			//三点之前的流还是回放流
			if($flag == 0){
				$radio_info_today = $mRadio->getRadioInfoByRid(array($program_info['rid']));
				if($radio_info_today['errorno'] == 1){
					$seekInfo['start_time'] = $radio_info_today['result'][$program_info['rid']]['start_time'];
					$seekInfo['rid'] = $radio_info_today['result'][$program_info['rid']]['rid'];
					$seekInfo['end_time'] = $radio_info_today['result'][$program_info['rid']]['end_time'];
					$seekInfo['epgid'] = $radio_info_today['result'][$program_info['rid']]['epgid'];
				}
			}else{
				$start_time = mktime(0,0,0,date("m"),date("d")-1,date("Y"));//得到对应的日期
				//$start_time = strtotime(date('Ymd')-2);//得到上周对应的星期几
				$start_time = $start_time+21600;//生成开始时间
//				echo '<pre>';
//				print_r(date('Y m d H i s',$start_time));
//				exit;
				$seekInfo = $mRadio->getSeek(array('rid'=>$program_info['rid'],'start_time'=>$start_time));
				$seekInfo = $seekInfo['result'];
			}
		}else{
			//获取上一星期对应天的流
			if($today<$day){
				$temp = $day-$today-7;
			}
			//获取本周对应天的流
			if($today>$day){
				$temp = $day-$today;
			}
//			$start_time = strtotime(date('Ymd')+$temp-$flag);//得到对应的日期
			$start_time = mktime(0,0,0,date("m"),date("d")+$temp-$flag,date("Y"));//得到对应的日期
			$start_time = $start_time+21600;//生成开始时间
			$seekInfo = $mRadio->getSeek(array('rid'=>$program_info['rid'],'start_time'=>$start_time));
			$seekInfo = $seekInfo['result'];
		}
//				echo '<pre>';
//				print_r($seekInfo);
//				exit;
		//转换节目的开始时间格式，方便前台处理
		$program_info['begintime'] = strtotime($program_info['begintime'])+$temp*86400;
		$program_info['endtime'] = strtotime($program_info['endtime'])+$temp*86400;
		$seekInfo['program_info'] = $program_info;
		$data['seekInfo'] = $seekInfo;
		$data['radioinfo'] =  $data['radio_info'];
		$uids = array_keys($program_info['dj_info']);
		if(!empty($uids)){
			unset($di_info);
			foreach($uids as $v){
				$di_info[] = $mRadio->getRadioCardByUid($v);
			}
		}else{
			$di_info = array();
		}
		$data['dj_info'] = json_encode($di_info);
//			echo '<pre>';
//			print_r($data);
//			exit;
		//分配scope变量
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/seek.html' ), 'data' => $data ), 'html' );

	}
	
}

new RadioSeek(RADIO_APP_SOURCE);
?>
