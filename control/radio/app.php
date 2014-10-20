<?php
/**
 * 电台下载(control层)
 * 
 * @author zhanghu<zhanghu@staff.sina.com.cn>
 * @copyright(c) 2013-10-16, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioApp extends control{
	protected function checkPara(){
	}
	
	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$cur_rid = $this->para['rid'];	//当前电台id
		$data=$mRadio->formatScope($cur_rid);
		//页面title		
		$data['page_title'] = sprintf(RADIO_TITLE, "微电台客户端");
		$data['backurl'] = RADIO_URL.'/app';
//		print '<pre>';
//		print_r($data);
//		exit;
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
        DisplaySmarty::getSmartyObj();
        DisplaySmarty::$smarty->left_delimiter = '{=';
        DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/app.html' ), 'data' => $data ), 'html' );
	}
}

new RadioApp(RADIO_APP_SOURCE);
?>