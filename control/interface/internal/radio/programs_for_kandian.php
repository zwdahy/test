<?php
/**
 * Project:     获取某电台节目单 (指定的四家电台 )
 * File:        programs.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/programs_for_kandian.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class ProgramsForKandian extends control {
	protected function checkPara() {	
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		
		//给看点的电台
		$kandian_radios = array(
			1 => array('rid' => 31, 'city' => 'china','domain' => 'fm915'),
			2 => array('rid' => 99, 'city' => 'china','domain' => 'fm887'),
			3 => array('rid' => 442, 'city' => 'china','domain' => 'fm900'),
			4 => array('rid' => 513, 'city' => 'china','domain' => 'am846')
		);
		foreach ($kandian_radios as $val){
			$province_spell = $val['city'];
			$domain = $val['domain'];
			$rid = $val['rid'];
			$result[] = $obj->getProgramList($rid);
			
		}
		
		//处理序列化信息
		foreach($result as $key=>$val){
			foreach($val as $kk=>$vv){
				$result[$key][$kk]['program_info'] = unserialize($vv['program_info']);
			}		
		}	
		
		$data = array();
		if((int)$result[0][0]['rid'] > 0) {
			$data = $result;
		} else {
			global $_LANG;
			$data = array(
				'request' => $_SERVER['SCRIPT_URI'],
				'error_code' => -9,
				'error' => '获取电台信息失败'
			);
		}
		$this->display($data, 'json');
		return true;
		
	}
}
new ProgramsForKandian(RADIO_APP_SOURCE);
?>
