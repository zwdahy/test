<?php
/**
 * Project:    手机客户端微博page首页
 * File:       weibopage_get_index.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/weibopage_get_index.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class weiboPageIndex extends control {
	//大于此版本号的客户端可以显示新特性
	const VERSION4 = 4;
	//大于此版本号的客户端PAGEID和CARDID改为CONTAINERID和ITEMID
	const VERSION5 = 5;
	protected function checkPara() {		
		$this->para['pageid'] = $_REQUEST['page_id'];		//电台id
		$this->para['containerid'] = $_REQUEST['containerid'];
		$this->para['uid'] = $_REQUEST['uid'];		//用户id
		$this->para['vp'] = $_REQUEST['v_p'];

		if ($this->para['vp'] >= self::VERSION5) {
			$this->para['pageid'] = $this->para['containerid'];
		}
		if(empty($this->para['pageid'])){
			$this->display(array('errno'=>-4,'errmsg'=>'page_id参数错误'), 'json');
			exit();
		}
		return true;
	}
	public function xssCallBackCheck($content){
		$strlen = strlen($content);
		$return = '';
		$is_html_start = false;
		for($i = 0; $i < $strlen; $i++) {
			if($content{$i} == '<') {
				$is_html_start = true;
			}
			if($is_html_start && $content{$i} == '>') {
				$is_html_start = false;
				continue;
			}
			if(!$is_html_start) {
				$return .= $content{$i};
			}
		}
		return $return;
	}
	protected function action() {
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');	
		$vp = $this->para['vp'];
		$pageid = $this->para['pageid'];

		$ids = explode('_',$pageid);
		$cur_rid = $ids[1];
		$cur_uid = $this->para['uid'];
		//电台相关信息
		$radioInfo = $mRadio->getRadioInfoByRid(array($cur_rid));
		$radioInfo = $radioInfo['result'][$cur_rid]; 

		//当前正在播放的节目
		$today = getdate();
		if($today['wday'] == 0){
			$today['wday'] = 7;
		}
		$programs = $mRadio->getRadioProgram($cur_rid,$today['wday']);
		$begintime = strtotime($topic['begintime']);
		$endtime = strtotime($topic['endtime']);
		$program_today = $mRadio->getProgramInfo(unserialize($programs['program_info']));
		if(!empty($program_today)){
			foreach($program_today as $k =>$value){
				$begintime = strtotime($value['begintime']);
				$endtime = strtotime($value['endtime']);
				if(time() >= $begintime && time() <= $endtime){
					$program_now = $value;
					$program_next =$program_today[$k+1];
					break;
				}
			}
		}

		//获取电台主持人
		$aDj = $mRadio->getDjDetail($cur_rid, $cur_uid);
		if(!empty($aDj['result'])){
			$radiodj = $aDj['result'];
			$djs_arr = array();
			$i=1;
			if ($vp >= self::VERSION4) {
				$n = 7;
			} else {
				$n = 5;
			}
			foreach($radiodj['userinfo'] as $v){
				if($i>$n){
					break;
				}
				$djs_arr[$v['uid']] = $v['portrait'];
				$i++;
			}
		}

		//获取正在收听用户
		$aListeners = $mRadio->getListeners($cur_rid, $cur_uid);
		if(!empty($aListeners['result'])){
			$listener_arr = array();
			$l=1;
			if ($vp >= self::VERSION4) {
				$n = 7;
			} else {
				$n = 5;
			}
			$listeners = $aListeners['result'];
			foreach($listeners as $v1){
				if($l>$n){
					break;
				}
				foreach($v1 as $v2){
					if($l>$n){
						break;
					}
					$listener_arr[$v2['id']] = $v2['profile_image_url'];
					$l++;
				}
			}
		}

		//获取热门电台
		$hot_radioinfo1 = $mRadio->getListenRank();
		$tmp = array(
			'errorno' => '1',
			'result' => array());
		foreach($hot_radioinfo1 as $key => $val){
			$tmp['result'][$val['info']['rid']] = $val['info'];
		}
		$hot_radioinfo = $tmp;
		$hot_radios = array();
		if($hot_radioinfo['errorno'] == 1 && !empty($hot_radioinfo['result'])){
			$hot_radios = $hot_radioinfo['result'];
			foreach($hot_radios as $key => $value){ 
				if($value['online'] == '2' || $value['rid'] == $cur_rid){
					unset($hot_radios[$key]);
				}
			}
		}
		if(!empty($hot_radios)){
			$radio_pic = array();
			$r = 1;
			foreach($hot_radios as $value){
				if($r>5){
					break;
				}
				$radio_pic[] = array('pic'=>$value['img_path']);
				$r++;
			}
		}
		//拼凑数组
		$keyword = str_replace('#','',$radioInfo['tag']);

		if ($vp >= self::VERSION5) {
			$pageInfo = array(
					'containerid' => $pageid,
					'v_p'=>$vp,
					'background'=>'http://u1.sinaimg.cn/upload/page-cover/page_cover_radio_2x.jpg',
					'title_top' => '微电台',
					'page_title'=>$keyword,
					'display_arrow' => 0,
					'nick'=>$radioInfo['name'],
					'portrait'=>$radioInfo['img_path'],
					'portrait_scheme'=> 'sinaweibo://pagedetailinfo?containerid='.$pageid.'_-_intro&title='.urlencode('电台介绍'),
					'desc'=>$this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro'])),
					'desc_scheme'=>'sinaweibo://pagedetailinfo?containerid='.$pageid.'_-_intro&title='.urlencode('电台介绍'),
					'buttons' => array (
							0 => array (
									'type' => 'like',
									'name' => '赞',
							),
							1 => array (
								'type' => 'link',
								'name' => '参与互动',
								'pic' => 'http://u1.sinaimg.cn/upload/2013/07/04/userinfo_relationship_indicator_discuss.png',
								'params' => array (
									'scheme' => 'sinaweibo://sendweibo?containerid='.$pageid.'&pagetitle='.urlencode($keyword).'&title='.urlencode('参与互动'),
								),
							),
					),
			);			
		} else {
			$pageInfo = array(
					'page_type_name'=>'微电台',
					'page_id' => $pageid,
					'page_tag'=>'radio',
					'page_type'=> 2,
					'page_title'=>$keyword,
					'like'=>'',
					'page_redirect_url'=>'',
					'v_p'=>$vp,
					'background'=>'http://u1.sinaimg.cn/upload/page-cover/page_cover_radio_2x.jpg',
			);

			$cards[0] = array(
				'card_type' => 1,
				'card_id' => 'intro',
				'card_type_name'=>'电台介绍',
				'nick'=>$radioInfo['name'],
				'title'=>$radioInfo['name'],
				'portrait'=>$radioInfo['img_path'],
				'desc'=>$this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro'])),
				'islike'=>0,
				'scheme'=>'',
				'desc_scheme'=>'sinaweibo://pagedetailinfo?pageid='.$pageid.'&cardid=intro&title='.urlencode('电台介绍'),
				'portrait_scheme'=> 'sinaweibo://pagedetailinfo?pageid='.$pageid.'&cardid=intro&title='.urlencode('电台介绍'),
				'background'=>'http://ww3.sinaimg.cn/mw690/788cb0ffjw1e0iseg8k9xj.jpg',
				'buttons' => array (
								array (
									'type' => 'like',
									'name' => '赞',
								),
							),
			);

			if ($vp >= self::VERSION4) {
				$cards[0]['buttons'][] =  array (
							'type' => 'link',
							'name' => '参与互动',
							'pic' => 'http://u1.sinaimg.cn/upload/2013/07/04/userinfo_relationship_indicator_discuss.png',
							'params' => array (
								'scheme' => 'sinaweibo://sendweibo?pageid='.$pageid.'&pagetitle='.urlencode($keyword).'&title='.urlencode('参与互动'),
						),
				);
			}
		}

		$appList[0] =	array(
					'is_show'=> 1,
					'title'=>"官方\n微博",
					'scheme'=>'sinaweibo://userinfo?uid='.$radioInfo['uid'],
					'type'=>'',
					'count'=>'',
				);

		$appList[1] = array(
					'is_show'=> 1,
					'title'=>'节目单',
					'scheme'=>'sinaweibo://pagedetailinfo?containerid='.$pageid.'_-_list&title='.urlencode('今日节目单'),
					'type'=>'',
					'count'=>'',
				);

		if(1==$radioInfo['is_feed']){
			$appList[2] = array(
					'is_show'=> 1,
					'title'=>'听友互动',
					'scheme'=>'sinaweibo://pagesearchweibo?containerid='.$pageid.'_-_searchweibo&title='.urlencode('听友互动').'&q='.urlencode($keyword),
					'type'=>'pagesearchweibo',
					'count'=>'',
					'display_arrow' => 0,
					'is_asyn' => 0,
					'desc' => '参与节目互动',
					'prefix' => $keyword,
			);
		}

		$appList[3] = array(
					'is_show'=> 1,
					'title'=>'喜欢',
					'scheme'=>'',
					'type'=>'like',
					'count'=>'',
				);

		$cards[1] = array(
			'card_type' => 2,
			'card_type_name'=>'应用列表',
			'itemid'=>$pageid,
			'title'=>'',
			'scheme'=>'',
			'display_arrow'=>0,
			'is_asyn'=>0,
			'newflag'=>'',
			'apps'=>$appList
		);

		if(!empty($program_now['program_name'])){
			$cards[2] =array(
				'card_type' => 8,
				'card_type_name'=>'正在播出',
				'itemid' => $pageid.'_-_list',
				'title'=>'',
				'display_arrow'=> 1,
				'scheme'=>'sinaweibo://browser?url='.urlencode('http://m.weibo.cn/pubs/radio/play?showmenu=0&channel='.$radioInfo['province_spell'].'_'.$radioInfo['domain']),
				'pic'=>'http://www.sinaimg.cn/dy/deco/radio/img/radio_12.jpg',
				'title_sub'=>'正在播出《'.$program_now['program_name'].'》',
				'desc1'=>'',
				'desc2'=> !empty($program_next['program_name']) ? '即将播出《'.$program_next['program_name'].'》' : '',
				'card_display_type'=>1,
			
			);
		}else{
			$cards[2] = array(
				'card_type' => 8,
				'card_type_name'=>'正在播出',
				'itemid' => $pageid.'_-_list',
				'title'=>'',
				'display_arrow'=> 1,
				'scheme'=>'sinaweibo://browser?url='.urlencode('http://m.weibo.cn/pubs/radio/play?showmenu=0&channel='.$radioInfo['province_spell'].'_'.$radioInfo['domain']),
				'pic'=>'http://www.sinaimg.cn/dy/deco/radio/img/radio_10.jpg',
				'title_sub'=>$radioInfo['name'],
				'desc1'=>'',
				'desc2'=>'',
				'card_display_type'=>1,
			
			);
		}

		if(1==$radioInfo['is_feed']){			
			if ($vp < self::VERSION4) {
				$cards[3] = array(
					'card_type' => 5,
					'card_type_name' => 'input',
					'itemid' => $pageid,
					'title' => '参与节目互动', 
					'scheme' => 'sinaweibo://sendweibo?containerid='.$pageid.'&pagetitle='.urlencode($keyword).'&title='.urlencode('参与互动'),
					'display_arrow' => 0,
					'is_asyn' => 0,
					'desc' => '参与节目互动',
					'prefix' => $radioInfo['tag']
				);
			}
			$cards[4] = array(
				'card_type' => 9,
				'card_type_name' => 'WEIBO_CARD_点评',
				'itemid' => $pageid,
				'title' => '听友互动', 
				'scheme'=>'sinaweibo://pagesearchweibo?containerid='.$pageid.'_-_searchweibo&title='.urlencode('听友互动').'&q='.urlencode($keyword),
				'keyword' => $keyword,
				'display_arrow' => 1,
				'is_asyn' => 0,
				'weibo_need' => 'mblog',   
			);
		}
		if(!empty($djs_arr)){
			//新版采用7个用户样式
			if ($vp >= self::VERSION4) {
				$djs_uid = array();
				foreach ($djs_arr as $uid => $pic) {
					$djs_uid[]['uid'] = $uid;
				}
				$cards[5] = array(
					'card_type'=>24,
					'card_type_name'=>'相关用户',
					'title'=>'本电台DJ',
					'scheme'=>'sinaweibo://pageuserlist?containerid='.$pageid.'_-_djs&title='.urlencode('本台DJ'),
					'itemid'=>$pageid.'_-_djs',
					'roundedcorner'=>1,
					'display_arrow'=>1,
					'weibo_need'=>'user',
					'users'=>$djs_uid,
				);
			} else {
				$djs_img = array();
				foreach ($djs_arr as $uid => $pic) {
					$djs_img[]['pic'] = $pic;
				}
				$cards[5] = array(
					'card_type'=>3,
					'card_type_name'=>'多图',
					'title'=>'本电台DJ',
					'scheme'=>'sinaweibo://pageuserlist?containerid='.$pageid.'_-_djs&title='.urlencode('本台DJ'),
					'itemid'=>$pageid.'_-_djs',
					'pics'=>$djs_img,
				);
			}
		}	
		if(!empty($listener_arr)){
			//新版采用7个用户样式
			if ($vp >= self::VERSION4) {
				$listener_uid = array();
				foreach ($listener_arr as $uid => $pic) {
					$listener_uid[]['uid'] = $uid;
				}
				$cards[6] = array(
					'card_type'=>24,
					'card_type_name'=>'相关用户',
					'title'=>'正在收听本频率的人',
					'scheme'=>'sinaweibo://pageuserlist?containerid='.$pageid.'_-_listener&count=10&title='.urlencode('正在收听本频率的人'),
					'itemid'=>$pageid.'_-_listener',
					"roundedcorner"=>1,
					'display_arrow'=>1,
					'weibo_need'=>'user',
					'users'=>$listener_uid,
				);
			} else {
				$listener_img = array();
				foreach ($listener_arr as $uid => $pic) {
					$listener_img[]['pic'] = $pic;
				}
				$cards[6] = array(
					'card_type'=>3,
					'card_type_name'=>'多图',
					'title'=>'正在收听本频率的人',
					'scheme'=>'sinaweibo://pageuserlist?containerid='.$pageid.'_-_listener&count=10&title='.urlencode('正在收听本频率的人'),
					'itemid'=>$pageid.'_-_listener',
					'pics'=>$listener_img,
				);
			}
		}
		$cards[7] = array(
				'card_type'=>3,
				'card_type_name'=>'多图',
				'title'=>'更多热门电台',
				'scheme'=>'sinaweibo://pageproductlist?containerid='.$pageid.'_-_hotradios&count=10&title='.urlencode('更多热门电台'),
				'containerid'=>$pageid.'_-_hotradios',
				'display_arrow'=>1,
				'pics'=>$radio_pic,
		);

		//兼容旧版格式
		if ($vp < self::VERSION5) {
			if (isset($cards[1])) {
				unset($cards[1]['itemid']);
				$cards[1]['card_id'] = '';
				$cards[1]['apps'][1]['scheme'] = 'sinaweibo://pagedetailinfo?pageid='.$pageid.'&cardid=list&title='.urlencode('今日节目单');
			}
			if (isset($cards[1]['apps'][2])) {
				$cards[1]['apps'][2]['scheme'] = 'sinaweibo://pagesearchweibo?pageid='.$pageid.'&cardid=searchweibo&title='.urlencode('听友互动').'&q='.urlencode($keyword);
			}
			if (isset($cards[2])) {
				unset($cards[2]['itemid']);
				$cards[2]['card_id'] = 'list';
			}
			if (isset($cards[3])) {
				unset($cards[3]['itemid']);
				$cards[3]['card_id'] = '';
				$cards[3]['scheme'] = 'sinaweibo://sendweibo?pageid='.$pageid.'&pagetitle='.urlencode($keyword).'&title='.urlencode('参与互动');
			}
			if (isset($cards[4])) {
				unset($cards[4]['itemid']);
				$cards[4]['card_id'] = '';
				$cards[4]['scheme'] = 'sinaweibo://pagesearchweibo?pageid='.$pageid.'&cardid=searchweibo&title='.urlencode('听友互动').'&q='.urlencode($keyword);
			}
			if (isset($cards[5])) {
				unset($cards[5]['itemid']);
				$cards[5]['card_id'] = 'djs';
				$cards[5]['scheme'] = 'sinaweibo://pageuserlist?pageid='.$pageid.'&cardid=djs&title='.urlencode('本台DJ');
			}
			if (isset($cards[6])) {
				unset($cards[6]['itemid']);
				$cards[6]['card_id'] = 'listener';
				$cards[6]['scheme'] = 'sinaweibo://pageuserlist?pageid='.$pageid.'&cardid=listener&count=10&title='.urlencode('正在收听本频率的人');
			}
			if (isset($cards[7])) {
				unset($cards[7]['itemid']);
				$cards[7]['card_id'] = 'htoradios';
				$cards[7]['scheme'] = 'sinaweibo://pageproductlist?pageid='.$pageid.'&cardid=hotradios&count=10&title='.urlencode('更多热门电台');
			}
		}
	
		$data = array();
		if(!empty($pageInfo)) {
			$data= array(
				'pageInfo'=>$pageInfo,
				'cards'=>$cards,
			); 
		
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		
	}
}
new weiboPageIndex(RADIO_APP_SOURCE);
?>
