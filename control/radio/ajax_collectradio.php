<?php

/**
 * Project:     radio
 * File:        ajax_collectradio.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class CollectRadio extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		//$this->para['uid'] = intval(request::post('uid', 'STR'));
		$this->para['rids'] = request::post('rids', 'STR');
		$this->para['type'] = request::post('type', 'STR');
		
		$this->para['type'] = !empty($this->para['type']) ? $this->para['type'] : "add";

		//参数检测处理
		if((empty($this->para['rids']) && ($this->para['type'] == 'add')) || ($this->para['type'] != "add" && $this->para['type'] != "set")) {
			$this->setCError('M00009', '参数错误');
			return false;
		}				
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$cur_user = $person->currentUser();
		$this->para['uid']=$cur_user['id'];
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$collection = $mRadio->getRadioCollection(array($this->para['uid']));
		$rids_array = $collection[$this->para['uid']]['rids'];
		//增加权限判断
		if (empty($cur_user['id'])){
			$jsonArray['code'] = 'A00003';//未登录
		}
		elseif(count($rids_array) >= RADIO_COLLECTION_MAX && $this->para['type'] == 'add'){
			$jsonArray['code'] = 'RDO003';//收藏过多
		}
		else{
			if(!empty($this->para['rids'])){
				$tmp_rids = explode(',',$this->para['rids']);
				
				if($this->para['type'] == 'set'){
					$rids_array = array();
				}
				foreach($tmp_rids as $v){
					if(is_numeric($v)){
						if(empty($rids_array[intval($v)])){
							$rids_array[$v]['rid'] = intval($v);
						}	
					}			
				}
			}
			else{
				$rids_array = array();
			}
			
			if(count($rids_array) > RADIO_COLLECTION_MAX){
				$jsonArray['code'] = 'RDO003';
			}
			else{
				$rids = serialize($rids_array);
				
				$args = array('uid' => $this->para['uid']
							,'rids' => $rids);
				$result = $mRadio->addRadioCollection($args);		
				if($result['errorno'] == 1 && count($result['result']) > 0){        	
					$jsonArray['code'] = 'A00006';
					//记录用户行为记录
					$args = array(
						'optime'   => date("Y-m-d H:i:s", time()),
						'serverip' => $_SERVER['SERVER_ADDR'],
						'typeid'   => RADIO_USER_COLLECT,
						'opip'     => check::getIp(),
						'opuid'    => $this->para['uid'] >0 ? $this->para['uid'] : '',
						'orgin'    => '',
						'radioid'  => $this->para['rids'],
						'result'   => 'true',
						'mblogid'  => '',
						'from'     => 0,
					);
					if($this->para['type'] != 'set'){
						$radio_infos = $mRadio->getRadioInfoByRid(array(intval($this->para['rids'])));
						$radio_info = $radio_infos['result'][$this->para['rids']];
						$radio_name = $radio_info['name'].' '.$radio_info['fm'];
						$radio_url = RADIO_URL.'/'.$radio_info['province_spell'].'/'.$radio_info['domain'];
						$radio_intro = $radio_info['intro'];
						if(!empty($radio_intro)){
							if(mb_strlen($radio_intro,'UTF-8')>40){
								 $radio_intro = mb_substr($radio_intro,0,40,'UTF-8')."……";
							}
						}
						$image_url = $mRadio->generateFeedRadioThumbnail($radio_info['img_path']);
						$image = array(
							'url'=>$image_url,
							'width'=>'98',
							'height'=>'98',
							);
						$object = array(
						'id' => RADIO_SUBJECT_ID.':'.$this->para['rids'],
						'display_name' => $radio_name,
						'summary' => !empty($radio_intro) ? $radio_intro : $radio_name,
						'url' => $radio_url,
						'object_type'=> 'webpage',
						'image'=>$image
						);
						$object_json = json_encode($object);
						$data = array(
							'tpl_id'=> RADIO_TPL_ID,
							'object_id'=> RADIO_SUBJECT_ID.':'.$this->para['rids'],
							'object'=>$object_json,
						);
						$activity = $mRadio->addMblogActivity($data);
					}
				}
				else{
					$jsonArray['code'] = 'RDO004';
				}
			}			
		}		
		$this->display($jsonArray, 'json');
	}
}

new CollectRadio(RADIO_APP_SOURCE);
?>