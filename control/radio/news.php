<?php
/**
 * 电台新闻列表页(control层)
 *
 * @author 张旭<zhangxu5@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."config/radioareaspell.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioNews extends control{
	protected function checkPara(){
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$this->para['cuserInfo'] = $person->currentUser();
	}
	protected function action(){
		$data['cuid'] = $this->para['cuserInfo']['uid'];
		$data['page_title'] = "微电台新闻页|微电台";
		$this->display ( array ('tpl' => array ('radio/news.html' ), 'data' => $data ), 'html' );
	}
}
new RadioNews(RADIO_APP_SOURCE);
?>
