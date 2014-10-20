<?php
/**
    * @Copyright (c) 2009, 新浪网运营部-网络应用开发部
    * All rights reserved.
    * 数据库，MC配置
    * @author          Xin Wang <wangxin3@staff.sina.com.cn>
    * @package         /config
    * @version         Id:
  */

//数据库：default   DB_{数据库}_{类型}_{主从}_{变量}
if (PLATFORM == 'sae') {
    define('DB_DEFAULT_PRODUCT_MASTER_HOST', SAE_MYSQL_HOST_M);
    define('DB_DEFAULT_PRODUCT_MASTER_PORT', SAE_MYSQL_PORT);
    define('DB_DEFAULT_PRODUCT_MASTER_USER', SAE_MYSQL_USER);
    define('DB_DEFAULT_PRODUCT_MASTER_PASS', SAE_MYSQL_PASS);
    define('DB_DEFAULT_PRODUCT_MASTER_DATABASE', SAE_MYSQL_DB);
    
    define('DB_DEFAULT_PRODUCT_SLAVE_HOST',  SAE_MYSQL_HOST_S);
    define('DB_DEFAULT_PRODUCT_SLAVE_PORT',  SAE_MYSQL_PORT);
    define('DB_DEFAULT_PRODUCT_SLAVE_USER',  SAE_MYSQL_USER);
    define('DB_DEFAULT_PRODUCT_SLAVE_PASS',  SAE_MYSQL_PASS);
    define('DB_DEFAULT_PRODUCT_SLAVE_DATABASE', SAE_MYSQL_DB);
} else {
    define('DB_DEFAULT_PRODUCT_MASTER_HOST', $_SERVER['SINASRV_DB_HOST']);
    define('DB_DEFAULT_PRODUCT_MASTER_PORT', $_SERVER['SINASRV_DB_PORT']);
    define('DB_DEFAULT_PRODUCT_MASTER_USER', $_SERVER['SINASRV_DB_USER']);
    define('DB_DEFAULT_PRODUCT_MASTER_PASS', $_SERVER['SINASRV_DB_PASS']);
    define('DB_DEFAULT_PRODUCT_MASTER_DATABASE', $_SERVER['SINASRV_DB_NAME']);
    
    define('DB_DEFAULT_PRODUCT_SLAVE_HOST', $_SERVER['SINASRV_DB_HOST_R']);
    define('DB_DEFAULT_PRODUCT_SLAVE_PORT', $_SERVER['SINASRV_DB_PORT_R']);
    define('DB_DEFAULT_PRODUCT_SLAVE_USER', $_SERVER['SINASRV_DB_USER_R']);
    define('DB_DEFAULT_PRODUCT_SLAVE_PASS', $_SERVER['SINASRV_DB_PASS_R']);
    define('DB_DEFAULT_PRODUCT_SLAVE_DATABASE', $_SERVER['SINASRV_DB_NAME_R']);
    
}

//+++++++++++++++++++++++++++++++++++华丽的分割线++++++++++++++++++++++++++++++++++++++++++
/*测试环境和正常环境使用库不一样时使用,SysInitConfig.php中ENV变量控制选择测试环境数据库
//数据库：default   test代表测试库
define('DB_DEFAULT_TEST_MASTER_HOST', 'localhost');
define('DB_DEFAULT_TEST_MASTER_PORT', 3306);
define('DB_DEFAULT_TEST_MASTER_USER', 'root');
define('DB_DEFAULT_TEST_MASTER_PASS', '');
define('DB_DEFAULT_TEST_MASTER_DATABASE', 'default');
define('DB_DEFAULT_TEST_SLAVE_HOST', 'localhost');
define('DB_DEFAULT_TEST_SLAVE_PORT',  3306);
define('DB_DEFAULT_TEST_SLAVE_USER',  'root');
define('DB_DEFAULT_TEST_SLAVE_PASS',  '');
define('DB_DEFAULT_TEST_SLAVE_DATABASE', 'default');
*/

//*******************************************************MC配置******************************************************
//第一个MC，名为：default
define("MC_DEFAULT_SERVERS", $_SERVER['SINASRV_MEMCACHED_SERVERS']);

//第二MC，名为：MARS
//define("MC_MARS_SERVERS", '192.168.1.1:11211 127.0.0.1:11211');
     
?>
