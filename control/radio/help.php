<?php
/**
 * 电台帮助页(control层)
 * 
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."config/radioareaspell.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioHelp extends control{
	protected function checkPara(){
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		//假数据
		$this->para['cuserInfo'] = $person->currentUser();		
	}
	
	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		
		$data['cuid'] = $this->para['cuserInfo']['uid'];
		//页面title		
		$data['page_title'] = sprintf(RADIO_TITLE, "使用帮助");
		$this->display ( array ('tpl' => array ('radio/help.html' ), 'data' => $data ), 'html' );
	}
}

new RadioHelp(RADIO_APP_SOURCE);
?>