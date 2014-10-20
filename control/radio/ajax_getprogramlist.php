<?php

/**
 * Project:     radio
 * File:        ajax_getprogramlist.php
 * 
 * 根据传入的rid和星期获取当天电台的节目单(不可视的不输出) 合并加入回听
 * 
 * @copyright sina.com
 * @author wenda@ <wenda@staff.sina.com.cn>
 * @package radio
 * @2014/4/24
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
Check::create_token();
class GetProgram extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval( request::post( 'rid','INT' ) );
		$this->para['day'] = $_POST['day'];
        if( '0'===$this->para['day'] ){
			$this->para['day']=7;
		}
		$this->para['day'] = intval($this->para['day']);
        if(empty($this->para['day'])){
            $this->para['day'] = date('N');
        }
//		//测试数据
//		$this->para['day'] = 1;
//		$this->para['rid'] = '848';		

		//参数检测处理
		if(empty($this->para['rid'])||empty($this->para['day'])) {
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
		//获取当前rid节目单
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$radioinfo = $mRadio->getRadioInfoByRid(array($this->para['rid']));
		$program_visible = $radioinfo['result'][$this->para['rid']]['program_visible'];//是否显示
		$radio_url = $radioinfo['result'][$this->para['rid']]['radio_url'];
		unset($radioinfo);
		if($program_visible==2){
			$programList=$mRadio->getRadioProgram2($this->para['rid'],$this->para['day']);
			foreach($programList as &$v){
				$v['radio_url'] = $radio_url;
				$v['dj_info']=array_values($v['dj_info']);
//				$types = $mRadio->getRadioProgramType($v['program_id']);
//                $v['program_types'] = $types['result'];

//				if($this->para['day']!=date('N')){
////					$url='http://'.$_SERVER['HTTP_HOST'].'/seek?rid='.$this->para['rid'].'&pgid='.$v['program_id'].'&day='.$this->para['day'].'&ustart_time='.$v['begintime'].'&uend_time='.$v['endtime'];//跳转seek页面
//					$url='http://'.$_SERVER['HTTP_HOST'].'/seek?pgid='.$v['program_id'].'&day='.$this->para['day'];//跳转seek页面
////					//交换获取短连接? 似乎没有必要
////					$url=$mRadio->long2short_url(urlencode($url));
////					if($url['urls']['0']['result']==1){//是否有回看地址
////						$v['seekurl']=$url['urls']['0']['url_short'];
////					}
//					$v['seekurl']=$url;
//				}else if($this->para['day'] == date('N') && strtotime($v['endtime'])<time()){
//							$url='http://'.$_SERVER['HTTP_HOST'].'/seek?pgid='.$v['program_id'].'&day='.$this->para['day'];
//							$v['seekurl']=$url;
//						}else{
//							$v['seekurl']=array();
//						}
				//今天还没开始 明天的 跨越三点的 都没有回听
				if($this->para['day'] == date('N') && strtotime($v['endtime'])>time()||$this->para['day'] == date('N',strtotime('tomorrow'))){
					$v['seekurl']=array();
				}else{
					$url=RADIO_URL.'/seek?pgid='.$v['program_id'].'&day='.$this->para['day'];
					$v['seekurl']=$url;
				}
				if(date('H',strtotime($v['begintime']))<=2 && date('H',strtotime($v['endtime']))>=3){
					$v['seekurl']=array();
				}
				if(strtotime($v['begintime'])<time()&&strtotime($v['endtime'])>time()&&($this->para['day']==date('N'))){
					$v['now']=1;
				}else{
					$v['now']=0;
				}
			}
			unset($v);
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = $programList;
		}else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = array();
		}
		$this->display($jsonArray, 'json');
	}
}

new GetProgram(RADIO_APP_SOURCE);
?>
