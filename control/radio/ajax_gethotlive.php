<?php

/**
 * Project:     radio
 * File:        ajax_gethotlive.php
 * 
 * 获取某一天的热播节目
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
//include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetHotLive extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		$this->para['rid'] = intval($_POST['rid']);//当前正在收听的电台id	非必须
		$this->para['type'] = intval($_POST['type']);//节目类型 非必须
		$this->para['now'] = intval($_POST['now']);//是否需要当前, 0值区当前,1获取全部
		$this->para['page'] = intval($_POST['page']);//第几页
		$this->para['size'] = intval($_POST['size']);//页面大小

	 //	$this->para['now'] = 0;//是否需要当前 0当前 1全部
	//	$this->para['type'] = 12;//节目类型 非必须
//		$this->para['page'] = 1;//显示的当前页1-n
//		$this->para['size'] = 10;//每页显示数量
		
		//参数检测
		if($this->para['now'] !==0 && $this->para['now']!==1) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
		$this->para['page'] = !empty($this->para['page'])?$this->para['page']:1;
		$this->para['size'] = !empty($this->para['size'])?$this->para['size']:20;
		
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}

		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取正在所有热门节目
		$live_pinfo = $mRadio->getHotProgramByDay2();
		if(empty($live_pinfo['error'])){
			$types = array(array('id'=>0,'program_type'=>'热门','sort'=>0));
			if($this->para['now']==0){
				//获取当前正在播放的节目
				$now = time();
				$program_ids = array();
				foreach($live_pinfo as $k=>&$v){
					if($now<strtotime($v['begintime'])||$now>strtotime($v['endtime'])||$v['is_del']!=0){
						unset($live_pinfo[$k]);
						continue;
					}
					if(is_array($v['dj_info'])){
						$v['dj_info'] = array_values($v['dj_info']);
					}else{
						$v['dj_info'] = array();
					}
					$v['now']=0;
					if($v['rid']==$this->para['rid']){
						$v['now']=1;
					}
					$program_ids[] = $v['program_id'];
				}
				unset($v);
				$type = $mRadio->getRadioProgramType2($program_ids);
				if($type['errorno'] == 1){
					$type = $type['result'];
					foreach($type as $k=>&$v){
						if(empty($v)){
							unset($type[$k]);
							continue;
						}
						foreach($v as $v2){
							$types[$v2['sort']] = array('id'=>$v2['id'],'program_type'=>$v2['program_type'],'sort'=>$v2['sort']);
						}
					}
					unset($v);
				}
				$mRadio->setCacheData(MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES_NOW,$types,7200);
			}
			$live_pinfo=array_values($live_pinfo);
			if(!empty($this->para['type'])){
				//按节目类型分类
				$res=array();
				foreach($live_pinfo as $k=>&$v){
					if(empty($v['type'])){
						continue;
					}
					foreach($v['type'] as $v2){
						if($v2['id']==$this->para['type']){
							$res[$v['program_id']] = $v;
						}
					}
				}
				unset($v);
				$live_pinfo=$res;
				if(empty($live_pinfo)){
					$live_pinfo=array();
				}
			}
			//全部节目进行分页
			//总的记录数
			$count=count($live_pinfo);
			$pages=ceil($count/$this->para['size']);
			$start=($this->para['page']-1)*$this->para['size'];
			$live_pinfo=array_slice($live_pinfo,$start,$this->para['size']);
			$jsonArray['code'] = 'A00006';	
		}else{
			$live_pinfo=array();
			$jsonArray['code'] = 'A00010';//mc问题反馈信息
		}
		$jsonArray['data']['live_pinfo'] = $live_pinfo;
		$jsonArray['data']['types'] = $types;
		$jsonArray['data']['pages'] = $pages;

		$this->display($jsonArray, 'json');
	}
}

new GetHotLive(RADIO_APP_SOURCE);
?>