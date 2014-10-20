<?php
/**
 * attention data
 *
 *
 * @package data
 * @author wangchao<wangchao@staff.sina.com.cn>
 * @copyright (c) 2010, 新浪网 MiniBlog All rights reserved.
 */

class dAttention extends data {
	/**
	 * 获取关注关系
	 * @param $uid string 当前用户
	 * @param $fuids string 要判断的关系，支持批量，多个uid以“，”分割开
	 * @param $cacheTime int mc缓存时间 单位S
	 */
	public function getFriendship($uid, $fuids, $cacheTime=30) {
		$items = array_unique(explode(',', $fuids));
		$res = $this->_connectMc();
		if($cacheTime != 0) {
			$itemKey = array();
			foreach ($items as $item) {
				$itemKey[] = $uid.'_'.$item;
			}
			$resultFromMC = $res->getMulti($itemKey);
			if($resultFromMC !== false) {
				$key = array_keys($resultFromMC);
				$items = array_diff($items,$key);
			}
		}//var_dump('resultFromMC:',$resultFromMC);
		$api = clsFactory::create ('libs/api', 'InternalAPI');
		$args = array(
			'uid' => $uid,
			'fuids' => implode(',', $items)
		);
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$resultFromApi = $api->getRelation($args);
		if($resultFromApi != false && $cacheTime > 0) {
			$cacheItem = array();
			$resultApi = array();
			foreach ($resultFromApi as $key => $value) {
				$cacheItem[$uid.'_'.$key] = $value;
				$resultApi[$key] = $value['relation'];
			}
			//var_dump('set:',$res->setMulti($cacheItem, time() + $cacheTime));
			$res->setMulti($cacheItem, time() + $cacheTime);
		}
		$resultMC = array();
		if(!empty($resultFromMC)) {
			foreach ($resultFromMC as $mkey => $mvalue) {
				$resultMC[substr($mkey, strpos('_'))] = $mvalue;
			}
		}//var_dump($resultMC);var_dump($resultMC, $resultApi);
		$result = array_merge($resultMC, $resultApi);
		return $result;
	}
	/**
	 * 创建关注关系
	 * $args array 接口参数
	 */
	public function createFriendship($args) {
		$api = clsFactory::create ('libs/api', 'RestAPI');
		$api->setAppKey(OPENAPI_APP_KEY);
		return $api->friendships_create($args);
	}
	
	private function _connectMc() {
		return $this->connectMc(CACHE_RESOURCE);
	}
}
?>