<?php
/**
 * Project:     根据电台rid提供微博特殊feed的短链
 * File:        show_url_for_weibofeed.php
 * 
 * 获取无线端电台推荐图
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_url_for_weibofeed.php
 * @copyright sina.com
 * @author  张旭<zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class showUrlFORWeibofeed extends control {
	protected function checkPara() {
		$this->para['rid'] = request::get('rid', 'INT');		
		if(empty($this->para['rid'])){			
			$this->display(array('errno' => -4, 'errmsg' => '参数错误!'), 'json');
			exit;
		}
		return true;
	}
	protected function action() {			
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$annotations = $mRadio->getRadioAnnotations($this->para['rid']);
		//拼凑URL供特殊feed解析 
		date_default_timezone_set('PRC');
		$now =time();
		$week = date("N",$now);
		$time = date("H:i",$now); 
		
		$programs =  $mRadio->getRadioProgram($this->para['rid'],$week);
		$programs = unserialize($programs['program_info']);
		$program_now = null;
		
		if(!empty($programs)){
			foreach($programs as $v){
				if($time>=$v['begintime']&&$time<=$v['endtime']){
					$program_now = $v['begintime'];
					$program_time = explode(':',$program_now);
					break;
				}
			}
		 }
		$tmp_annotations = json_decode(htmlspecialchars_decode($annotations));
		foreach($tmp_annotations[0] as $k=>$v){
			$radio_info[$k] = $v;
		}
		if($program_time){
			$url = $radio_info['url'].'_'.$week.$program_time[0].$program_time[1];
		}else{
			$url = $radio_info['url'];
		}
		$short = $mRadio->long2short_url($url);
		$short_url = $short['urls'][0]['url_short'];
		
	
		$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' =>array('feedurl'=> $short_url)
				);
	
		$this->display($data, 'json');
		return true;
	}
}
new showUrlFORWeibofeed(RADIO_APP_SOURCE);
?>
