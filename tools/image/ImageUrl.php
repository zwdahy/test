<?php
/**
 * 获取图片路径
 * @copyright	(c) 2009, 新浪网MiniBlog  All rights reserved.
 * @author		王占(wangzhan@staff.sina.com.cn)
 * @version		1.0
 * @package		Tools
 */
class ImageUrl {
	/**
	 * 返回图片地址
	 * @param String pid
	 * @param String type 图片规格
	 * @return String imageurl
	 */
	public function get_image_url($pid, $type = 'thumbnail') {
		$result = ftImage::getUrlByPids ($pid, $type);
		return $result; //接口返回数组
	}
	/**
	 * 获取icon地址
	 * @param $uid 用户uid
	 * @param $iconver 版本号
	 * @param $size 尺寸大小
	 * @return string icons
	 */
	public function get_icon_url($uid, $iconver, $size = 50) {
		$tpUid = $uid % 4 + 1;
		$icons = "http://tp{$tpUid}.sinaimg.cn/{$uid}/{$size}/{$iconver}";
		return $icons;
	}
	
	public function getPidFrmoUrl($url)
	{
		//exp:http://ss5.sinaimg.cn/thumbnail/6b957307t75287b096ce4&690
		$photo_url_formate = '/^http:\/\/ss(.*).sinaimg.cn\/(.*)\/(.*)&690/i';
		preg_match($photo_url_formate , $url, $re);
		if($re != false && is_array($re)){
			$pid = $re[3];
		}else{
			// exp:http://ww3.sinaimg.cn/small/57611ecfjw6dasv1p9dggj.jpg
			$pid = substr($url, strrpos($url, '/')+1,strrpos($url,'.')-strrpos($url,'/')-1);
		}
		return $pid;			
	}		
}

?>