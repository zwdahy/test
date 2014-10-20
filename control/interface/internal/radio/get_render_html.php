<?php
/**
 * Project:     微电台特殊feed吐出html
 * File:        get_render_html.php
 * 
 * 添加地区首页
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/get_render_html.php
 * @copyright sina.com
 * @author  张旭<zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */

include_once SERVER_ROOT . 'config/radioconf.php';
class getRenderHtml extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['cuid'] = $_POST['cuid'];						//当前登录用户的UID
		$this->para['url_short'] = $_POST['url_short'];			//短链地址
		$this->para['url_long'] = $_POST['url_long'];				//长链地址
		$this->para['type'] = $_POST['type'];				//短链类型，本项目默认为22
		$this->para['metadata'] = urldecode($_POST['metadata']);			//短链原数据，即annonations字段数据
		$this->para['lang'] = $_POST['lang'];					//语言版本(zh-cn  |  zh-tw  | en-us)
		$this->para['cip'] = $_POST['cip'];				//用户的IP，非必须

		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['type'] !=22){
			$error = array('errmsg' => '此接口仅限微电台调用');
			$this->display($error, 'json');
			return true;
		}
		
		$json = $this->para['metadata'];
		$infos = json_decode($json,true);
		if(!empty($infos[0]['logo_img_url'])){
			$html = '<div class="O_radio S_line1 S_bg4" style="text-align:left"><dl class="clearfix"><dt><img width="98" height="98" src="'.$infos[0]['logo_img_url'].'" alt="" /></dt><dd>';
		}else{
			$html = '<div class="O_radio S_line1 S_bg4" style="text-align:left"><dl class="clearfix"><dt><img width="98" height="98" src="http://ww4.sinaimg.cn/large/6536b7b0jw1dwknd1vywsj.jpg" alt="" /></dt><dd>';
		}
		if(!empty($infos)){
			$infos = $infos[0];
			$djs = $infos['dj'];
			if( !empty($infos['rid']) ){
				if( !empty($infos['program_name']) ){
					$html.= '<h3>'.$infos['program_name'].'</h3>';
				}else{
					$html.= '<h3>'.$infos['title'].'</h3>';
				}	
				$infos['url'] = !empty($infos['url']) ? $infos['url'] : RADIO_URL;
					if(!empty($djs)){
					$html.= '<div class="detail">';
					if( !empty($infos['program_name']) ){
							$html.= '<p class="S_txt2">所属电台：'.$infos['title'].'</p>';
							$html.='<p class="S_txt2">节目主持：';
						}else{
							$html.='<p class="S_txt2">明星主持：';
						}
				
					foreach($djs as $v){
						$tmpuid = array($v['uid']);
						$dj_info = $obj->getUserInfoByUid($tmpuid);
						$html.= '<a href="'.$dj_info[$v['uid']]['link_url'].'" title="'.$dj_info[$v['uid']]['screen_name'].'" ><img src="'.$dj_info[$v['uid']]['profile_image_url'].'" class="W_face_radius" alt="" width="30" height="30" usercard="id='.$v['uid'].'" /></a>';
					}
					$html.= '</p></div>';
				}else{
					$html.='<p class="detail S_txt2">所属电台：'.$infos['title'].'</p>';
				}
			}else{
				$html.= '<h3>微电台</h3>';
				$infos['url'] = RADIO_URL;
				$html.='<p class="detail S_txt2">微电台是将传统电台与微博相结合的产品，突破以往收听电台的地域和终端限制，实现听友与主持人的实时互动的需求。400家电台，3000多名DJ，上万种声音，在微电台等着你！</p>';
			}
		}

		
		$html.= '<p class="btn"><a href="'.$infos['url'].'" class="W_btn_a" target="_blank" suda-uatrack="key=tblog_radio_specialfeed&value=listen_button"><span>点击收听</span></a></p></dd></dl></div>';
	
		$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'html' => $html
		);

		$this->display($data, 'json');
		return true;
	}
	
	
}

new getRenderHtml(RADIO_APP_SOURCE);
?>