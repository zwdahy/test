<?php
/**
 * Project:     电台管理后台接口
 * File:        setdjrank.php
 * 
 * 编辑DJ活跃榜
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setdjrank.php
 * @copyright sina.com
 * @author <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setDjRank extends control {
	protected function checkPara() {
		$this->para['infos'] = request::post('infos', 'STR');	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		$temp1 = explode(',',$this->para['infos']);
	
		$args = array();
		foreach ($temp1 as $k => $v){
			$temp2 = explode('|',$v);
			$args[$k]['sort'] = $temp2[0];
			$args[$k]['uid'] = $temp2[1];
			$args[$k]['vradio_id'] = $temp2[2];
		}
		
		$result = $obj->setDjRank($args);
		$data = array();
		if($result['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result'],
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$result['errorno']] != '' ? $_LANG[$result['errorno']] : $result['errorno']
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new setDjRank(RADIO_APP_SOURCE);
?>