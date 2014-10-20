<?php

/**
 * Project:     radio
 * File:        ajax_sort.php
 * 
 * 在全部电台中筛选电台(播放页 默认为热门电台50个)
 * 
 * @copyright sina.com
 * @author @wenda <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
class RadioSort extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}		//获取参数
		$this->para['cid'] = intval($_POST['cid']);//电台分类id
		$this->para['pid'] = intval($_POST['pid']);//地区id
        $this->para['rid'] = intval($_POST['rid']);//当前播放电台id
		//@test
//		$this->para['cid'] = 1;//电台分类id
//		$this->para['pid'] = 11;//地区id
//		$this->para['rid'] = 10;//当前播放电台id
//		if(empty($this->para['rid'])) {
//			$this->setCError('M00009', '参数错误');
//			return false;
//		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		//通过pid和cid进行结果查询电台列表
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if(empty($this->para['cid']) && empty($this->para['pid'])){
        	//等效于查询全部电台
//			$radioList = $mRadio->getAllRadioList();
//          $radioList = $radioList['result'];

			$radioList = $mRadio->getListenRank(500);
			foreach($radioList as &$v){
				$v = $v['info'];
			}
			unset($v);
            //$radioList = $radioList;
			//修改为等效于热播电台
//			print '<pre>';
//			print_r($radioList);
//			exit;
    	}else if(empty($this->para['cid'])){
			//等效于根据pid查询电台
            $radioList = $mRadio->getRadioInfoByPid(array($this->para['pid']));
            $radioList = $radioList['result'][$this->para['pid']];
		}else if(empty($this->para['pid'])){
			//等效于根据cid查询电台
        	$radioList = $mRadio->getRadioInfoByClassificationids(array($this->para['cid']));
            $radioList = $radioList['result'][$this->para['cid']];
       }else{
			//根据cid和pid查询电台
			$radioList = $mRadio->sortRadioList(array('cid' => $this->para['cid'], 'pid'=>$this->para['pid']));
			$radioList = $radioList['result'];
        }
//			print '<pre>';
//			print_r($radioList);
//			exit;
		if(!empty($radioList)){
			foreach ($radioList as $k=>&$v){
				if($v['online']==2){
					unset($radioList[$k]);
					continue;
				}
				if((time()-$v['first_online_time'])<86400*2){
					$v['is_new'] = 1;
				}else{
					$v['is_new'] = 0;
				}
				if($v['rid']==$this->para['rid']){
					$v['now']=1;
				}else{
					$v['now']=0;
				}
				if($v['program_visible']==2){
					$dj_info=array();
					$dj_uids=$mRadio->getDjInfoByRid(array($v['rid']));
					//@test 此处还需加判断
					if(empty($dj_uids['result'])){
						$v['dj_info']=$dj_info;
						continue;
					}
					$dj_uids=explode(',',$dj_uids['result'][$v['rid']]['uids']);
					$dj_uids=array_slice($dj_uids,0,4);
					foreach($dj_uids as $v2){
						$dj_info[]=$mRadio->getRadioCardByUid($v2);
					}
					$v['dj_info']=$dj_info;
	//					$tmp=explode('|',$v['info']);
	//					$v['name']=$tmp[0];
	//					$v['fm']=$tmp[1];
				}
			}
			unset($v);
			//$radioList = array_values($radioList);
			$data['radio_list'] = array_values($radioList);
			$data['cid'] = $this->para['cid'];
			$data['pid'] = $this->para['pid'];
			$data['count'] = count($radioList);
		}else{
			$data=array();
		}
		//处理反馈信息
		if(empty($data)||empty($data['radio_list'])){
			$jsonArray = array(
				'code'=>'A00012',
				'data'=> array()
			);
		}else{
			$jsonArray = array(
				'code'=>'A00006',
				'data'=>$data
			);
		}
		$this->display($jsonArray, 'json');
	}
}

new RadioSort(RADIO_APP_SOURCE);
?>
