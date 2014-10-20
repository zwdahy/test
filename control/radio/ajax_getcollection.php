<?php
/**
 * 获取当前登录用户的电台收藏列表
 * 
 *ajax_getcollection.php
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}


class RadioCollection extends control{
	protected function checkPara(){
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$this->para['rid'] = request::post('type', 'STR');//当前收听的电台
	}
	
	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$scope=$mRadio->formatScope();
		$cuid=$scope['cuid'];
		if($cuid>0){//用户已经登录
			$collection = $mRadio->getCollectionList($cuid);
			if(!empty($collection)){
				//$collection=array_values($collection);
				foreach($collection as &$v){
					unset($v['source']);
					unset($v['recommend']);
					unset($v['feed_require']);
					unset($v['is_feed']);
					unset($v['search_type']);
					unset($v['right_picture']);
					unset($v['epgid']);
					unset($v['http']);
					unset($v['mu']);
					unset($v['start_time']);
					unset($v['end_time']);
					unset($v['mu']);
					if($v['rid']==$this->para['rid']){
						$v['now']=1;//正在播放
					}else{
						$v['now']=0;//不在播放
					}
				}
				unset($v);
			}
			$data=$collection;
		}else{
			$data=array();
		}
		$jsonArray['code'] = 'A00006';
		if(empty($data)){
			$jsonArray['code'] = 'A00012';
		}
		$jsonArray['data'] = array_reverse($data);
		$this->display($jsonArray, 'json');

		/*
		//根据地区参数，获取电台
		global $CONF_PROVINCE;
		
		//当前登录用户信息
		$currUserInfo = $this->para ['cuserInfo'];

		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		//获取电台收藏排行榜	
		$col_top10 = $mRadio->getCollectionRank();		
		if(!empty($col_top10)){
			$col_top10[count($col_top10)-1]['end'] = true;
			//微电台排行榜增加new标签
			foreach($col_top10 as &$val){
				$val['info']['isnew'] = $mRadio->checkRadioIsNew($val['info']['first_online_time']);
			}
		}
				
		//获取用户收藏电台信息
		$collection_list = array();
		if($currUserInfo['uid'] > 0){
			$collection = $mRadio->getCollectionList($currUserInfo['uid']);
			foreach($collection as $key => &$val){
				$val['isnew'] = $mRadio->checkRadioIsNew($val['first_online_time']);
				
				$val['province_name'] = $CONF_PROVINCE[$val['province_id']];
						
				if($val['province_id'] == 1){
					$val['province_name'] = "全国";
				}
				if($val['province_id'] == 2){
					$val['province_name'] = "网络";
				}
			}
			$collection_list = array('count' => count($collection)
									,'info' => $collection);
		}						
		
		$data['radio_collection'] = $collection_list;	//用户电台收藏列表
		
		$data['col_top10'] = $col_top10;	//热门收藏电台信息
		
		$data['cuid'] = !empty($currUserInfo['uid']) ? $currUserInfo['uid'] : 0;	//当前登录用户id
		$data['currUserInfo'] = $currUserInfo;	//当前登录用户信息
		//页面title
		$data['page_title'] = sprintf(RADIO_TITLE, "我收藏的电台");
		
		$this->display ( array ('tpl' => array ('radio/collection.html' ), 'data' => $data ), 'html' );
	}*/
	}
}

new RadioCollection(RADIO_APP_SOURCE);
?>
