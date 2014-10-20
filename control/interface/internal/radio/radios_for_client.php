<?php
/**
 * Project:     获取过滤不稳定流后的电台信息 for openAPI
 * File:        radios_for_client.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/radios_for_client.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
include_once SERVER_ROOT . 'config/radiostream.php';
class radiosForClient extends control {
	protected function checkPara() {
		$this->para['version'] = request::get('version', 'STR');                    //登录用户的ID
        return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');    
		$data = array();
		
		//从音频源的接口中  获取不稳定的音频数据
		$radio_mu_ids = $obj->getUnableMu();
		foreach($radio_mu_ids as $key=>$val){
			$unuse_radio_id[] = $val['rid']; 
		}

		//获取所有电台信息
		$radios = $obj->getAllOnlineForOpen(false);
		if($radios['result']['version'] == $this->para['version']){
			//返回提示当前是最新的电台所有信息
			$data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-1,'error'=>'当前版本是最新版本，无需更新');
		}else{
			//返回最新版本的电台信息
			if(1 == $radios['errorno'] && isset($radios['result']['version'])){
				//获取mu信息
				global $RADIO_STREAM;
				if(count($radios['result']['radios']) > 0){
					foreach($radios['result']['radios'] as $key => &$val){
						$rid = $val['rid'];
						
						//过滤掉不稳定的音频流的电台信息
						if(is_array($unuse_radio_id)){
							if(in_array($rid,$unuse_radio_id)){
								unset($radios['result']['radios'][$rid]);
							}
						}
						
						$val['mu'] = $RADIO_STREAM[$rid]['mu'];
						unset($val['tag']);
						unset($val['source']);
						unset($val['recommend']);
						unset($val['uid']);
						unset($val['url']);
						unset($val['city_id']);
						unset($val['feed_require']);
						unset($val['search_type']);
						unset($val['right_picture']);
						unset($val['admin_uid']);
						unset($val['first_online_time']);
						unset($val['admin_url']);
						unset($val['name']);
						unset($val['fm']);
						unset($val['isnew']);
						unset($val['province_name']);
						$tem_province_ids[] = $val['province_id'];
					}
				}		
				
				$radios['result']['radios'] = array_merge($radios['result']['radios']);
				
				//通过DB获取最新的areas列表，保证最新的地区列表，并去掉不存在或者下线的地区列表
				$areas = $obj->getAreaList(true);
				foreach($areas['result'] as $key=>$val){
					if(!in_array($val['province_id'],$tem_province_ids)){
						unset($areas['result']["$key"]);
					}
				}
				$radios['result']['areas'] = array_merge($areas['result']);
				$data = $radios['result'];
			}else{
				$data = array(
					'request' => $_SERVER['SCRIPT_URI'],
					'error_code' => -9,
					'error' => '获取电台信息失败'
				);
			}
		}
		$this->display($data, 'json');
		return true;
	
	}
}
new radiosForClient(RADIO_APP_SOURCE);
?>
