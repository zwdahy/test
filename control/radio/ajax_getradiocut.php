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

//include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "config/radiostream.php";
//include_once SERVER_ROOT . "model/mPerson.php";
//include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetRadioCut extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		//获取参数
		//$this->para['time'] = request::post('time', 'str');
		$this->para['rid'] = intval(request::post('rid', 'str'));
		//@test
//		$this->para['rid'] = 848;
		//参数检测处理
		if(empty($this->para['rid'])) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		//身份校验
        $person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
        $cuserInfo = $person->currentUser();
        $cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
		//获取当前播放节目
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$day=date("N");
		$programList=$mRadio->getRadioProgram2($this->para['rid'],$day);
//		echo '<pre>';
//		print_r($programList);
//		exit;
		//$programList=unserialize($programList['program_info']);
				//error_log(strip_tags(print_r($programList, true))."\n", 3, "/tmp/err.log");
		$data=array();
		$now=time();
		$count=count($programList);
		for($i=0;$i<$count;$i++){
			if((strtotime($programList[$i]['begintime'])<=$now)&&($now<=strtotime($programList[$i]['endtime']))){
				$data['current_pg']=$programList[$i];
				//$data['current_pg']['dj_info']=array_values($programList[$i]['dj_info']);
				$tmp=array();
				if(!empty($data['current_pg']['dj_info'])){
					$djs = array_keys($data['current_pg']['dj_info']);
					$tmp=$mRadio->getSimpleNameCard2($djs);
				}
				$data['current_pg']['dj_info']=array_values($tmp);
				$data['next_pg']=$programList[$i+1];
				if(!empty($data['next_pg']['dj_info'])>0){
					$data['next_pg']['dj_info']=$programList[$i+1]['dj_info'];
				}
				break;
			}
		}
		if(empty($data['next_pg'])){
			$data['next_pg'] = array();
		}
		$data['hascollected']=0;
		if($cuid > 0){
            $hasCollected = $mRadio->hasCollected($cuid,$this->para['rid']);
			if($hasCollected){
				$data['hascollected']=1;
			}
		}
		$jsonArray['code'] = 'A00006';

		//新方式
		$radio_info = $mRadio->getRadioInfoByRid(array($this->para['rid']));
		if($radio_info['errorno']==1){
			$data['epg_id'] = $radio_info['result'][$this->para['rid']]['epgid'];			
			$data['http'] = $radio_info['result'][$this->para['rid']]['http'];
			$data['mu'] = $radio_info['result'][$this->para['rid']]['mu'];
			$data['start_time'] = $radio_info['result'][$this->para['rid']]['start_time'];
			$data['end_time'] = $radio_info['result'][$this->para['rid']]['end_time'];
			$data['cur_time'] = time();//服务器时间
			$jsonArray['data'] = $data;
		}else{
			$jsonArray['code'] = 'E00001';
		}

//		global $RADIO_STREAM;
//		if(!empty($RADIO_STREAM[$this->para['rid']])){
//			$http_url = $RADIO_STREAM[$this->para['rid']]['http'];
////			$mu_url = $RADIO_STREAM[$this->para['rid']]['mu'].'?c='.$encode;
//			$mu_url = $RADIO_STREAM[$this->para['rid']]['mu'];
//			$epg_id = $RADIO_STREAM[$this->para['rid']]['epg_id'];
//			$start_time = $RADIO_STREAM[$this->para['rid']]['start_time'];
//			$end_time = $RADIO_STREAM[$this->para['rid']]['end_time'];			
//			$data['epg_id'] = $epg_id;			
//			$data['http'] = $http_url;
//			$data['mu'] = $mu_url;
//			$data['start_time'] = $start_time;
//			$data['end_time'] = $end_time;
//			$data['cur_time'] = time();//服务器时间
//			$jsonArray['data'] = $data;
//		}else{
//			$jsonArray['code'] = 'E00001';
//		}
		
		$this->display($jsonArray, 'json');
	}

	
//	function encode( $province, $radio) 
//	{
//	if (empty( $province) || empty($radio)) return false;
//	 
//	$tmpstr=NULL;
//	$md5str=NULL;
//	 
//	$key = 'cA3+V9s';	
//	$timenumber = (time() - 1013654233) / 2;	
//	$timenumber = (int) $timenumber;
//	$tmpstr = $key . $province . $radio . $timenumber;
//	$md5str = substr( md5($tmpstr) , 0 , 16 );
//	 
//	return $md5str . $timenumber;
//	}
	
}

new GetRadioCut(RADIO_APP_SOURCE);
?>
