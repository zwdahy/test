<?php
/**
 * 
 * 表情ajax请求
 * 
 * @package 
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * 
 */
include_once SERVER_ROOT."control/radio/insertFunc.php";
class faceIcon extends control {
	protected function checkPara() {
		//判断来源
		$referinfo = parse_url($_SERVER['HTTP_REFERER']);
		//if(!in_array($referinfo['host'],$GLOBALS['REFER_URL'])) {
		//	$this->setCError('M00004','Refer来源错误');
		//	return false;
		//}
	}
	
	protected function action() {
		/*表情*/
		global $LANGUAGE;
		$LANGUAGE = in_array($LANGUAGE, array('zh-cn','zh-tw')) ? $LANGUAGE : 'zh-cn';
	
		$emotions = $sources = array ();
		if (is_array ( $GLOBALS ['EMOTION_TXT2ID'] [$LANGUAGE])) {
			foreach ( $GLOBALS ['EMOTION_TXT2ID'] [$LANGUAGE] as $text => $src ) {
				if (empty ( $sources [$src] )) {
					$emotions [] = array ('icon' => $text, 'value' => "[{$text}]", 'src' => "basic/{$src}.gif" );
					$sources [$src] = true;
				}
			}
		}
		$jsonArray = array(
			'code' => 'A00006',
			'data' => $emotions
		);
		$this->display($jsonArray, 'json');
	}
	
}
new faceIcon(RADIO_APP_SOURCE);
?>