<?php
/**
 * 图片处理数据层
 * 
 * @author 刘焘<liutao3@staff.sina.com.cn>
 * @version 1.0 2010-10-13
 * @copyright 
 * @package data
 * @example
 */
include_once PATH_ROOT.'framework/datadriver/http/tHttp.php';
class dImage extends data{

	const PHOTO_URL_CRC = 'http://ww%d.sinaimg.cn/%s/%s.%s';		// 返回getUrlByPids生成的图片地址
	const PHOTO_URL = 'http://ss%d.sinaimg.cn/%s/%s&690';		// 返回getUrlByPids生成的图片地址
	const IMAGE_IURL_UPLOAD = 'http://upload.t.sinaimg.cn/interface/pic_upload.php?app=miniblog&s=php';		// 上传图片接口（内网彩信）
	const WATER_MARK = 'weibo.com';		// 主域名
	const WATER_STATUS = 0;		// 调用接口时传参状态
	const CURL_UPLOAD_IMAGE_TIMEOUT = 20;		// 超时时间
	const IMAGE_URL_DELETE = 'http://admin.photo.api.matrix.sina.com.cn/delete_pic/delete_pic.php?app=miniblog&pid=%s';		// 删除图片接口地址
	const IMAGE_URL_DELETE_NEW = 'http://admin.t.sinaimg.cn/pic_admin.php?op=1&app=miniblog&pids=%s';	// 删除图片新接口
	const MINIBLOG_KEY = '5HvYbgNp=YhAOjd.Wd^?\;y0ey%ZUV6G';

	/**
	 * 通过pid列表，取得图片完整url地址列表
	 *
	 * @param	array	$pids	图片pid列表
	 * @param	string	$type	图片大小类型
	 * @return	array | false
	 */
	public function getUrlByPids($pids, $type='square') {
		if(empty($pids) || ! is_array($pids) || empty($type)) return false;
		
		$result = array();
		foreach($pids as $pid) {
			if ($pid[9] == 'w') {	// 代表新接口
				// 新系统显示规则，注意新系统用的crc32做的域名哈希
				if($type == "orignal") {
					$type == "large";
				}
				$hv = sprintf("%u", crc32($pid));
				$zone = fmod(floatval($hv) ,4) + 1;
				$ext = ($pid[21] == 'g' ? 'gif' : 'jpg');
				$result[$pid] = sprintf(self::PHOTO_URL_CRC, $zone, $type, $pid, $ext);
			} else {
				$num = (hexdec(substr($pid, -2)) % 16) + 1;
				$result[$pid] = sprintf(self::PHOTO_URL, $num, $type, $pid);
			}
		}
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
	public function uploadImage($filename, $pictype='jpg', $uid='', $url=self::IMAGE_IURL_UPLOAD, $ip='') {
		if(empty($filename)) return false;
		$cuid = (is_numeric($uid) && strlen($uid) <=10 && strlen($uid)>=5) ? $uid : $_POST['uid'];
		$uniqueid = uniqid();
		if($pictype == 'png' || $pictype == 'PNG') $pictype = "jpg"; //解决curl在5.2.13以下版本无法识别png图片的Content-Type问题
		$url .= $this->getSecretUrl($cuid);
		
		//处理上传图片名
		if($pictype == "jpg"){
			$fields['pic1'] =  '@'.realpath($filename).";image/jpeg";
		}else{
			$fields['pic1'] =  '@'.realpath($filename).";image/gif";
		}
		$fields['uid']  =  $cuid;
		$fields['marks']  =  self::WATER_STATUS;
		
		$userpwd = ADMIN_EMAIL.":".ADMIN_PWD;
		$tHttp = new tHttp();
		$content = $tHttp ->requestWithPost($url,$fields,self::CURL_UPLOAD_IMAGE_TIMEOUT,$userpwd,'post',true);

		$filesize = filesize($filename);

	    unlink($filename);    //删除临时图片文件

		$picDatas = unserialize($content);
		if($picDatas['pics']['pic_1']['ret'] == 1) {
			return $picDatas['pics']['pic_1']['pid'];
		}else{
			return false;
		}
	}

	/**
	 * 删除图片
	 *
	 * @param	array	$pids	图片id列表
	 * @return	boolean | false
	 */
	public function delImage($pids) {
		if(empty($pids)) return false;

		$new_pids = array();	//新接口pid
		$old_pids = array();	//老接口pid
		foreach($pids as $v) {
			if($v[9] == 'w') {
				$new_pids[] = $v;
			}else{
				$old_pids[] = $v;
			}
		}

		if(!empty($new_pids)) {
			//新接口
			$url = sprintf(self::IMAGE_URL_DELETE_NEW, implode(',', $new_pids));
			$content = $this->requestGet ( $url );

			$delInfo = json_decode($content, true);
			if($delInfo['ret'] != 1) {
				return false;
			}
			return true;
		}

		if(!empty($old_pids)) {
			$url = sprintf(self::IMAGE_URL_DELETE, implode('|', $old_pids));

		    $fields['app'] = 'miniblog';
		    $fields['pid'] = implode('|', $pids);

			$userpwd = ADMIN_EMAIL.":".ADMIN_PWD;
			$tHttp = new tHttp();
			$content = $tHttp ->requestWithPost($url,$fields,self::CURL_UPLOAD_IMAGE_TIMEOUT,$userpwd,'post',true);

			$re = @explode(';', $content);
			foreach($re as $dv) {
				if(empty($dv)) continue;
				list($dkey, $dval) = @explode('=', $dv);
				$delInfo[$dkey] = $dval;
			}
			if($delInfo['ret'] != 1) {
				return false;
			}
			return true;
		}
	}

	/**
	 * 获取图片信息
	 *
	 * @param string $imageUrl 图片url
	 * @return array|false
	 */
	public function getPicInfo($imageUrl) {
		if(empty($imageUrl)) return false;
		$timeout = 10;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $imageUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_USERAGENT, 'SinaWeiboBot'); 
		$re = curl_exec($ch);
		if($re){
			$info = curl_getinfo($ch);
		}else
			return false;
		return 	$info;
	}

	/**
	 * CURL方式上传图片
	 *
	 * @param   string $uploadfile  图片路径
	 * @param   string $tmpfile  临时图片路径
	 * @param	integer $uid 当前用户UID
	 * @param	string $url 图片上传地址
	 * @return  图片id
	 */
	public function curlUploadImage($filename,$pictype='jpg', $uid='', $url=self::IMAGE_IURL_UPLOAD) {
		if(empty($filename)) return false;
		if(!file_exists($filename)) return false;

		$cuid = (is_numeric($uid) && strlen($uid) <=10 && strlen($uid)>=5) ? $uid : $_POST['uid'];
		$url .= $this->getSecretUrl($cuid);
		
		if($pictype == "jpg"){
			$fields['pic1'] =  '@'.realpath($filename).";image/jpeg";
		}else{
			$fields['pic1'] =  '@'.realpath($filename).";image/gif";
		}
	    $fields['uid']  =  $cuid;
	    $fields['marks']  =  self::WATER_STATUS;
		if(is_array($GLOBALS['IMAGE_WATERMARK']) && in_array($cuid, $GLOBALS['IMAGE_WATERMARK'])) {
			$fields['wm']  =  2;
		}

		$userpwd = ADMIN_EMAIL.":".ADMIN_PWD;
		$tHttp = new tHttp();
		$content = $tHttp ->requestWithPost($url,$fields,self::CURL_UPLOAD_IMAGE_TIMEOUT,$userpwd,'post',true);

		$filesize = filesize($filename);

	    unlink($filename);    //删除临时图片文件

		$picDatas = unserialize($content);
		if($picDatas['pics']['pic_1']['ret'] == 1) {
			return $picDatas['pics']['pic_1']['pid'];
		}else{
			return false;
		}
	}

	/**
	 * 生成私有认证验证码
	 *
	 * @param   int $uid  用户id
	 * @return  生成的url串
	 */
	public function getSecretUrl($uid){
		$g_time  = time();
		$g_skey  = self::MINIBLOG_KEY;
		$g_app   = "miniblog";
		$g_rand  = mt_rand(0, 10000);
		$token = $g_time . $g_rand;
		$sess = md5($uid . $token . $g_skey);
		$url = "&p=1&uid=" . $uid . "&token=" . $token . "&sess=" .$sess;
		return $url;
	}
}