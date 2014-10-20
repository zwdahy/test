<?php
/**
 * Project:    手机客户端微博page二级页面信息
 * File:       weibopage_get_second.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/weibopage_get_second.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class weiboPageSecond extends control {
	//大于此版本号的客户端PAGEID和CARDID改为CONTAINERID和ITEMID
	const VERSION5 = 5;

	protected function checkPara() {		
		$this->para['pageid'] = $_REQUEST['page_id'];		//电台id
		$this->para['cardid'] = $_REQUEST['card_id'];  //card类型
		$this->para['containerid'] = $_REQUEST['containerid'];
		$this->para['page'] = $_REQUEST['page'];
		$this->para['vp'] = $_REQUEST['v_p'];

		if ($this->para['vp'] >= self::VERSION5) {
			$arr = explode('_-_', $this->para['containerid']);
			$this->para['pageid'] = $arr[0];
			$this->para['cardid'] = $arr[1];
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
		$pageid = $this->para['pageid'];
		$page = $this->para['page'];
		$ids = explode('_',$pageid);
		$cur_rid = $ids[1];
		$cardid = $this->para['cardid'];
		//电台相关信息
		$radioInfo = $mRadio->getRadioInfoByRid(array($cur_rid));
		$radioInfo = $radioInfo['result'][$cur_rid]; 
		
		//电台介绍
		if('intro'==$cardid){
			$intro = $this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro']));
			$item = array(
			'item_name'=>'简介',
			'item_content'=>$intro
			);
			$data = array(
				'page_title'=>'电台介绍',
				'type'=>'pagedetailinfo',
				'groups'=>array(
					'0'=>array(
					'group_name'=>'',
					'group_item'=>array($item)
					)
				) 
			);
		}
		//节目单
		if('list'==$cardid){
			$card_type_name = '今日节目单';
			$today = getdate();
			if($today['wday'] == 0){
				$today['wday'] = 7;
			}
			$programs = $mRadio->getRadioProgram($cur_rid,$today['wday']);
			$begintime = strtotime($topic['begintime']);
			$endtime = strtotime($topic['endtime']);
			$program_today = $mRadio->getProgramInfo(unserialize($programs['program_info']));
			if(!empty($program_today)){
				$program_list = array();
				foreach($program_today as $k=>$v){
					$program_list[$k]['item_name'] = $v['begintime'].'-'.$v['endtime'];
					$program_list[$k]['item_content'] = $v['program_name'];
					$program_list[$k]['type'] = 'verticaltext';
				}
			}
			$data = array(
				'page_title'=>'今日节目单',
				'type'=>'detailinfo',
				'groups'=>array(
					'0'=>array(
					'group_name'=>'今日节目单',
					'group_item'=>$program_list,
					)
				) 
			);
		}
		//获取电台主持人
		if('djs'==$cardid){
			$card_type_name = '本电台DJ';
			$aDj = $mRadio->getDjDetail($cur_rid, $cur_uid);
			$page_djs = array();
			if(!empty($aDj['result'])){
				$radiodj = $aDj['result'];
				$djs = array();
				$i =0 ;
				foreach($radiodj['userinfo'] as $v){
					$v['user']['scheme'] = 'sinaweibo://userinfo?uid='.$v['user']['id'];
					$djs[$i] = $v['user'];
					$i++;
				}
			}
			if($page>1){
				for($p=($page-1)*10;$p<($page-1)*10+10;$p++){
					if(!empty($djs[$p])){
					  $page_djs[]=$djs[$p];
					}
				}
			}else{
				for($p=0;$p<10;$p++){
					if(!empty($djs[$p])){
					  $page_djs[]=$djs[$p];
					}
				}
			}
			$data = array(
				'page_title'=>'本台DJ',
				'card_type_name'=>'本台DJ',
				'count'=>$i,
				'type'=>'pageuserlist',
				'users'=>$page_djs,
				);
	  }
		
		//获取正在收听用户
		if('listener'==$cardid){
			$card_type_name = '正在收听本频率的人';
			$aListeners = $mRadio->getListeners($cur_rid, $cur_uid);
			if(!empty($aListeners['result'])){
				$aListeners = $aListeners['result'];
				$listeners = array();
				$i =0 ;
				foreach($aListeners as $v){
					foreach($v as $v1){
						$v1['scheme'] = 'sinaweibo://userinfo?uid='.$v1['id'];
						$listeners[$i] = $v1;
						$i++;
					}
				}
			}
			$page_listeners = array();
			if($page>1){
				for($p=($page-1)*10;$p<($page-1)*10+10;$p++){
					if(!empty($listeners[$p])){
						  $page_listeners[]=$listeners[$p];
					}
				}
			}else{
				for($p=0;$p<10;$p++){
					if(!empty($listeners[$p])){
						  $page_listeners[]=$listeners[$p];
					}
				}
			}
			$data = array(
				'page_title'=>'正在收听本频率的人',
				'card_type_name'=>'正在收听本频率的人',
				'count'=>$i,
				'type'=>'pageuserlist',
				'users'=>$page_listeners,
				);
		}
		//获取热门电台
		if('hotradios'==$cardid){
			$card_type_name = '热门电台';
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
					if($value['online'] == '2'){
						unset($hot_radios[$key]);
					}
				}
			}

			if(!empty($hot_radios)){
				$hot =array();
				$i=0;
				foreach($hot_radios as $v){
					$hot[$i]['pic'] = $v['img_path'];
					$hot[$i]['title_sub'] = $v['name'];
					$hot[$i]['desc1'] = $this->xssCallBackCheck(htmlspecialchars_decode($v['intro']));
					$hot[$i]['desc2'] = '';
					$hot[$i]['card_display_type'] = 0;
					if ($this->para['vp'] >= self::VERSION5) {
						$hot[$i]['scheme'] = 'sinaweibo://pageinfo?containerid=101111_'.$v['rid'];
					} else {
						$hot[$i]['scheme'] = 'sinaweibo://pageinfo?pageid=101111_'.$v['rid'];
					}
					$i++;
				}
			}
			$data =  array (
					'card_type_name' => '热门电台',
					'title'=>'热门电台',
					'type' => 'pageproductlist',
					'count'=>10,
					'pro_items' => $hot,
				);
		}
		
		//拼凑数组
		
 
		if(empty($data)){ 
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new weiboPageSecond(RADIO_APP_SOURCE);
?>
