<?php
include_once 'cache.inc.php';
define('DEFAULT_APP_ID', 68);

$find = strpos($_SERVER['HTTP_HOST'],'t.sina.com.cn');
if($find !== false) {
	define('SAPPS_LEVEL_DOMAIN','t.sina.com.cn');
} else {
	define('SAPPS_LEVEL_DOMAIN','weibo.com');
}

//------------------------------公共部分定义------------------------------------
//页面公共title定义
define("GLOBAL_TITLE", "新浪微博-随时随地分享身边的新鲜事儿");
define("GLOBAL_NAME", "微博");

define("T_URL", "http://weibo.com");
define("T_DOMAIN","weibo.com");
define("SERVICE_DOMAIN","service.weibo.com");

define('CAST_URL', 'http://live.weibo.com');//直播
define('RADIO_URL', 'http://radio.weibo.com');//电台
define('LIVE_URL', 'http://talk.weibo.com/');//访谈
define('GLOBAL_DAILY_DOMAIN', 'http://apps.weibo.com/daily');
define("DOMAIN_TYPE",0);
define("GLOBAL_CSS_PATH_V3", "http://timg.sjs.sinajs.cn/t35/style");
define("GLOBAL_JS_PATH_V3", "http://tjs.sjs.sinajs.cn/t35/miniblog");
define("GLOBAL_CSS_PATH_SRV", "http://timg.sjs.sinajs.cn/t35/yunyingstyle");
define("GLOBAL_JS_PATH_PLATFORM", "http://tjs.sjs.sinajs.cn/t35/platform");

//V4  css&js&images
define('SAPPS_JS_PATH_V4','http://js.t.sinajs.cn/t4');
define('SAPPS_CSS_NAV_PATH_V4','http://img.t.sinajs.cn/t4/style/css/module/global/out_frame.css');
//JS版本号,ssojs路径
define('JS_CSS_VERSION_FILE', '/data1/www/privdata/t.sina.com.cn/version/top.txt');
$js_ver = trim(@file_get_contents(JS_CSS_VERSION_FILE));
define('JS_CSS_VERSION', ctype_digit($js_ver) ? $js_ver : date('YmdHi'));

define('GLOBAL_SSOJS_PATH', "http://js.t.sinajs.cn/t35/miniblog");

//老版微博使用的CSS/JS
define("GLOBAL_CSS_PATH", "http://timg.sjs.sinajs.cn/miniblog2style");
define("GLOBAL_SKIN_PATH", "http://timg0.sjs.sinajs.cn/miniblog2style");
define("GLOBAL_JS_PATH", "http://tjs.sjs.sinajs.cn/miniblog2");

define('ADMIN_UID',1797392503);        //微博小秘书uid
define('INTERFACE_SAFE_TIME',5);		//接口保护时间5秒
define('ADMIN_EMAIL',"weibozhuchiren@sina.cn");
define('ADMIN_PWD','zhuchiren5968');
//v5动态
define('RADIO_TPL_ID','1735204928691387');
define('RADIO_SUBJECT_ID','1016');

//定义请求来源域名
$GLOBALS['REFER_URL'] = array(
	't.sina.com.cn',
	'service.t.sina.com.cn',
	'talk.t.sina.com.cn',
	'live.t.sina.com.cn',
	'weibo.com',
	'service.weibo.com',
	'talk.weibo.com',
	'live.weibo.com',
	'live.new.weibo.com',
	'talk.new.weibo.com',
	'radio.new.weibo.com',
	'service.new.weibo.com',
);

//退出接口
define('LOGOUT_URL','https://login.sina.com.cn/sso/logout.php');
//定义搜索接口
define('SEARCH_MBLOG_URL', "http://" . T_DOMAIN . "/k/%s");		// 微博客搜索的地址
define('SEARCH_GETDATA',      'http://miniblog2.match.sina.com.cn/miniblog/querytext.php?query=%s&start=%d&num=%d&absMark=%d&cuid=%d&sid=%d');	// 获取搜索数据接口
define('SEARCH_GETDATA_BY_SORT','http://58.63.238.237/miniblogdyn/querytext.php?query=%s&start=%d&num=%d&absMark=%d&sid=%d');	// 获取搜索数据接口

//APPid/模板id定义
define('MBLOG_APP_MBLOG', 1);// 微博客

//表情定义
$GLOBALS['EMOTION_TXT2ID']['zh-cn'] = array(
	'足球' => 'football',
	'哨子'=>'shao',
	'红牌' => 'redcard',
	'黄牌' => 'yellowcard',
	'哈哈' => 'laugh',
	'呵呵' => 'smile',
    '衰' => 'cry',
    '汗' => 'sweat',
    '爱你' => 'love',
    '嘻嘻' => 'tooth',
    '哼' => 'hate',
    '心' => 'heart',
    '晕' => 'dizzy',
	'怒' => 'angry',
    '蛋糕' => 'cake',
    '花' => 'flower',
	'抓狂' => 'crazy',
	'困' => 'sleepy',
	'干杯' => 'cheer',
	'太阳' => 'sun',
	'下雨' => 'rain',
	'泪' => 'sad',
	'月亮' => 'moon',
	'猪头' => 'pig',
	'蜡烛' => 'candle',
	'伤心' => 'unheart',
	'风扇' => 'fan',
	'冰棍' => 'ice',
	'西瓜' => 'watermelon',
);
$GLOBALS['EMOTION_TXT2ID']['all'] = array(
	'粽子' => 'dumpling',
	'足球' => 'football',
	'哨子'=>'shao',
	'红牌' => 'redcard',
	'紅牌' => 'redcard',
	'黄牌' => 'yellowcard',
	'黃牌' => 'yellowcard',
	'綠絲帶' => 'green',
	'绿丝带' => 'green',
	'哈哈' => 'laugh',
	'呵呵' => 'smile',
    '泪' => 'sad',
    '淚' => 'sad',
    '汗' => 'sweat',
    '爱你' => 'love',
    '愛你' => 'love',
    '嘻嘻' => 'tooth',
    '哼' => 'hate',
    '心' => 'heart',
    '晕' => 'dizzy',
    '暈' => 'dizzy',
	'怒' => 'angry',
    '蛋糕' => 'cake',
    '花' => 'flower',
	'抓狂' => 'crazy',
	'困' => 'sleepy',
	'干杯' => 'cheer',
	'乾杯' => 'cheer',
	'太阳' => 'sun',
	'太陽' => 'sun',
	'下雨' => 'rain',
	'伤心' => 'unheart',
	'傷心' => 'unheart',
	'月亮' => 'moon',
	'猪头' => 'pig',
	'豬頭' => 'pig',
	'蜡烛' => 'candle',
	'蠟燭' => 'candle',
	'衰' => 'cry',
	'风扇'=>'fan',
	'風扇'=>'fan',
	'冰棍'=>'ice',
	'西瓜'=>'watermelon',
);

//内部接口可允许的访问ip
$GLOBALS['allow_ip'] = array(
// sso
	'10.55.+',	// dongtai
 	'10.54.+',	// dongtai
	'10.49.+',
	'10.71.+',	// dongtai
// wap | blue
	'172.16.+',
	'192.168.43.194',
// msn
	'218.30.+',
// 动态平台
	'10.29.10.+',
	'10.44.6.+',    
	'10.53.3.+',
	'10.69.6.+',      
	'10.6.63.+',
	'10.67.11.+',
	'10.69.16.+', 
	'10.69.17.+', 
	'10.67.16.+', 
	'10.67.17.+', 
	'10.73.11.+',


//dongtai  yangqiao for yanfa
	'10.67.11.+',
	'10.66.11.+',

	'10.71.2.+',  
	'10.71.10.+',
	'10.209.1.+',
	'10.218.+', //公司
	'202.85.139.186',	//香港服务器
	'10.255.0.179',	// 北美获取twitter信息
	'10.69.8.+',	// 手机服务VIP
	'10.69.2.+',	// 手机服务  
	'10.69.3.+',//内网授权ip
	'10.210.+',
	'172.16.113.78', 	//openapi测试机
	'172.16.113.76',	//openapi测试机
	'60.28.175.218',//邮件服务器
	'60.28.2.119',//邮件测试服务器
	'121.14.1.241',//邮件服务器

  	'10.67.14.25',//zhulei for open api 20100720 12:00
  	'10.67.14.26',
  	'10.67.14.27',
  	'10.67.14.28',
  	'10.67.14.29',
  	'10.67.14.30',
  	'10.67.14.31',
  	'10.67.14.32',
  	'10.67.14.33',
  	'10.67.14.34',
  	'10.67.14.35',

  	'10.55.40.98',//chenbo 0722 for open api  
  	'10.55.40.99',  
  	'10.55.40.100',

	'202.108.43.240', //动态平台公网出口vip
	'202.108.43.241',
	'202.108.43.242',
	'202.108.43.246',
	'202.108.43.253',
	'202.106.182.228',
	'60.28.175.177',
	'61.172.201.133',
	'61.172.201.137',
	'61.172.201.138',
	'61.172.201.140',
	'61.172.201.142',
	'61.172.201.239',
	'121.14.1.+',
	'58.63.234.253',
	'218.30.115.170',
	'218.30.115.169',
	'121.194.0.130',

  	'10.49.1.68',//tongyu need for map
  	'10.44.3.59',
  	'10.44.3.86',

  	'10.68.1.23',//search
  	'10.69.1.23',//neiwang
	'127.0.0.1',
	'10.73.15.+',//cuihan
		
	'10.73.19.+',//企业微博
	'10.73.13.+',
	'10.75.12.+',
	'10.75.14.+',
	'10.75.13.+',
	
	
	'10.215.20.+',//统计
	//数据部门调用
	'10.73.20.73',
	'10.72.20.73',
	//feed短链
	'10.73.14.+',
	'10.75.0.+',

	//Open API
	'10.73.+',
	'10.75.+',
	'10.75.2.+',
	'10.73.32.181', 
	'10.73.32.182', 
	'123.125.104.+',
	'61.135.152.+',
	'10.129.88.22',
	'10.129.88.23',
	'10.129.88.24',
	'10.75.2.80',
	'10.75.2.81',
	'10.75.24.103',
	'10.75.24.109',
	'10.73.89.86',
	'10.73.89.87',
	'10.75.28.36',
	'10.75.28.37',


	//tou ming dai li
	'10.75.28.21',
	'10.75.28.22', 
	'10.75.28.23', 
	'10.73.14.155',
	'10.73.32.175',
);
//-----------------------------------------------------------------------------

//-------------------------SAPPS下项目相关定义----------------------------------
define('OPENAPI_APP_KEY',2694189587);

//----------------------------访谈相关------------------

//------------------------------------------------------
//-----------------------------------------------------------------------------
//短链域名定义
$obj = clsFactory::create ( 'tools/analyze/model', 'mShortUrl', 'service' );
define('SHORTURL_DOMAIN',$obj->getDomainShort());
?>