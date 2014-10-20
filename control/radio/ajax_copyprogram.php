<?php

/**
 * Project:     radio
 * File:        ajax_copyprogram.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class CopyProgram extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['rid'] = intval(request::post('rid', 'STR'));
		$this->para['from_day'] = intval( str_replace('0','7', request::post('from_day', 'STR')));
		$this->para['to_day'] = trim(str_replace('0','7', request::post('to_day', 'STR')));

		//参数检测处理
		if(empty($this->para['rid']) || empty($this->para['from_day']) || empty($this->para['to_day'])) {
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
		$from_day =  $this->para['from_day'];
        $to_day = explode(',', $this->para['to_day']); //1,2,3,4,5 
        if(in_array($from_day, $to_day)){//防止to_day里有 from_day
            unset($to_day[array_search($from_day,$to_day)]);
        }
        $to_day = implode(',', $to_day);

		$radioinfo = $mRadio->copyProgram($rid, $from_day, $to_day);	
        exit;
        //1 is_del =1

        //2 insert


        //var_dump($programs, $program_types);

	}
}

new CopyProgram(RADIO_APP_SOURCE);
?>
