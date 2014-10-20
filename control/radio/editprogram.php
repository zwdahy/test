<?php
/**
 * 电台列表页(control层)
 *
 * @date 2014-01-10
 * @author 丁润西6827<runxi@staff.sina.com.cn>
 * @copyright(c) 2010+4, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";
require_once(SERVER_ROOT.'dagger/libs/extern.php');


class EditProgram extends control{
	protected function checkPara(){
       // $this->para['rid'] = request::post('rid', 'INT');
		//@test 此处需要修改为post
        $this->para['rid'] = request::get('rid', 'INT');
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
		$data['page_title'] = sprintf(RADIO_TITLE, "管理节目单");
		$data['backurl'] = RADIO_URL.'/editprogram?rid='.$cur_rid;
		//echo "<pre>";
		//print_r($data);
		//exit;
		/*
        $radio_info = $mRadio->getRadioInfoByRid(array($cur_rid));
        $cur_radioInfo = $radio_info['result'][$cur_rid];

		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$curruserinfo = $person->
		$cuid = !empty($this->para ['cuserInfo']['uid']) ? $this->para ['cuserInfo']['uid'] : 0;	//当前登录用户id
		$curruserinfo = !empty($this->para ['cuserInfo']) ? $this->para ['cuserInfo'] : array();	//当前登录用户信息
		//当前用户身份
		$admin_id = $mRadio->getAllPowerList();
		$admin_id = $admin_id['result'];
		
		$cur_radioInfo['power'] = 'visit';
        $isCurrentDj = $mRadio->isCurrentDj($cuid,$cur_rid);
		if(($cuid > 0 && $cuid == $cur_radioInfo['admin_uid']) || in_array($cuid,$admin_id)){
			$cur_radioInfo['power'] = 'admin';
		}
		if($isCurrentDj !== false && $cur_radioInfo['power'] == 'visit'){
			$cur_radioInfo['power'] = 'djonline';
		}
		*/
        if($data['curruserinfo']['power'] == 'visit'){
            header("Content-type: text/html; charset=utf-8");
            $url = 'http://weibo.com/login?url='.urlencode("http://radio.weibo.com/editprogram?rid=$cur_rid&rnd=".time());
            echo '您没有编辑电台节目单的权限，请用管理权限登录微博后才可操作 新浪微博登陆：<a href="'.$url.'" >http://weibo.com/</a><br/>';
            exit('You do not have the permission to edit this page, please login first. <a href="'.$url.'" >http://weibo.com/</a>');
        }
        include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
        DisplaySmarty::getSmartyObj();
        DisplaySmarty::$smarty->left_delimiter = '{=';
        DisplaySmarty::$smarty->right_delimiter = '=}';
/*
        $data['servertime'] = time();
        $data['rid'] = $cur_rid;
        $data['radio_info'] = $radio_info;
        $data['curruserinfo'] = $curruserinfo;
        $radio_info['radio_url'] = RADIO_URL."/".$radio_info['province_spell'].'/'.$radio_info['domain'];
        $data['radioInfo'] = $radio_info;
        $data['cuid'] = $cuid;
        if(empty($cuid)) {
            $islogin = 0;
        } else {
        $islogin = 1;
        }
        $data['islogin'] = $islogin;
*/

		$this->display ( array ('tpl' => array ('radio/editprogram.html' ), 'data' => $data ), 'html' );
	}
}

new EditProgram(RADIO_APP_SOURCE);
?>
