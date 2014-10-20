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

class cron_update_weibolist {
	function __construct() 
	{	
		$this->objRadio  = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	}
	//定期更新所有电台的最新微博信息
	public function Run(){
		$radioList = $this->objRadio->getRadioList();
	
		$tem_arr = array();
		foreach($radioList['result'] as $value){
			if(!empty($value)){
				$new_arr_list = array_merge($tem_arr, $value);
				$tem_arr = $new_arr_list;
			}
		}
		
		if (count($new_arr_list) >0){
			$this->objRadio  = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			foreach($new_arr_list as $val){
				
				$key_word = $val['tag'];
				$search_type = $val['search_type'];
				$province_spell = $val['province_spell'];
				$domain = $val['domain'];			
				$result = $this->objRadio->updateCrontabWeiboCache($key_word,$province_spell,$search_type,$domain);
				usleep(200);		
			}
		}		
		
		//$result = $this->objRadio->updateFeedList();
	}
}
$obj=new cron_update_weibolist();
$obj->Run();
?>