<?php
/**
 * 处理图片URL地址工具.根据新的url规则做处理.
 * 
 * @author 刘焘<liutao3@staff.sina.com.cn>
 * @version 1.0 2010-10-13
 * @copyright 
 * @package tools
 * @example 
 * 
 * 测试案例
 * 
 *	include_once PATH_ROOT . 'tools/image/fImage.php';
 *	fImage::getUrlByPids(array('6d0bf1e7492a5aea76c4f'));		// 传入图片ID获取URL
 *	fImage::getPicInfo('http://tp4.sinaimg.cn/1829499367/180/1287032361/1');		// 获取图片信息 传入图片URL
 *	fImage::uploadImage(file_get_contents('http://tp4.sinaimg.cn/1829499367/180/1287032361/1'), 'jpg', '1829499367');		// 上传图片
 *	// fImage::curlUploadImage($this->para['picfile']['name'],$this->para['picfile']['tmp_name'], $this->para['cuid']);		// 上传图片 只能针对POST的数据处理
 *	fImage::delImage(array('6d0bf1e7492a5aea76c4f'));		// 删除图片
 */

class fImage {

	const IMAGE_IURL_UPLOAD = 'http://upload.t.sinaimg.cn/interface/pic_upload.php?app=miniblog&s=php';		// 上传图片接口（内网彩信）

	/**
	 * 通过pid列表，取得图片完整url地址列表
	 *
	 * @param	array	$pids	图片pid列表
	 * @param	string	$type	图片大小类型
	 * @return	array | false
	 */
	public static function getUrlByPids($pids, $type = 'square') {
		if(!is_array($pids)) $pids = array($pids);
		if(empty($pids) || empty($type)) return false;
		$o_image = clsFactory::create ( CLASS_PATH . 'tools/image/data', 'dImage', 'service' );
		$result = $o_image->getUrlByPids($pids, $type);
		return $result;
	}

	/**
	 * 上传图片
	 *
	 * @param   string $picdata 图片信息 file_get_contents
	 * @param	string $pictype 图片类型
	 * @param	string $uid 用户ID
	 * @param	string $url 上传图片接口URL
	 * @param	string $ip 支持指定特定IP服务器
	 * @return  string 图片ID 新版本22位字符串 | false
	 */
	public static function uploadImage($picdata, $pictype = 'jpg', $uid = '', $url = self::IMAGE_IURL_UPLOAD, $ip = '') {
		if(empty($picdata) || empty($pictype)) return false;
		$o_image = clsFactory::create ( CLASS_PATH . 'tools/image/data', 'dImage', 'service' );
		return $o_image->uploadImage($picdata, $pictype, $uid, $url, $ip);
	}

	/**
	 * 删除图片
	 *
	 * @param	array $pids	图片id列表
	 * @return	boolean | false
	 */
	public static function delImage($pids) {
		if(!is_array($pids)) $pids = array($pids);
		if(empty($pids)) return false;
		$o_image = clsFactory::create ( CLASS_PATH . 'tools/image/data', 'dImage', 'service' );
		$bRet = $o_image->delImage($pids);
		return $bRet;
	}
	/**
	 * 获取图片信息
	 *
	 * @param string $imageUrl 图片url
	 * @return array|false
	 */
	public static function getPicInfo($imageUrl) {
		if(empty($imageUrl)) return false;
		$o_image = clsFactory::create ( CLASS_PATH . 'tools/image/data', 'dImage', 'service' );
		$info = $o_image->getPicInfo($imageUrl);
		return $info;
	}

	/**
	 * CURL方式上传图片
	 *
	 * @param   string $uploadfile  图片路径
	 * @param   string $tmpfile  临时图片路径
	 * @param	integer $uid 当前用户UID
	 * @param	string $url 图片上传地址
	 * @return  图片ID
	 */
	public static function curlUploadImage($uploadfile, $pictype='jpg', $uid = '', $url = self::IMAGE_IURL_UPLOAD) {
		if(empty($uploadfile)) return false;
		if(!file_exists($uploadfile)) return false;
		$o_image = clsFactory::create ( CLASS_PATH . 'tools/image/data', 'dImage', 'service' );
		return $o_image->curlUploadImage($uploadfile, $pictype, $uid, $url);
	}
}