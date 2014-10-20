<?php
/**
 * 电台列表页(control层)
 *
 * @date 2010-09-01
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";



//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class MyRadio extends control{
	protected function checkPara(){
		//判断来源合法性
//		if(!Check::checkReferer()){
//			$this->setCError('M00004','Refer来源错误');
//			return false;
//		}
	}

	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}

		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$cuid=$mPerson->getCurrentUserUid();
		if(empty($cuid)){
			header("Location:http://weibo.com/sorry",true,302);
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$data=$mRadio->formatScope();
		$data['page_title'] = sprintf(RADIO_TITLE, "我的电台");
		$data['backurl'] = RADIO_URL.'/myradio';
		$collection = $mRadio->getCollectionList($cuid);
		$data['collection'] = array_values($collection);
		$data['collection_num'] = count($collection);
//		print '<pre>';
//		print_r($data);
//		exit;
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/myradio.html' ), 'data' => $data ), 'html' );
	}
}

new MyRadio(RADIO_APP_SOURCE);
?>
