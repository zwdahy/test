<?php
//*******************************************************非动态池环境，模拟环境变量***********************************
/*
$_SERVER['SINASRV_DATA_DIR'] = "d:/work/data";
$_SERVER['SINASRV_CACHE_DIR'] = "d:/work/data";
$_SERVER['SINASRV_APPLOG_DIR'] = "d:/work/data";
$_SERVER['SINASRV_MEMCACHED_SERVERS'] = "127.0.0.1:11211";
$_SERVER['SINASRV_MEMCACHED_KEY_PREFIX'] = "test_";
$_SERVER['SINASRV_DB_HOST'] = "127.0.0.1";
$_SERVER['SINASRV_DB_PORT'] = "3306";
$_SERVER['SINASRV_DB_USER'] = "root";
$_SERVER['SINASRV_DB_PASS'] = "";
$_SERVER['SINASRV_DB_NAME'] = "test";
$_SERVER['SINASRV_DB_HOST_R'] = "127.0.0.1";
$_SERVER['SINASRV_DB_PORT_R'] = "3306";
$_SERVER['SINASRV_DB_USER_R'] = "root";
$_SERVER['SINASRV_DB_PASS_R'] = "";
$_SERVER['SINASRV_DB_NAME_R'] = "test";
*/
define('PROJECT_ID', 1);//项目ID，如没有项目ID，请到监控中心里创建项目
//*******************************************************运行平台********************************************************
if (isset($_SERVER['HTTP_APPNAME'])) {
    define('PLATFORM', 'sae');//可指定为sae，其他情况为dpool。
} else {
    define('PLATFORM', 'dpool');
}
//*******************************************************目录设置********************************************************
//框架基础路径设置
define('PATH_APP',          PATH_ROOT_DAGGER . 'app/');//应用所在目录
define('PATH_CONFIG',       PATH_ROOT_DAGGER . 'config/');//config目录
define('PATH_MODEL',        PATH_ROOT_DAGGER . 'model/');//model目录
define('PATH_LIBS',         PATH_ROOT_DAGGER . 'libs/');//框架库目录
define('PATH_LIBS_MODEL',   PATH_LIBS . 'model/');//框架model基类目录
define('PATH_LIBS_VIEW',    PATH_LIBS . 'view/');//框架view基类目录
define('PATH_LIBS_CTL',     PATH_LIBS . 'controller/');//框架controller基类目录
define('PATH_LIBS_PLT',     PATH_LIBS . 'pagelet/');
define('PATH_MYPLUGINS',    PATH_LIBS_VIEW . 'myplugins');//smarty扩展插件目录
//其他目录设置
define('PATH_SINA_SERVICE', 'SinaService/');
//数据目录
if (PLATFORM == 'sae') {
    define('PATH_DATA',         'saestor://data/');
    define('PATH_CACHE',        'saemc://cache/');
    //define('PATH_APPLOG',       'SaeStorage://log/');//SAE不提供追加写入，日志在程序中使用的sae_debug()
} else {
    define('PATH_DATA',         rtrim($_SERVER['SINASRV_DATA_DIR'], '/') . '/');//数据目录,格式：/data1/apache/data
    define('PATH_CACHE',        rtrim($_SERVER['SINASRV_CACHE_DIR'], '/') . '/');//缓存目录,格式：/data1/apache/cache
    define('PATH_APPLOG',        rtrim($_SERVER['SINASRV_APPLOGS_DIR'], '/') . '/');//缓存目录,格式：/data1/apache/applog
}
//*******************************************************应用名称********************************************************
define('APP_EXAMPLE','example');
define('APP_ADMIN','admin');
define('APP_TOOLS','tools');

//*******************************************************URL设置********************************************************
define('URL_ROOT', '/');

//*******************************************************数据库*******************************************************
define('DB_DEFAULT', 'default');//默认数据库
if (defined('QUEUE')) {
    define('DB_ERROR_MOD', 2);//数据库报错模模式:出现错误页面、停止执行|2:或略错误（后台队列运行时报错不停止执行）
} else {
    define('DB_ERROR_MOD', 0);//数据库报错模模式:出现错误页面、停止执行|2:或略错误（后台队列运行时报错不停止执行）
}
define('DB_CHARS', 'utf8');//数据库使用编码

//*******************************************************调试DEBUG********************************************************
if (in_array($_SERVER['SERVER_ADDR'], array('127.0.0.1', '10.44.6.241', '10.44.6.235', '10.210.227.172','10.69.21.108')) || !empty($_SERVER['HTTP_APPVERSION']) || (defined('QUEUE') && !defined('DEBUG_OFF'))) {
    if ($_COOKIE['dagger_env'] == 'test') {
        define('ENV', 'test');//dev:开发模式|test:测试模式|product:线上模式，使用数据库时：DBConfig中配置，如果没有配置dev和test的DB时，自动使用product
    } else {
        define('ENV', 'dev');//dev:开发模式|test:测试模式|product:线上模式，使用数据库时：DBConfig中配置，如果没有配置dev和test的DB时，自动使用product
    }
    define('DAGGER_DEBUG', 1);//DEBUG模式
} else {
    define('ENV', 'product');//dev:开发模式|test:测试模式|product:线上模式，使用数据库时：DBConfig中配置，如果没有配置dev和test时，自动使用product
}
define('DEBUG_OUT', 'phpfire');//DEBUG模式输出方式     'phpfire':phpfire输出|'':HTTP输入临时日志
define('DEBUG_DISABLED_OUT', '');//phpfire禁止输出类型  关掉平时用不到的debug信息，主要是因为机器不行太卡，多个可以用,隔开，也可通过firephp参数来开启
define('DEBUG_HTTP_OUT', '');//指定HTTP输入哪些日志，便于线上调试
define('DEBUG_ARG_NAME', 'debug');

//******************************************************MC设置**************************************************************
define('MC_DEFAULT', 'default');//默认使用的MC组
define('MC_KEY_PREFIX',$_SERVER['SINASRV_MEMCACHED_KEY_PREFIX']);//项目MC前缀，格式：project_

//******************************************************S3和CDN配置*********************************************************
define('S3_PROJECT_NAME', 'sandbox');
define('S3_BASE_URL', 'http://sinastorage.com');
define('S3_ACCESS_KEY', 'SYS00000000SANDBOX');
define('S3_SECRET_KEY', '1111111111111111111111111111111111111111');
define('SINA_EDGE_KEY', false);
define('SINA_EDGE_SECRET_KEY', false);

//******************************************************URL路由开关**********************************************************
define('ROUTER', 1); //1为打开，设置路由打开前请确认rewrite规则：RewriteEngine On RewriteCond %{REQUEST_URI} !^/(css(.*)|js(.*)|image(.*)|(.*).php)$ RewriteRule (.*) /index.php/$1 [L]已添加，具体配置请到RouterConfig进行设置

//******************************************************SSO认证**************************************************************
define('SINA_SSO_ENTRY', 'general'); //新浪通行证entry,根据自己项目更改
define('SINA_SSO_PIN', 'd25da9913239626241998b569717ea73'); //新浪通行证pin,根据自己项目更改

//******************************************************控制器\方法参数名\请求串*********************************************
//修改参数名需对应修改apache的ReWrite规则。
define('APP','p');
define('STATE','s');
define('ACTION','a');
define('QUERY','q');
define('APP_PREFIX', 'app_');

//******************************************************开始运行时间*********************************************************
define('STARTTIME', microtime(true));//程序开始运行时间

//******************************************************允许的POST REFERER***************************************************
$_SERVER['SERVER_ACCEPT_REFERER'] = array('sina.com.cn', 'wangxin.com');
?>
