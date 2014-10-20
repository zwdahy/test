<?php
/**
 * Project:     电台对外接口
 * File:        getradioinfoforshorturl.php
 * 
 * 获取电台信息
 * 提供给微博开放平台部门，域名http://radio.weibo.com/下的短链解析时查询。 
 *
 * @link http://i.service.t.sina.com.cn/sapps/radio/getpicforshorturl.php
 * @copyright sina.com
 * @author 刘玉刚<yugang2@staff.sina.com.cn>
 * @package Sina
 * @version 1.2
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getPicForShortUrl extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['url'] = request::get('url', 'STR');		//电台url
		if(empty($this->para['url'])){			
			$this->display(array('errno'=>-4,'errmsg'=>'url参数错误'), 'json');
			exit();
		}
		return true;
	}
	
	protected function action() {
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$tmpArr = explode('/',$this->para['url']);
		$domainStr = array_pop($tmpArr);
		$province_spell= array_pop($tmpArr);
		$domainStr = str_replace('.html', '', $domainStr);
		$tmpArr = explode('_', $domainStr);
		$domain = $tmpArr[0];
		if(strpos($domain,'?')){
			$tmp_domain = explode('?', $domain);
			$domain = $tmp_domain[0];
		}
		if(!empty($tmpArr[1]) && (strlen($tmpArr[1]) == 5) ){
			$program_wday = $tmpArr[1][0];
			$program_begintime = $tmpArr[1][1].$tmpArr[1][2].':'.$tmpArr[1][3].$tmpArr[1][4];
		}

		//查询电台
		$args = array(
			'search_key' => "domain&province_spell",
			'search_value' => $domain.'&'.$province_spell,
			'search_type' => "=&="
		);
		$rs = $mRadio->getRadio($args);
		if(($rs['errorno'] == 1) && !empty($rs['result']['content'])){
			$info = $rs['result']['content'][0];
			$rid = $info['rid'];
			$tmp = explode('|',$info['info']);
			$dj_uids = array();
			$img_path = $info['img_path'];
			$program_name = '';
			if($program_wday && $program_begintime){ //节目内容
				$program_info = $mRadio->getRadioProgram($rid,$program_wday);
				if(!empty($program_info['program_info'])){
					$program_info = unserialize($program_info['program_info']);
					foreach ($program_info as $k => $p) {
						if($p['begintime'] == $program_begintime){
							$program_name = $p['program_name'];
							if(!empty($p['dj_info'])){
								foreach ($p['dj_info'] as $dj) {
									$dj_uids[] = array('uid' => $dj['uid']);
								}
							}
							break;
						}
					}
				}
			}else{ //全电台dj
				$djinfo = $mRadio->getDjInfoByRid(array($rid));
				if(!empty($djinfo['result'])){
					$djinfo = current($djinfo['result']);
					if(!empty($djinfo['uids'])){
						$djuids = explode(',',$djinfo['uids']);
					}
					if(!empty($djuids)){
						foreach ($djuids as $k=>$uid) {
							if($k < 4){
								$dj_uids[] = array('uid' => $uid);
							}
						}
					}
				}
			}
		}
		$radioInfo =  array(
			'title' => '微电台',
			'url' => RADIO_URL,
		);
		if(!empty($domain)&&!empty($province_spell)){
			$radioInfo['url'] = RADIO_URL. '/'.$province_spell.'/'.$domain;
		}
		//测试预览图片
		$default_img_url = 'http://ww4.sinaimg.cn/large/788cb0ffjw1dwzio2h7a4g.gif';
		$radioInfo['rid'] = isset($rid) ? $rid : 0;
		$radioInfo['program_name'] = !empty($program_name) ? $program_name : '';
		if(!empty($tmp)){
			$radioInfo['title'] = $tmp[0].$tmp[1];
			$rtitle = $tmp[0].'-'.$tmp[1];
		}
		if(!empty($dj_uids)){
			$radioInfo['dj'] = $dj_uids;
			foreach ($dj_uids as $dj) {
				$uidsArr[] = $dj['uid'];
			}
		}

		//生成预览图
		$rinfo = array(
			'title' => !empty($radioInfo['program_name']) ? $radioInfo['program_name'] : $radioInfo['title'],
			'dj' => array(),
			'radio_title' => '',
			'img_path'=>$img_path
		);
		if(!empty($dj_uids)){
			$djinfos = $mRadio->getUserInfoByUid($uidsArr);
			if(!empty($djinfos)){
				foreach ($djinfos as $k => $v) {
					$djnames[] = $v['screen_name'];
				}
			}
			$rinfo['dj'] = $djnames;
		}
		if( !empty($radioInfo['program_name']) && empty($rinfo['dj'])){
			$rinfo ['radio_title'] = $radioInfo['title'];
		}
		if(!empty($rinfo['title'])&&$rinfo['title']!='微电台'){
			$preview_img_url = $mRadio->generateFeedThumbnailPre($rinfo);
		}
	}
}
new getPicForShortUrl(RADIO_APP_SOURCE);
?>