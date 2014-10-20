<?php
/**
 * Project:     电台管理后台接口
 * File:       show_hot_program.php
 * 
 * province_id    地区ID，全部地区时传all，全国传1，网络传2
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_hot_program.php
 * @copyright sina.com
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radiostream.php';
class showHotProgram extends control {
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
			$hot_program_rank = $obj->getHotProgramRank(10);
			if(!empty($hot_program_rank)){
				$hot_program_rank[count($hot_program_rank)-1]['end'] = true;
			}
			if(!empty($hot_program_rank)){
				foreach($hot_program_rank as $v){
				$result[] = array(
					'rid'=>$v['vradio_id'],
					'radio_name'=>$v['pinfo']['radio_name'],
					'province_id'=>$v['pinfo']['province_id'],
					'radio_url'=>$v['pinfo']['radio_url'],
					'program_name'=>$v['pinfo']['name'],
					'orders'=>$v['orders'],
					'showtime_info'=>$v['pinfo']['showtime_info'],
					);
				}
			}
			$data = array(
					'radios' => $result,
					"total_number" => count($result)
				);
			}else{
			//获取热门节目单排行榜
				$hot_program_rank = $obj->getHotProgramRankByPid($this->para['province_id']);
				if(!empty($hot_program_rank)){
					$hot_program_rank[count($hot_program_rank)-1]['end'] = true;
				}	
				if(!empty($hot_program_rank)){
					foreach($hot_program_rank as $v){
					$result[] = array(
						'rid'=>$v['vradio_id'],
						'radio_name'=>$v['pinfo']['radio_name'],
						'province_id'=>$v['pinfo']['province_id'],
						'radio_url'=>$v['pinfo']['radio_url'],
						'program_name'=>$v['pinfo']['name'],
						'orders'=>$v['orders'],
						'showtime_info'=>$v['pinfo']['showtime_info'],
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
new showHotProgram(RADIO_APP_SOURCE);
?>
