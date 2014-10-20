<?php

/**
 * Project:     radio
 * File:        ajax_getprogram.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
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
//todo  remember open this comment runxi
		//获取参数
		$this->para['rid'] = intval( request::post( 'rid','STR' ) );
		$this->para['day'] = request::post( 'day','INT' );
        if( '0'===$this->para['day'] ){
			$this->para['day']=7;
		}
        if(empty($this->para['day'])){
            $this->para['day'] = date('N');
        }
		//测试数据@test

		//$this->para['rid'] = '848';		

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
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$rid =  $this->para['rid'];
		$radioinfo = $mRadio->getRadioInfoByRid(array($rid));
		$program_visible = $radioinfo['result'][$rid]['program_visible'];
		
		$programs = $mRadio->getRadioProgram($this->para['rid'],$this->para['day']);
		$program_today = array();
		$program_today = unserialize($programs['program_info']);

		//必须在节目单没有隐藏，有当前节目，而且当前节目不为空的状态下，才显示左侧播放的节目
        //&& $program_visible == 2  这个为显示
		if(!empty($program_today) && !empty($program_today[0]['program_id'])&& $program_visible == 2){			
			foreach($program_today as &$value){		
//					echo '<pre>';
//					print_r($value);exit;
                $types = $mRadio->getRadioProgramType($value['program_id']);
                $value['program_types'] = $types['result'];
				if(strtotime($value['begintime'])<time()&&strtotime($value['endtime'])>time()&&($this->para['day']==date('N'))){
					$value['now']=1;
				}else{
					$value['now']=0;
				}
			}
			unset($value);
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = $program_today;			
		}		
		else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = $program_today;
		}
		$this->display($jsonArray, 'json');
	}
}

new GetProgram(RADIO_APP_SOURCE);
?>
