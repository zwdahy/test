<?php
/**
 * 分析@符号的类
 *
 * @copyright	(c) 2009, 新浪网SPACE  All rights reserved.
 * @author 	李刚(ligang1)
 * @version	1.0 - 2009-09-21
 * @package	tools
 */
define("NAMEURL","/n/");

class TAnalyzeAt {
	//存储替换规则  '被替换字符串'=>'替换成字符串'
	var $stripArr = array();

	/**
	 * 提取出@
	 *
	 * @param string $content
	 * @return array all the @username
	 */
	public function getAtUsername($content) {
		$content = $this->stripEmail($content, false);
		$content = $this->mb_filter($content);
		$names = array();
		$ret = preg_match_all("/@([\x{4e00}-\x{9fa5}\x{ff00}-\x{ffff}\x{0800}-\x{4e00}\x{3130}-\x{318f}\x{ac00}-\x{d7a3}-a-zA-Z0-9_]+)/u",$content,$names);
		if(false === $ret) {
			return array();
		} else {
			return $names[1];
		}
	}


	//提取文本中的email地址
	public function getEmail($str) {
		$pattern = "/[a-z0-9]([a-z0-9]*[-_\.]?[a-z0-9]+)*@([a-z0-9]*[-_]?[a-z0-9]+)+[\.][a-z]{2,3}([\.][a-z]{2})?/i";
    	preg_match_all($pattern,$str,$emailArr);
    	return $emailArr[0];
	}

	//去除文本中的email地址
	public function stripEmail($content, $repStr=false) {
		$emailArr = $this->getEmail($content);
		foreach($emailArr as $no => $email) {
			if($repStr) {
				$repStr = "reSinaEmail".$no;
				$this->stripArr[$repStr] = $email;
				$content = str_replace($email, $repStr, $content);
			} else {
				$content = str_replace($email, "", $content);
			}
		}
		return $content;
	}

	public function atTOlink(&$content, $atUsers, $isTarget=false) {
		if(!is_array($atUsers)) return;
		mb_internal_encoding("utf-8");
		//去除字符串中的email地址
		$content = $this->stripEmail($content, true);
		//对要替换昵称长度排序
		usort($atUsers, 'sortByLen');
		$targetStr = $isTarget ? "target=\"_blank\"" : "";
		foreach ($atUsers as $nick) {
   			$content = preg_replace('|(?!>.*)((<span[^>]+>)?@(<span[^>]+>)?(' . $nick . ')(</span>)?)(?![^<]*<\/)|e', "'<a href=\"http://'. T_DOMAIN . NAMEURL . urlencode('\\4') . '\" {$targetStr}>\\1</a>'", $content);
		}
		//还原原始$content
		foreach($this->stripArr as $pat => $reStr) {
			$content = mb_ereg_replace($pat, $reStr, $content);
		}

	}

	public function strip_minblog_tags(&$text) {
		mb_internal_encoding("utf-8");
		$pattern = ">#(.*?)#<\/a>";
		$result = array();
		preg_match_all($pattern,$text,$result);
		if(is_array($result) && count($result)>0) {
			$paRe = $result[1];
			foreach($paRe as $key => $value) {
				$pattern2 = "<[^>]*>";
				$content = mb_ereg_replace($pattern2, "", $value);
				$paRe[$key] = $content;
			}
			$rep = $result[0];
			foreach($rep as $key => $ma) {
				$text = str_replace($ma, "#" . $paRe[$key] . "#</a", $text);
			}
		}
	}
	/**
	 * 过滤掉标点符号
	 *
	 * @param string $str
	 * @return string the filtered string
	 */	
	public function mb_filter_punct($str){
		$str = $this->mb_filter($str);
		$str = str_replace(" ", "",$str);
		return $str;
	}
	/**
	 * 过滤掉字符串中的特殊字符
	 *
	 * @param string $str
	 * @return string the filtered string
	 */
	private function mb_filter($str) {
		mb_internal_encoding("utf-8");
		$filter = array("`","~","!","#","$","%","^","&","*","(",")","-","=","+","[","]","{","}","|","'",";",":","\"","?","/",">","<",",",".","｀","～","·","！","◎","＃","￥","％","※","×","（","）","—","＋","－","＝","§","÷","】","【","『","』","‘","’","“","”","；","：","？","、","》","。","《","，","／","＞","＜","｛","｝","＼");
		foreach($filter as $v) {
			$str = str_replace($v,' ',$str);
		}
		return $str;
	}
}

function sortByLen($a, $b) {
   if (strlen($a) == strlen($b)) return 0;
   return (strlen($a) < strlen($b)) ? 1 : - 1;
}
?>
