<?php
/**
 * 电台page跳转页(control层)
 * 
 * @author 白珅<baishen@staff.sina.com.cn>
 * @copyright(c) 2013, 新浪网 MiniBlog All rights reserved.
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


class RadioPages extends control{
	protected function checkPara(){
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

		$page_arr = explode("/",$GLOBALS['URL_PATH']);
		$page_id = $page_arr[2];
		$rid = intval(substr($page_id, 7));

		$redirectUrl = "http://radio.weibo.com/";

		if ($rid != 0) {
			$args = array(
				'order_field' => "",
				'order' => "",
				'search_key' => "rid",
				'search_type' => "=",
				'search_value' => $rid,
				'page' => "",
				'pagesize' => ""
			);
			$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$result = $obj->getRadio($args);

			if ($result['errorno'] == 1 && !empty($result['result'])) {
				$province = $result['result'][0]['province_spell'];
				$domain = $result['result'][0]['domain'];

				$redirectUrl = "http://radio.weibo.com/".$province."/".$domain;
			}
		}

		header('Location: '.$redirectUrl);
		exit();
	}
}

new RadioPages(RADIO_APP_SOURCE);
?>
