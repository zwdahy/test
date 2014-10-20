<?php
/**
 * Project:     电台管理后台接口
 * File:       show_hot_radio.php
 * 
 *  province_id    地区ID，全部地区时传all，全国传1，网络传2
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_hot_radio.php
 * @copyright sina.com
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radiostream.php';
class showHotRadio extends control {
	protected function checkPara() {
		$this->para['province_id'] = request::get('province_id', 'STR');
		if(empty($this->para['province_id'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'errno'=>-4,'errmsg'=>'province_id参数错误'), 'json');
			exit();
		}
		if($this->para['province_id']!='all'){
			if((int)$this->para['province_id'] <= 0){
				$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'errno'=>-4,'errmsg'=>'province_id参数错误'), 'json');
				exit();
			}
		}
		/*
	if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			//log
            $ip = Check::getIp();  
            $objLog = clsFactory::create ('framework/tools/log/', 'ftLogs', 'service' );
            $objLog->switchs (1); //1 开    0 关闭
            $objLog->write ('radio', array('ip'=>$ip,'ip_url'=>$_SERVER['REQUEST_URI']), 'radio_data_open_api_error');
	
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
}*/
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['province_id']=='all'){
				//获取全国电台收听排行榜
				$listen_rank_province = $obj->getListenRank();
				//微电台排行榜增加new标签
				foreach($listen_rank_province as $key => &$val){
					$val['info']['isnew'] = $obj->checkRadioIsNew($val['info']['first_online_time']);
					if($key == count($listen_rank_province)-1){
						$val['end'] = true;
					}
				}
			$listen_rank_province = !empty($listen_rank_province) ? $listen_rank_province:array();
		if(!empty($listen_rank_province)){
				foreach($listen_rank_province as $v){
				$result[] = array(
					'rid'=>$v['info']['rid'],
					'radio_name'=>$v['info']['name'],
					'province_id'=>$v['info']['province_id'],
					'radio_url'=>'http://radio.weibo.com/'.$v['info']['province_spell'].'/'.$v['info']['domain'],
					'orders'=>$v['orders'],
					'order_change'=>$v['order_change'],
					'date'=>$v['date'],
					'isnew'=>$v['info']['isnew'],
					);
				}
			}
			$data = array(
					'radios' => $result,
					"total_number" => count($result)
				);
			}else{
				//获取地区收听排行榜
				$listen_rank_province = $obj->getListenRankByPid($this->para['province_id']);
				//微电台排行榜增加new标签
				foreach($listen_rank_province as $key => &$val){
					$val['info']['isnew'] = $obj->checkRadioIsNew($val['info']['first_online_time']);
					if($key == count($listen_rank_province)-1){
						$val['end'] = true;
					}
				}
				if(!empty($listen_rank_province)){
					foreach($listen_rank_province as $v){
					$result[] = array(
						'rid'=>$v['info']['rid'],
						'radio_name'=>$v['info']['name'],
						'province_id'=>$v['info']['province_id'],
						'radio_url'=>'http://radio.weibo.com/'.$v['info']['province_spell'].'/'.$v['info']['domain'],
						'orders'=>$v['orders'],
						'order_change'=>$v['order_change'],
						'date'=>$v['date'],
						'isnew'=>$v['info']['isnew'],
						);
					}
				}
				$data = array(
					'radios' => $result,
					"total_number" => count($result)
				);
			}
		$this->display($data, 'json');
		return true;
	}
}
new showHotRadio(RADIO_APP_SOURCE);
?>
