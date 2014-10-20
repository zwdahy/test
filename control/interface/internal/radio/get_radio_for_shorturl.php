<?php
/*
 * Project:    返回最近更新过的电台url
 * File:        get_radio_for_shorturl.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/get_radio_for_shorturl.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class  getRadioForShorturl extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['type'] = request::post('type', 'INT');	
		return true;
	}
	protected function action() {
		date_default_timezone_set('PRC');
		$week_ago = strtotime(date("Y-m-d",mktime(0,0,0,date("m"),date("d")-7,date("Y"))));
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->getAllOnlineRadio();
		$program_url = array();
		if(!empty($result['result'])){
			foreach($result['result'] as $v){
				if(strtotime($v['uptime'])>$week_ago){
					$program_url[] = RADIO_URL.'/'.$v['province_spell'].'/'.$v['domain'];
					continue;
				}else{
					$dj = $obj->getDjInfoByRid(array($v['rid']));
					if(strtotime($dj['result'][$v['rid']]['djinfo']['uptime'])>$week_ago){
						$program_url[] = RADIO_URL.'/'.$v['province_spell'].'/'.$v['domain'];
					}
				}
				
			}
		} 
		$data = array();
		$data = array(
			'errno' => 1,
			'errmsg' => '成功',
			'result' => $program_url
		);
		
		$this->display($data, 'json');
		return true;
	}
}
new getRadioForShorturl(RADIO_APP_SOURCE);
?>