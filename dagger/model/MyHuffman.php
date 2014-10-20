<?php
include_once SERVER_ROOT . 'dagger/model/MyConvert.php';
include_once SERVER_ROOT . 'dagger/config/HuffmanDictConfig.php';
/**
 * 注意：PHP必须开启高精度数学运算
 * 哈夫曼压缩
 * 
 * @copyright	
 * @author		fanrong@
 * @version		1.0 - 2012-08-17
 * @package		Tools
 */
class MyHuffman 
{
	/**
	 * 哈夫曼字典
	 */
	private $__huffman_dict = array();
	
	/**
	 * 载入哈夫曼字典
	 * 
	 * @param	array	$dict	哈夫曼字典
	 */
	public function __construct($dict)
	{
		$this->__huffman_dict = $dict;
	}
	
	/**
	 * 进行哈夫曼压缩
	 *
	 * @param	string	$str	待压缩字符串
	 * @return	string
	 */
	private function huffmanEncode($str) 
	{
		$binary = '';
		$len = strlen($str);
		for($i=0;$i<$len;$i++)
		{
			$alphabet = $str{$i};
			if(!isset($this->__huffman_dict[$alphabet])) {
				Message::showError("字符串里面含有非法字符");
			}
			$mapBinary = $this->__huffman_dict[$alphabet];
			$binary .= $mapBinary;
		}
		
		return $binary;
	}
	
	/**
	 * 进行哈夫曼解压
	 *
	 * @param	string	$binary	待解压字符串
	 * @return	string
	 */
	private function huffmanDecode($binary) 
	{
		$binarylen = strlen($binary);
		$str = $tbinary = '';
		for($i=0;$i<$binarylen;$i++)
		{
			$tbinary .= $binary{$i};
			$ori_alpha = array_search($tbinary, $this->__huffman_dict, true);
			if($ori_alpha !== false) {
				$str .= $ori_alpha;
				$tbinary = '';
			}
		}
		return $str;
	}
	
	/**
	 * 进行哈夫曼压缩，并转换为六十二进制
	 *
	 * @param	string	$str	待压缩字符串
	 * @return	string
	 */
	public function huffman62Encode($str) 
	{
		$binary = self::huffmanEncode($str);
		$binary = "1" . $binary;
		$scale62 = MyConvert::encode2_62($binary);
		return $scale62;
	}
	
	/**
	 * 将六十二进制转换为二进制，并进行哈夫曼解压
	 *
	 * @param	string	$str	待解压字符串
	 * @return	string
	 */
	public function huffman62Decode($str) 
	{
		$binary = MyConvert::decode2_62($str);
		$binary = substr($binary, 1);
		$ori_str = self::huffmanDecode($binary);
		return $ori_str;
	}
}
?>