<?php
/**
 * 程序主入口
 * 接收两个参数s(state)/a(action)
 */
ob_start();
define('PATH_ROOT_DAGGER', rtrim(dirname(__FILE__), "/") . "/");

/**
 * Initial System Configure
 */

//系统define
require PATH_ROOT_DAGGER . 'config/SysInitConfig.php';

//__autoload函数
require PATH_LIBS . 'basics.php';

//载入数据存储配置
require PATH_ROOT_DAGGER . "config/DBConfig.php";

//静态URL解析规则
BaseModelRouter::route();

$class = BaseModelCommon::getFormatName($_GET[STATE], 'class');
$class .= 'Controller';
$controller = new $class($_GET[STATE], $_GET[ACTION]);
$controller->runCommand();
BaseModelCommon::debug(Common::getRunTime(), '页面执行时间');
BaseModelCommon::sendOnlineDebug();
ob_end_flush();
