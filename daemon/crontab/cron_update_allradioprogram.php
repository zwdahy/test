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

class cron_update_allradioprogram {
	function __construct() 
	{	
		$this->objRadio  = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	}
	public function Run() 

	{ //定期更新电台feedlist
		//@test 暂时关闭 等待新接口
       // $result = $this->objRadio->updateHotProgram2();
//        $result = $this->objRadio->updateHotProgramByDay2();//调试ok 
        $result = $this->objRadio->updateAllProgramNumber();//调试ok 


        //$result = $this->objRadio->updateAllRadioProgram();
        //以下可以干掉
        //$result = $this->objRadio->updateHotProgramByDay();
	}
}
$obj=new cron_update_allradioprogram();
$obj->Run();
?>
