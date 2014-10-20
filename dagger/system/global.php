<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 后台运行主程序
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 15:03
 * @version         Id: 0.9
*/
set_time_limit(0);
set_magic_quotes_runtime(0);
declare(ticks = 1);

if ($_SERVER['HTTP_HOST']) {
    //防止被http调用
    exit();
}
define('QUEUE', 1);

if(strpos(PHP_OS, 'WIN') !== false || in_array($_SERVER['HOSTNAME'], array('vm12060035'))) {
    define('PATH_CONF', dirname(__FILE__) . '/DAGGER_TEST_CONFIG');
} else {
    define('PATH_CONF', dirname(__FILE__) . '/DAGGER_SINASRV_CONFIG');
}
$_SERVER = array_merge($_SERVER, parse_ini_file(PATH_CONF));//模拟获取环境变量

define('PATH_ROOT_DAGGER', rtrim(dirname(__FILE__), "/") . "/../");
require PATH_ROOT_DAGGER . 'config/SysInitConfig.php';//系统define
require PATH_LIBS . 'basics.php';//__autoload函数
require PATH_ROOT_DAGGER . "config/DBConfig.php";//载入数据库配置

array_shift($argv);
if (!empty($argv)) {
    foreach ($argv as $k => $arg) {
        preg_match('/--(\w+)(?:=([\w,-]+))?/', $arg, $match);
        $_GET[$match['1']] = $match['2'];
        $match['1'] === DEBUG_ARG_NAME && $pos = $k;
    }
}

if (isset($_GET[DEBUG_ARG_NAME])) {// 提取debug单数并从argv中删除
    $second = array_splice($argv, $pos);
    array_shift($second);
    $argv = array_merge($argv, $second);
} else {
    define('DEBUG_OFF', 1);
}

function daggerSignalHandler($signal){
    switch($signal) {
        case SIGTERM:
            Message::showError('Caught SIGTERM');
            exit;
        case SIGKILL:
            Message::showError('Caught SIGKILL');
            exit;
        case SIGINT:
            Message::showError('Caught SIGINT');
            exit;
    }
}

if(function_exists('pcntl_signal')){
    pcntl_signal(SIGTERM, 'daggerSignalHandler');
    pcntl_signal(SIGKILL, 'daggerSignalHandler');
    pcntl_signal(SIGINT, 'daggerSignalHandler');
}

?>
