<?php
/**
 * 电台列表页(control层)
 *
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
//include_once(SERVER_ROOT.'config/area.php');
//include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
//include_once SERVER_ROOT."config/radioareaspell.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";
//date_default_timezone_set('Asia/Shanghai');

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioArea extends control{
	protected function checkPara(){
		//$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		//假数据
		//$this->para['cuserInfo'] = $person->currentUser();
		/*测试用数据
		$this->para['cuserInfo']['uid'] ='2026257053';
		$this->para['cuserInfo']['nick'] = 'chaos_gao';
		$this->para['cuserInfo']['portrait'] = 'http://tp2.sinaimg.cn/2026257053/50/1300153625/1';*/
	}

	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$cuid=$mPerson->getCurrentUserUid();
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$data=$mRadio->formatScope();
		$data['page_title'] = sprintf(RADIO_TITLE, "微电台首页");		//获取二级域名
		$data['backurl'] = RADIO_URL;
		//获取多少地区多少电台
		//获取认为编写的数据
		$radio_statistic_info = $mRadio->getPicInfo(array('pic_id'=> RADIO_STATISTIC_PIC_ID));
		//获取多少地区多少电台	**新版**
		$radio_statistic_info_new = $mRadio->getRadioPageById(RADIO_STATISTIC_PIC_ID_NEW);
		$radio_statistic_info_new = $radio_statistic_info_new['result'][0]['block_text'];
		$radio_statistic_info_new = json_decode(urldecode($radio_statistic_info_new),true);
		if($radio_statistic_info['errorno']==1){
			$radio_statistic_info = json_decode($radio_statistic_info['result']['content'][0]['img_url'],true);
			$radio_statistic_info = !empty($radio_statistic_info_new)?$radio_statistic_info_new:$radio_statistic_info;
			$temp = array();
			if($radio_statistic_info['area_nums'] == '%s'){
				//获取计算出的数据
				$temp = $mRadio->getStaticData();
				$radio_statistic_info['area_nums'] = $temp['areas'];
			}else{
				$radio_statistic_info['area_nums'] = ltrim($radio_statistic_info['area_nums'],'%');
			}
			if($radio_statistic_info['radio_nums'] == '%s'){
				$temp =$temp?$temp:$mRadio->getStaticData();
				$radio_statistic_info['radio_nums'] = $temp['radios'];
			}else{
				$radio_statistic_info['radio_nums'] = ltrim($radio_statistic_info['radio_nums'],'%');
			}
			if($radio_statistic_info['dj_nums'] == '%s'){
				$temp = $temp?$temp:$mRadio->getStaticData();
				$radio_statistic_info['dj_nums'] = $temp['djs'];
			}else{
				$radio_statistic_info['dj_nums'] = ltrim($radio_statistic_info['dj_nums'],'%');
			}
			$data['radio_statistic_info'] = $radio_statistic_info;
		}
		//获取电台收听榜
		$data['top10']=$mRadio->getListenRank();
		$last_rids = $_COOKIE['rid'];
		$last_rids = explode('|',$last_rids);
		$last_radioinfo = $mRadio->getRadioInfoByRid($last_rids);
		$data['last_radioinfo'] = array_values($last_radioinfo['result']);
		//获取推荐图
		$recommend_picinfo = $mRadio->getRecommendPicNow();
		$recommend_picinfo = $recommend_picinfo['result'];
		//error_log(strip_tags(print_r($recommend_picinfo, true))."\n", 3, "/tmp/err.log");
		//获取长图 广告图
		$ad['link'] = RADIO_URL."/app";
		$ad['img'] = RADIO_APP_PIC_PATH;
		$data['ad'] = $ad;
		if(count($recommend_picinfo)>2){
			//随机选取两个
			$keys = array_rand($recommend_picinfo,2);
			$data['recommend_picinfo'][0]=$recommend_picinfo[$keys[0]];
			$data['recommend_picinfo'][1]=$recommend_picinfo[$keys[1]];
		}else{
			$data['recommend_picinfo'] = $recommend_picinfo;
		}
		//获取微电台 收听榜图
		$radio_rank_pic = $mRadio->getRadioPageInfoByBlockName("radio_rank",1);
		if($radio_rank_pic['errorno'] == 1){
			$radio_rank_pic = $radio_rank_pic['result'];
			if(count($radio_rank_pic)>2){
				//随机选取两个
				$keys = array_rand($radio_rank_pic,2);
				$data['radio_rank_pic'][0]=$radio_rank_pic[$keys[0]];
				$data['radio_rank_pic'][1]=$radio_rank_pic[$keys[1]];
			}else{
				$data['radio_rank_pic'] = $radio_rank_pic;
			}
		}else{
			$data['radio_rank_pic'] = array();
		}

		//获取热点预告图片
		$hot_preview_pic = $mRadio->getRadioPageInfoByBlockName("hot_preview_pic",1);
		if($hot_preview_pic['errorno'] == 1){
			$hot_preview_pic = $hot_preview_pic['result'];
			//选出没过期的
			foreach($hot_preview_pic as $k=>&$v){
//				if(time()>strtotime($v['end_time']) || time()<strtotime($v['start_time'])){
				if(date("Ymd")>date("Ymd",strtotime($v['end_time'])) || date("Ymd")<date("Ymd",strtotime($v['start_time']))){
					unset($hot_preview_pic[$k]);
					continue;
				}
				$res = $mRadio->getRadioInfoByRid(array($v['rid']));
				$v ['radio_url'] = $res['result'][$v['rid']]['radio_url'];

			}
			unset($v);
			if(count($hot_preview_pic)>1){
				//随机选取1个
				$key = array_rand($hot_preview_pic);
				$data['hot_preview_pic'][0]=$hot_preview_pic[$key];
			}else{
				$data['hot_preview_pic'] = array_values($hot_preview_pic);
			}
		}else{
			$data['hot_preview_pic'] = array();
		}

		//获取热点预告电台
		$hot_preview_radio = $mRadio->getRadioPageInfoByBlockName("hot_preview",1);
		if($hot_preview_radio['errorno'] == 1){
			unset($temp);
			$hot_preview_radio = $hot_preview_radio['result'];
			foreach($hot_preview_radio as $k=>$v){
//				if(time()>strtotime($v['end_time']) || time()<strtotime($v['start_time'])){
				if(date("Ymd")>date("Ymd",strtotime($v['end_time'])) || date("Ymd")<date("Ymd",strtotime($v['start_time']))){
					unset($hot_preview_radio[$k]);
				}
			}
			foreach($hot_preview_radio as $k=>$v){
				$temp = $mRadio->getRadioInfoByRid(array($v['rid']));
				if($temp['errorno'] == 1){
					$temp = $temp['result'][$v['rid']];
					$data['hot_preview_radio_info'][$k] = $temp;
					$data['hot_preview_radio_info'][$k]['introduce'] = $v['block_text'];
				}
			}
		}else{
			$data['hot_preview_radio_info'] = array();
		}
		//获取明星主播
		$dj_star = $mRadio->getRadioPageInfoByBlockName("dj_star",1);
		if($dj_star['errorno'] == 1){
			$dj_star = $dj_star['result'];
			$temp = array();

			if(count($dj_star)>3){
				//随机选取两个
				$keys = array_rand($dj_star,3);
				$temp[0]=$dj_star[$keys[0]];
				$temp[1]=$dj_star[$keys[1]];
				$temp[2]=$dj_star[$keys[2]];
			}else{
				$temp = $dj_star;
			}
			foreach($temp as $k=>$v){
				$data['dj_star_info'][$k] = $mRadio->getSimpleNameCard($v['block_uid']);
				$data['dj_star_info'][$k]['radio_url'] = $v['block_text'];
			}

		}else{
			$data['dj_star_info'] = array();
		}

		//获取收藏电台的数量
		if($cuid>0){
			$collection = $mRadio->getCollectionList($cuid);
			$data['collection_num'] = intval(count($collection));
		}
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
//		print '<pre>';
//		print_r($data);
//		exit;
		$this->display ( array ('tpl' => array ('radio/areaindex.html' ), 'data' => $data ), 'html' );
	}
}

new RadioArea(RADIO_APP_SOURCE);
?>
