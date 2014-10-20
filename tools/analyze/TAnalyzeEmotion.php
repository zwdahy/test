<?php
/**
 * 分析tag的类
 * 
 * @copyright	(c) 2010, 新浪网  All rights reserved.
 * @author 	李如其
 * @version	1.0 - 2010-1-21
 * @package	tools
 */

class TAnalyzeEmotion {
	/**
	 * 把表情替换为新浪标签
	 *
	 * @param string $content
	 * @return unknown
	 */
	public function textToIcon($content)
	{
		$content = preg_replace("/\[([^<>`~!@#$%^&*(){}\[\]]+)\]/ise", "renderIcon('\\1')", $content);
		return $content;
	}

    /**
     * iconToText 
     * 
     * 图片表情替换为文字 
     * @param mixed $content 
     * @access public
     * @return void
     */
	public function iconToText($content, $isUrldecode = true)
	{
		$GLOBALS['LANGUAGE'] = 'zh-cn';
		$content = (true === $isUrldecode) ? rawurldecode($content) : $content;
	    preg_match_all('/\<img\s*src\s*=\s*[\'\"]?http:\/\/simg.sinajs.cn\/miniblog2style\/images\/common\/face\/basic\/([\w]+).gif[\'\"]?\s*[^>]+\>/is', $content, $match);
        $emotion_id2txt = array_flip(array_unique($GLOBALS['EMOTION_TXT2ID'][$GLOBALS['LANGUAGE']]));

        if(!is_array($match[0]) || empty($match[0])) return (true === $isUrldecode) ? rawurlencode($content) : $content;
        foreach($match[0] as $k => $v) {
            $content = str_replace($v, '['.$emotion_id2txt[$match[1][$k]].']', $content); 
        }
        return (true === $isUrldecode) ? rawurlencode($content) : $content;
	}

}

function renderIcon($text) {
	$EMOTION_URL = "http://simg.sinajs.cn/miniblog2style/images/common/face/basic/%s.gif";
	$id = $GLOBALS['EMOTION_TXT2ID']['all'][$text];
	if(isset($id))
	{
		$str = sprintf($EMOTION_URL, $id);
		return "<img src=\"{$str}\" title=\"{$text}\" />";
	}
    return "[{$text}]";
}

?>
