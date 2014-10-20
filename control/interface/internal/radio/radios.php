<?php
/**
 * Project:     获取所有电台信息 for openAPI
 * File:        radios.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/radios.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
include_once SERVER_ROOT . 'config/radiostream.php';
class radios extends control {
	protected function checkPara() {
		$this->para['version'] = request::get('version', 'STR');                    //登录用户的ID
        return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');    
		$data = array();
		$radios = $obj->getAllOnlineForOpen(false);
//		echo '<pre>';
//		print_r($radios);exit;
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
						$val['mu'] = $RADIO_STREAM[$rid]['mu'];
						$val['http'] = $RADIO_STREAM[$rid]['http'];//后续加的。android版本不能播放
//						$val['http'] = htmlspecialchars_decode($val['http']);//后续加的。android版本不能播放
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
					unset($val);
				}
				$tem_province_ids=array_unique($tem_province_ids);
				$radios['result']['radios'] = array_merge($radios['result']['radios']);
				//通过DB获取最新的areas列表，保证最新的地区列表，并去掉不存在或者下线的地区列表
				$areas = $obj->getAreaList(true);
				foreach($areas['result'] as $key=>$val){
					if(!in_array($val['province_id'],$tem_province_ids)){
						unset($areas['result']['$key']);
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
new radios(RADIO_APP_SOURCE);
?>
