<?php
/**
 * Project:     获取用户的收藏列表
 * File:        collections.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/collections.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT . 'config/radiostream.php';
class Collections extends control {
	protected function checkPara() {
		$this->para['uid'] = request::get('uid', 'STR');					//登录用户的ID
		if(empty($this->para['uid'])){
			$this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
			exit();
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//获取mu信息
		global $RADIO_STREAM;
		//获取用户收藏电台信息
		$collection = $obj->getCollectionList($this->para['uid']);
		if(count($collection) > 0){
			foreach($collection as $key => &$val){
				$rid = $val['rid'];
				if(!$rid){
					unset($collection[$key]);
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
			}
			$collection = array_merge($collection);
		}

		$data = array();
		if(isset($collection)){
			$data = array(
				'collections' => $collection,
				"total_number" => count($collection)
			);
		}else{
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => '获取收藏列表失败'
			);
		}

		$this->display($data, 'json');
		return true;
		
	}
}
new Collections(RADIO_APP_SOURCE);
?>
