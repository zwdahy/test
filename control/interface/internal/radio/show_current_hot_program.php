<?php
/**
 * Project:     电台管理后台接口
 * File:       show_current_hot_program.php
 * 
 * 通过province_id获取某城市当前时间段热门电台列表，若province_id=all则返回全部热门节目排行榜的前20名 
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/show_current_hot_program.php
 * @copyright sina.com
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radiostream.php';
class showCurHotProgram extends control {
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
			$live_pinfo = $obj->getHotProgramByDay2();
			$now = time();
			$live_pinfo = !empty($live_pinfo) ? $live_pinfo:array();
			if(!empty($live_pinfo)){
				foreach($live_pinfo as $k=>$v){
					if($now<strtotime($v['begintime'])||$now>strtotime($v['endtime'])||$v['is_del']!=0){
						unset($live_pinfo[$k]);
						continue;
					}
					$result[] = array(
						'rid'=>$v['rid'],
						'program_name'=>$v['program_name'],
						'radio_name'=>$v['radio_info']['name'],
						'province_id'=>$v['radio_info']['province_id'],
						'radio_url'=>$v['radio_info']['radio_url'],
						'begintime'=>$v['begintime'],
						'endtime'=>$v['endtime'],
						);
					if(count($result)>=20){
						break;
					}
				}
			}
			$data = array(
					'radios' => $result,
					"total_number" => count($result)
				);
			}else{
				$live_pinfo = $obj->getHotProgramByDay2();
				$live_pinfo = !empty($live_pinfo) ? $live_pinfo:array();
				if(!empty($live_pinfo)){
					$now = time();
					foreach($live_pinfo as $v){
						if($now<strtotime($v['begintime'])||$now>strtotime($v['endtime'])||$v['is_del']!=0){
							unset($live_pinfo[$k]);
							continue;
						}
						if($v['radio_info']['province_id'] == $this->para['province_id']){
							$result[] = array(
							'rid'=>$v['rid'],
							'program_name'=>$v['program_name'],
							'radio_name'=>$v['radio_info']['name'],
							'province_id'=>$v['radio_info']['province_id'],
							'radio_url'=>$v['radio_info']['radio_url'],
							'begintime'=>$v['begintime'],
							'endtime'=>$v['endtime'],
							);
						}
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
new showCurHotProgram(RADIO_APP_SOURCE);
?>
