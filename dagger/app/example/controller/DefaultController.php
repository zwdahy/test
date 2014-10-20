<?php
/**
 * @Copyright (c) 2009, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 测试
 * @author            **
 * @package            /
 * @version            $Id: $2010-3-3
 */

class DefaultController extends BaseController {

    public function view() {
        echo "hello world!";
    }

    public function image() {
        $image = new Image('http://sinastorage.com/sandbox/test.jpg');
        $image->rotate(50);
        $s3 = new S3();
        $result = $s3->plainWrite('test.jpg', $image->getContent(), $image->getSize(), $image->getMimeType());
        Common::debug($result);
    }

    public function s3() {
        $s3 = new S3();
        Common::debug($_FILES, 'empty');
        Common::debug($_POST, 'empty');
        if (!empty($_FILES['test'])) {
            $image = new Image($_FILES['test']['tmp_name']);
            //做些操作
            $image->rotate(30);
            $image->addWaterMark('新浪漫画', 30, 0.3);
            $result = $s3->plainWrite('test.jpg', $image->getContent(), $image->getSize(), $image->getMimeType());
            if (!$result) {
                Message::showError('传输失败');
            }
        }
        $this->setView('time', time());
        $this->display('s3.html');
    }

    public function http() {
        Http::get('www.sina.com.cn');
        Http::post('www.sina.com.cn', array('id'=>1));
        Http::head('www.sina.com.cn');
    }

    public function db() {
        $db = new BaseModelDB();
        $rs = $db->getData("SELECT * FROM `test`", 10);
    }

    public function mc() {
        $mc = new MyMemcache();
        $mc->get('abc');
        $mc->set('abc', '1');
        $mc->increment('abc', 3);
        $mc->decrement('abc');
    }

    public function login() {
        $ssoClient = new SSOClient;
        $loginStat = $ssoClient->isLogined();
        if ($loginStat) {
            $sinaSso = new SSOCookie;
            $sinaSso->getCookie($result);
            $this->adminUserName = $result['email'];
            $this->user = $result;
            $this->setView('username', $this->adminUserName);
            var_dump($this->user);
        } else {
            $this->display('login.html');
            exit;
        }
    }

    public function logout() {
       return $this->redirectTo('https://login.sina.com.cn/sso/logout.php?r=http://local.sina.com.cn/default/login');
    }

    public function pagelet() {
        $this->display('pagelet.html');
    }
}
