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

class cron_update_transcoderadio {
	function __construct()
	{
		$this->objRadio  = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	}
	public function Run() {
		//执行开始时间
		list($usec, $sec) = explode(" ", microtime());
		$time_start =  ((float)$usec + (float)$sec);	
		$radioList = $this->objRadio->getAllRadioList();
        $res=array_merge($radioList['result']);
		if (count($res) >0){
			//将老的流存入seek表实现7天回看
			$this->objRadio->addSeek($res);
			//进行转码操作
			//$flag 为true 请勿随意改变 改版专用参数 清流后使用
			$flag = false;
			foreach($res as $val){
				$this->objRadio->transcodeRadio2($val,$flag);
				usleep(50000);		
			}
		}
		//执行结束时间
		list($usec, $sec) = explode(" ", microtime());
		$time_end = ((float)$usec + (float)$sec);
		//var_dump($time_end-$time_start,$result);
    }
}
$obj=new cron_update_transcoderadio();
$obj->Run();
?>
