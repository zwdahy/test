<?php

/**
 * Project:     radio
 * File:        ajax_getnotice.php
 * 
 * 获取电台公告
 * 
 * @copyright sina.com
 * @author 刘玉刚 <yugang2@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
include_once SERVER_ROOT . "tools/rent_house.php";
class GetNotice extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
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
		$result = array();		
        $params = array();
		//获取电台公告信息
		$noticeinfo = $mRadio->getRadioNotice();
		$week_notice = array();
		if(!empty($noticeinfo)){
			$j = 0;
			foreach($noticeinfo as $key=>$val){
				$start_time = $val['notice_start_time'];
				$end_time = $val['notice_end_time'];
				$notice_content = $val['notice_content'];
				$htmlParserModel=new HtmlParserModel($notice_content);
				$tmp=$htmlParserModel->child[1]->child[0];
				$notice_href=$tmp->attribute['href'];
				$notice_target=$tmp->attribute['target'];
				$notice_value=$tmp->child[0]->value;
				$week_day_arr = explode(',',$val['week_day']);
				foreach($week_day_arr as $k=>$v){
					$week_notice[$j]['week_day'] = $v;
					//转换成当天的时间time
					$week_notice[$j]['notice_starttime'] = date(('Y-m-d H:i:s'),strtotime(date('H:i:s',strtotime($start_time))));
					$week_notice[$j]['notice_endtime'] = date(('Y-m-d H:i:s'),strtotime(date('H:i:s',strtotime($end_time))));
					$week_notice[$j]['notice_content'] = $notice_content;
					$week_notice[$j]['notice_href'] = $notice_href;
					$week_notice[$j]['notice_target'] = $notice_target;
					$week_notice[$j]['notice_value'] = $notice_value;
					$week_notice[$j]['sort'] = $val['sort'];
					$j++;
				}
			}
		}
		$current_date_week =date('w');
		//重新按照星期整合后的公告数组
		$data=array();
		if(!empty($week_notice)){
			foreach($week_notice as $key=>$value){
				//首先判断 星期 和当天的是否是一天，然后在按照时间显示公告
				if($value["week_day"]==$current_date_week&&strtotime($value["notice_endtime"])>=time()&&strtotime($value["notice_starttime"])<=time()){
					unset($value["notice_endtime"]);
					unset($value["notice_starttime"]);
					$data[]=$value;
				}
			}
		}
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = $data;
		$this->display($jsonArray, 'json');
	}
}

new GetNotice(RADIO_APP_SOURCE);
?>