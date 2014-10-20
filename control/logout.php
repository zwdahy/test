<?php

/**
 * Project:     Sapps
 * File:        logout.php
 * 
 * 注销登录
 * 
 * @link http://www.sina.com.cn
 * @package Sapps
 * @date 2010-08-30
 * @author wangchao <wangchao@staff.sina.com.cn>
 * @copyright (c) 2010, 新浪网 MiniBlog All rights reserved.
 * @version 1.0
 */

class Logout extends control {
	public function checkPara() {
		$this->para['backurl'] = request::get('backurl','STR');
	}

	public function action() {
		setcookie ("COOKIE_GLOBAL", "", time() - 3600, "/", COOKIE_DOMAIIN);
		setcookie ("islogin", "", time() - 3600, "/", COOKIE_DOMAIIN);
		
		if($this->para['backurl']) {
			$redirectUrl =$this->para['backurl'];
		} else {
			$redirectUrl = T_URL;
		}
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson','service');

		if($mPerson->isLogined()) {
			$mPerson->logout();
		}
		header("Location: $redirectUrl");
		exit;
	}
}

new Logout('srv.sapps');
?>