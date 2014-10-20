<?php
/**
 * Project:     电台管理后台接口
 * File:        delradio.php
 * 
 * 根据批量RID删除电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/delradio.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class delRadio extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/
		$this->para['rids'] = request::post('rids', 'STR');		// 电台逗号分割的ID
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$args = array(
			'rids' => $this->para['rids']
		);
		
		//记录将要删除电台的地区province_id
		$pids = array();
		$rids = explode(',',$this->para['rids']);
		foreach($rids as $val){
			$radioinfo = $obj->getRadioInfoByRid(array($val));
			$radioinfo = $radioinfo['result'];
			if(array_search($radioinfo[$val]['province_id'],$pids) === false && $radioinfo[$val]['province_id'] 
> 0){
				$pids[] = $radioinfo[$val]['province_id'];
			}
		}
		
		$result = $obj->delRadio($args);
		$data = array();
		if($result['errorno'] == 1) {
			//判断电台信息表中是否存在被删除电台的province_id，不存在则删除。
			if(!empty($pids)){
				foreach ($pids as $v){
					$obj->delRadioArea($v);
				}
			}			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => $result['count'],
				'result' => $result['result']
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
new delRadio(RADIO_APP_SOURCE);
?>