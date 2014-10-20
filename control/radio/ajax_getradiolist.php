<?php

/**
 * Project:     Fangtan
 * File:        ajax_getradiolist.php
 * 
 * 获取电台列表
 * 
 * @package 
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */

class RadioList extends control {
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
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}

		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $mRadio->getRadioList();
		if($result['errorno'] != 1) {
			$data['code'] = 'C00001';
		} else {
			$data['code'] = 'A00006';
			$data['data'] = array(
				'radio' =>$result['result'],
				'recmmend'=>0
			);
		}
		
		$this->display($data, 'json');
	}
}

new RadioList(RADIO_APP_SOURCE);
?>