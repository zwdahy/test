<?php
/**
 * 基础服务封装工具类
 */
include_once dirname ( dirname ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) ) . '/stdafx.php';

class SappBasic {
	/**
	 * 长连接转短链接
	 * @param string	$url	url地址
	 * @param int 		$uid	当前登录用户uid
	 * @param int		$appid	appid
	 * @param int		$appkey	appkey
	 * @return array('shortUrl', 'longUrl', 'type') or false
	 */
	public static function long2short($url, $uid, $appid, $appkey){
		$param = array(
			'uid'	=>$uid,
			'appid'	=>$appid,
			'appkey'=>$appkey,
			'cip'	=>tCheck::getIp(),	//请求接口的ip
		);
		
		$obj = clsFactory::create ( 'libs/basic/model', 'bmShortUrl', 'service' );
		$obj->setParas($param);
		return $obj->long2short($url);
	}
}
?>