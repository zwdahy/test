<?php

/**
 * Project:     radio
 * File:        ajax_gethotlive.php
 * 
 * 获取某一天的热播节目
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
//include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetHotLive extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		
		$this->para['rid'] = request::post('rid', 'str');//当前正在收听的电台id	非必须
		$this->para['type'] = request::post('type', 'str');//节目类型 非必须
		$this->para['now'] = request::post('now', 'INT');//是否需要当前 0 当前 1全部
//		$this->para['now'] = 1;//是否需要当前 0 当前 1全部
//		$this->para['type'] = 10;//节目类型 非必须
		//$this->para['rid'] = 848;
//		if(empty($this->para['rid'])) {
//			$this->setCError('M00009', '参数错误');
//			return false;
//		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取正在所有热门节目
		$live_pinfo = $mRadio->getHotProgramByDay2();
		if($live_pinfo){
			if($this->para['now']!=1){
				//获取当前正在播放的节目
				$now = time();
				foreach($live_pinfo as $k=>&$v){
					if($now<strtotime($v['begintime'])||$now>strtotime($v['endtime'])||$v['is_del']!=0){
						unset($live_pinfo[$k]);
						continue;
					}
					$v['dj_info']=array_values($v['dj_info']);
					$v['now']=0;
					if($v['rid']==$this->para['rid']){
						$v['now']=1;
					}
				}
				unset($v);
			}
//		print "<pre>";
//		print_r($live_pinfo);
//		exit;
			$live_pinfo=array_values($live_pinfo);
			if(!empty($this->para['type'])){
				//按节目类型分类
				foreach($live_pinfo as &$v){
					if(empty($v['type'])){
						$res['nosort'][]=$v;
						continue;
					}
					foreach($v['type'] as $v2){
						$res[$v2['id']][]=$v;
					}
				}
					unset($v);
					$live_pinfo=$res;
			}
		}else{
			$live_pinfo=array();
		}
		$jsonArray['code'] = 'A00006';
//		print "<pre>";
//		print_r($res);
//		exit;
		$jsonArray['data']['live_pinfo'] = $live_pinfo;
		$this->display($jsonArray, 'json');
	}
}

new GetHotLive(RADIO_APP_SOURCE);
?>