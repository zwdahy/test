<?php
/**
 * BASE62 解析类，专门针对长mid与62进制互转，不适用其他操作
 * 
 * @copyright	(c) 2009, 新浪网MiniBlog  All rights reserved.
 * @author		王兆源 <zhaoyuan@staff.sina.com.cn> 朱建鑫 李如其
 * @version		1.0 - 2009-08-05
 * @package		Tools
 */

class base62Parse {
	
	private $string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	private $encodeBlockSize = 7;
	private $decodeBlockSize = 4;
	/**
	 * 将mid转换成62进制字符串
	 *
	 * @param	string	$mid
	 * @return	string
	 */
	public function encode($mid) {
		$str = "";
		$midlen = strlen($mid);
		$segments = ceil($midlen / $this->encodeBlockSize);
		$start = $midlen;
		for($i=1; $i<$segments; $i+=1) {
			$start -= $this->encodeBlockSize;
			$seg = substr($mid, $start, $this->encodeBlockSize);
			$seg = $this->encodeSegment( $seg );
			$str = str_pad($seg, $this->decodeBlockSize, '0', STR_PAD_LEFT) . $str;
		}
		$str = $this->encodeSegment( substr($mid, 0, $start) ) . $str;
		return $str;
	}

	/**
	 * 将62进制字符串转成10进制mid
	 *
	 * @param	string	$str
	 * @return	string
	 */
	public function decode($str, $compat=false ,$for_mid=true) {
		$mid = "";
		$strlen = strlen($str);
		$segments = ceil($strlen / $this->decodeBlockSize);
		$start = $strlen;
		for($i=1; $i<$segments; $i+=1) {
			$start -= $this->decodeBlockSize;
			$seg = substr($str, $start, $this->decodeBlockSize);
			$seg = $this->decodeSegment( $seg );
			$mid = str_pad($seg, $this->encodeBlockSize, '0', STR_PAD_LEFT) . $mid;
		}
		$mid = $this->decodeSegment( substr($str, 0, $start)) . $mid;
		if($compat && !in_array(substr($mid, 0, 3), array('109', '110', '201', '211', '221', '231', '241'))) {
			$mid = $this->decodeSegment(substr($str, 0, 4)).$this->decodeSegment(substr($str, 4));
		}
		if($for_mid){
			if(substr($mid, 0, 1)=='1' && substr($mid, 7, 1)=='0') {
				$mid = substr($mid, 0, 7).substr($mid, 8);
			}
		}
		return $mid;
	}

	/**
	 * 将10进制转换成62进制
	 *
	 * @param	string	$str	10进制字符串
	 * @return	string
	 */
	private function encodeSegment($str) {
		$out = '';
		while($str > 0){
			$idx = $str % 62;
			$out = substr($this->string, $idx, 1) . $out;
			$str = floor($str / 62);
		}
		return $out;
	}
	
	/**
	 * 将62进制转换成10进制
	 *
	 * @param	string	$str	62进制字符串
	 * @return	string
	 */
	private function decodeSegment($str) {
		$out = 0;
		$base = 1;
		for($t=strlen($str) - 1;$t>=0;$t-=1) {
			$out = $out + $base * strpos($this->string, substr($str, $t, 1));
			$base *= 62;
		}
		return $out . "";
	}
	
}

?>