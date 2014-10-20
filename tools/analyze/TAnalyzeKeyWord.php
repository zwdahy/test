<?php
/**
 * 分析##符号的类
 *
 * @copyright	(c) 2009, 新浪网SPACE  All rights reserved.
 * @package	tools
 */
class TAnalyzeKeyWord{
	/**
	 * 渲染tag显示
	 *
	 * @param string $content
	 * @return string
	 */
	public function renderTag($content, $istarget=false) {
		$istarget = $istarget ? 1 : 0;
		$content = str_replace("＃", "#", $content);
		$content = str_replace ( '&#039;', '\'', $content );
		$content = str_replace ( '&#39;', '\'', $content );
		$str = preg_replace("/#([^#]+)#/ise", "stripTagKey('\\1','\\0', {$istarget})", $content);
		return $str;
	}
}
function stripTagKey($str, $linkWord, $istarget=false) {
		$target = $istarget ? ' target="_blank"' : '';
    	if(mb_strwidth($str) > 15) {
        	$str = mb_substr($str, 0, 15, 'UTF-8');
		}
		$url = sprintf("http://huati.weibo.com/k/%s?from=514",htmlspecialchars($str));
		$str = '<a href="'.$url.'"'.$target.'>'.$linkWord.'</a>';
		return $str;
}
?>