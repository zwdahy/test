<?php
/**
 * 电台排行榜(control层)
 *
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";
include_once SERVER_ROOT . "config/area.php";


//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}

class RadioRank extends control{
	protected function checkPara() {
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
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$data=$mRadio->formatScope();
		$data['page_title'] = sprintf(RADIO_TITLE, "微电台节目推荐");
		//获取电台节目排行榜
		$program = $mRadio->getHotProgramByDay2();
		if(!empty($program)){
			$data['program'] = array_slice($program,0,10);
		}else{
			$data['program'] = array();
		}
//		print '<pre>';
//		print_r($data);
//		exit;
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/recommend.html' ), 'data' => $data ), 'html' );
	}
}
new RadioRank(RADIO_APP_SOURCE);
?>