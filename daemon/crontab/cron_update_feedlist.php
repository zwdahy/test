<?php
set_time_limit ( 0 );
include_once dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/stdafx.php';

define('CLASS_PATH','service/radio/');
//define('SERVER_ROOT', '/data1/www/htdocs/service.t.sina.com.cn/service/sapps/');
if(!defined('SERVER_ROOT')){
	define('SERVER_ROOT', PATH_ROOT.CLASS_PATH);	
}
include_once PATH_ROOT.CLASS_PATH.'config/config.php';
include_once PATH_ROOT.CLASS_PATH.'config/radioconf.php';
include_once PATH_ROOT.CLASS_PATH.'config/cache.inc.php';
include_once PATH_ROOT.CLASS_PATH.'config/errorcode.php';

class cron_update_feedlist {
	function __construct() 
	{	
		$this->objRadio  = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	}
	public function Run() { 
		//定期更新电台feedlist
		//执行开始时间
		list($usec, $sec) = explode(" ", microtime());
		$time_start =  ((float)$usec + (float)$sec);			
		
		$result = $this->objRadio->updateAllFeed();
		//$result = $this->objRadio->updateAllProgramFeed();
		
		//执行结束时间
		list($usec, $sec) = explode(" ", microtime());
		$time_end = ((float)$usec + (float)$sec);

//		var_dump($time_end-$time_start,$result);
	}
}
$obj=new cron_update_feedlist();
$obj->Run();
?>
