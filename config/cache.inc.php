<?php
define('CACHE_LIVE', 'S1');

define('CACHE_CITY', 'S2');

define('CACHE_WEBCAST', 'S3');

define('CACHE_DAILY', 'S4');

define('CACHE_RADIO', 'S5');

define('CACHE_PUBLIC', 'S0');

//-------------------------公共缓存定义---------------------------------------
//alias
define('MC_PUBLIC_ALIAS', 'srv.widget.main'); //登录用户信息缓存
//key
define('MC_PUBLIC_LOGINED_USERINFO', CACHE_PUBLIC . '_%s_user'); //登录用户信息缓存
//timeout
define('MC_TO_PUBLIC_LOGINED_USERINFO', 120); //登录用户信息缓存

define('MC_PUBLIC_USER_MARK_INFO', CACHE_PUBLIC . '_%s_user_mark'); //用户水印信息缓存

define('MC_PUBLIC_USER_MARK_INFO_TIME',600); //用户水印信息缓存有效期 10分钟
//-------------------------/公共缓存定义---------------------------------------

?>