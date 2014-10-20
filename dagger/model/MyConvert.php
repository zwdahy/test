<?php
include_once SERVER_ROOT . 'dagger/model/MyHuffman.php';
include_once SERVER_ROOT . 'dagger/config/HuffmanDictConfig.php';
/**
 * 注意：PHP必须开启高精度数学运算
 * 二进制、十进制与六十二进制之间的转换
 * 
 * @copyright	
 * @author		fanrong@
 * @version		1.0 - 2012-08-17
 * @package		Tools
 */
class MyConvert {
	/**
	 * 六十二进制对应字符串
	 */
	static private $__string = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	
	/**
	 * 将二进制转换成十进制
	 *
	 * @param	string	$str	二进制字符串
	 * @return	string
	 */
	static public function encode2_10($str) 
	{
		$decimal = '0';
		if(preg_match("/[0-1]*/", $str)) {
			$len = strlen($str);
			for($i=0;$i<$len;$i++)
			{
				$t = $str{$i};
				if($t) {
					$exp = $len - 1 - $i;
					$tpow = bcpow(2, $exp);
					$decimal = bcadd($decimal, $tpow);
				}
			}
		}
		
		return $decimal;
	}
	
	/**
	 * 将十进制转换成二进制
	 *
	 * @param	string	$str	十进制字符串
	 * @return	string
	 */
	static public function decode2_10($str) 
	{
		$binary = '0';
		if(preg_match("/[0-9]*/", $str)) {
			$binary = '';
			$tag = 1;
			do {
				$tmod = bcmod($str, 2);
				$binary .= $tmod;
				$str = bcdiv($str, 2);
				$tag = bccomp(2, $str);
			} while ($tag < 1);
			$binary .= $str ? 1 : '';
		}
		$binary = strrev($binary);
		
		return $binary;
	}
	
	/**
	 * 将十进制转换成六十二进制
	 *
	 * @param	string	$str	十进制字符串
	 * @return	string
	 */
	static public function encode10_62($str) 
	{
		$scale62 = '0';
		if(preg_match("/[0-9]*/", $str)) {
			$scale62 = '';
			$tag = 1;
			do {
				$tmod = bcmod($str, 62);
				$scale62 .= self::$__string{$tmod};
				$str = bcdiv($str, 62);
				$tag = bccomp(62, $str);
			} while ($tag < 1);
			$scale62 .= $str ? self::$__string{$str} : '';
		}
		$scale62 = strrev($scale62);
		
		return $scale62;
	}
	
	/**
	 * 将六十二进制转换成十进制
	 *
	 * @param	string	$str	十进制字符串
	 * @return	string
	 */
	static public function decode10_62($str) 
	{
		$decimal = '0';
		if(preg_match("/[0-9a-zA-Z]*/", $str)) {
			$len = strlen($str);
			for($i=0;$i<$len;$i++)
			{
				$t = $str{$i};
				if(!empty($t)) {
					$exp = $len - 1 - $i;
					$baseNum = strpos(self::$__string, $t);
					$tpow = bcpow(62, $exp);
					$tnum = bcmul($baseNum, $tpow);
					$decimal = bcadd($decimal, $tnum);
				}
			}
		}
		
		return $decimal;
	}
	
	/**
	 * 将二进制转换成六十二进制
	 *
	 * @param	string	$str	二进制字符串
	 * @return	string
	 */
	static public function encode2_62($str) 
	{
		$decimal = self::encode2_10($str);
		$scale62 = self::encode10_62($decimal);
		return $scale62;
	}
	
	/**
	 * 将六十二进制转换成二进制
	 *
	 * @param	string	$str	十进制字符串
	 * @return	string
	 */
	static public function decode2_62($str) 
	{
		$decimal = self::decode10_62($str);
		$binary = self::decode2_10($decimal);
		return $binary;
	}
}
?>