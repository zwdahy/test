<?php
/**
 * 电台路由
 * 
 *
 * @package
 * @author 高超<gaochao@staff.sina.com.cn>
 */
define('SERVER_ROOT', dirname(__FILE__).'/');
define('CLASS_PATH','service/radio/');
//项目相关配置导入
require_once(SERVER_ROOT.'config/config.php');
require_once(SERVER_ROOT.'config/errorcode.php');
require_once(SERVER_ROOT.'tools/check/Check.php');
require_once(PATH_ROOT . 'tools/image/ftImage.php');
require_once(SERVER_ROOT.'dagger/libs/extern.php');

global $GLB_USERSETAPIDOMAIN;
$GLB_USERSETAPIDOMAIN = 0;

define('SAPPS_VERSION','V4');

//取得要跳转的页面
$page = $GLOBALS['URL_PATH'];
if($page == '' || $page == '/'){
    $page = '/area';
//    $page = '/index';
}
$page_arr = explode("/",$page);
$dir = $page_arr[1];

$file = isset($page_arr[2]) ? $page_arr[2] : 'index';
//特殊域名处理
$domain = $_SERVER['HTTP_HOST'];
if($domain === 'radio.t.sina.com.cn' || $domain === 'radio.weibo.com' || $domain === 'radio.new.weibo.com'){
	//电台域名处理
    if($page == '/index.php'){
        require_once(SERVER_ROOT.'control/radio/index.php');
        exit;
    }elseif($page == '/ajax_login.php'){ //判断登录跳转页面
    	require_once(SERVER_ROOT.'control/ajax_login.php');
		exit;
	}elseif($page == '/upimgback.html'){ //转入到公告登录页面
		require_once(SERVER_ROOT.'control/upimgback.html');
	}elseif($page == '/logout.php'){ //退出
		require_once(SERVER_ROOT.'control/logout.php');
	}elseif($page == '/error.html'){ //错误页面
		require_once(SERVER_ROOT.'control/error.html');
		exit;
	}else{
		if($dir == 'area'){
			require_once(SERVER_ROOT.'control/radio/areaindex.php');
			exit;
		}
//		if($dir == 'collection'){
//			require_once(SERVER_ROOT.'control/radio/collection.php');
//			exit;
//		}
//		if($dir == 'help'){
//			require_once(SERVER_ROOT.'control/radio/help.php');
//			exit;
//		}
		if($dir == 'rank'){
			require_once(SERVER_ROOT.'control/radio/rank.php');
			exit;
		}
//		if($dir == 'news'){
//			require_once(SERVER_ROOT.'control/radio/news.php');
//			exit;
//		}
		if($dir == 'pages'){
			require_once(SERVER_ROOT.'control/radio/pages.php');
			exit;
		}else{
			$pri_action=array('app', 'editprogram','seek','search','recommend','myradio');
			if (in_array($dir,$pri_action)){
				$dir = strtolower($dir);
				require_once(SERVER_ROOT.'control/radio/'.$dir.'.php');
				exit;
			}
		}
		//转发
		if((substr($page,-4) == '.php')){
			if(file_exists(SERVER_ROOT.'control/radio'.$page)){
				require_once(SERVER_ROOT.'control/radio'.$page);
				exit;
			}else{
				header("Location:" . T_URL . "/sorry");
				exit;
			}
		}else{
			require_once(SERVER_ROOT.'control/radio/index.php');
			exit;
		}
	}
}

//通用路径处理
if(file_exists(SERVER_ROOT.'control/'. $dir . "/" . $file . '.php')){
	require_once(SERVER_ROOT.'control/'. $dir . "/" . $file .'.php');
    exit;
}

//转发
if((substr($page,-4) == '.php' && strstr($page,'.')<strlen($page)-4) || $page == '/upimgback.html') {
	if(file_exists(SERVER_ROOT.'control'.$page)){
		require_once(SERVER_ROOT.'control'.$page);
	}else{
		header("Location:" . T_URL . "/sorry");
	}
}
?>
