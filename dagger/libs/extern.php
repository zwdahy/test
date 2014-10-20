<?php
define('EXTERN', 1);
define('PATH_ROOT_DAGGER', rtrim(dirname(__FILE__), "/") . "/../");
ob_start();
//系统初始化定义
include_once(PATH_ROOT_DAGGER . 'config/SysInitConfig.php');
//数据库初始化定义
include_once(PATH_ROOT_DAGGER . 'config/DBConfig.php');
include_once('basics.php');
