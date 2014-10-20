<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty escape modifier plugin
 *
 * Type:     function<br>
 * Name:     include_http<br>
 * Purpose:  for include_http
 * @author   Wang Xin <wangxin3@staff.sina.com.cn>
 * @param string
 * @return string
 */
function smarty_function_include_http($params, &$smarty)
{
	extract($params);
	empty($cache_time) ? $cache_time=120 : 1;
	//下载页面
	require_once("HTTP/Request.php");
	$http = new HTTP_Request($url);
	$http->setMethod(HTTP_REQUEST_METHOD_GET);
	$http->_timeout = 5;
	//缓存页面
	if ($cache_mod == "file")
	{
		//文件缓存
		require (dirname(__FILE__)."/../../../config/config.inc.php");
		require_once (PEAR_INCLUDE_DIR."Cache/Lite.php");
		$cache_dir = CACHE_DIR."/http_cache/";
		if (!is_dir($cache_dir))
		{
			mmkdir ($cache_dir);
		}
		$options = array(
		    'cacheDir' => $cache_dir,
		    'lifeTime' => $cache_time
		);
		$cache_lite = new Cache_Lite($options);
		$cache_key = $url;
		$data = $cache_lite->get($cache_key);
		if (!empty($data))
		{
			return $data;
			exit;
		}
		if (!PEAR::isError($http->sendRequest()))
		{
			if ($http->getResponseCode() < 200 || $http->getResponseCode() >= 300)
			{
				$rs = "";
			}
			else
			{
				$rs = $http->getResponseBody(); //得到url里面的内容
			}
		}
		$cache_lite->save($rs);
	}
	else
	{
		//MEMCACHE缓存
		$memcache = new Memcache;
		$server_arr = explode (" ", $_SERVER['SINASRV_MEMCACHED_SERVERS']);
		foreach ($server_arr as $v)
		{
			list($server,$port) = explode(":",$v);
			$memcache->addServer($server, $port);
		}
		$cache_key = $url;
		$data = $memcache->get($_SERVER["SINASRV_MEMCACHED_KEY_PREFIX"]."INCLUDE_HTTP_".$cache_key);
		if (!empty($data))
		{
			return $data;
			exit;
		}
		if (!PEAR::isError($http->sendRequest()))
		{
			if ($http->getResponseCode() < 200 || $http->getResponseCode() >= 300)
			{
				$rs = "";
			}
			else
			{
				$rs = $http->getResponseBody(); //得到url里面的内容
			}
		}
		$memcache->set($_SERVER["SINASRV_MEMCACHED_KEY_PREFIX"]."INCLUDE_HTTP_".$cache_key, $rs, 0, $cache_time);
	}
	return $rs;
}

/* vim: set expandtab: */

?>
