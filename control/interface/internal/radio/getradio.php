<?php
/**
 * Project:     电台管理后台接口
 * File:        getradio.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getradio.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getRadio extends control {
	protected function checkPara() {
		/*
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		*/		
		$this->para['order_field'] = request::post('order_field', 'STR');		//排序字段
		$this->para['order'] = request::post('order', 'STR');					//排序规则
		$this->para['search_key'] = request::post('search_key', 'STR');			//查询字段
		$this->para['search_value'] = request::post('search_value', 'STR');		//查询字段值
		$this->para['search_type'] = request::post('search_type', 'STR');		//查询规则
		$this->para['page'] = request::post('page', 'INT');						//分页编号
		$this->para['pagesize'] = request::post('pagesize', 'INT');				//每页显示多少条		
		
		if($this->para['pagesize'] > 50) {
			$this->para['pagesize'] = 50;		// 每页最大为50条，大于50的话取50条
		}
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$args = array(
			'order_field' => $this->para['order_field'],
			'order' => $this->para['order'],
			'search_key' => $this->para['search_key'],
			'search_value' => $this->para['search_value'],
			'search_type' => $this->para['search_type'],
			'page' => $this->para['page'],
			'pagesize' => $this->para['pagesize']
		);
		if($args['order_field'] == "province"){
			$result = $obj->getRadioList();
			$tmp = array();
			$tmp['errorno'] = 1;
			$tmp['result']['count'] = $result['count'];
			$offset = ($this->para['page']-1)*$this->para['pagesize'];
			$limit = $this->para['pagesize'];						
			$count = 0;
			foreach($result['result'] as $val){
				foreach($val as $v){
					if($count == ($offset+$limit)){
						break;
					}
					if($count >= $offset){
						$tmp['result']['content'][] = $v;						
					}
					$count++;
				}				
			}			 
			$result = $tmp;
		}
		else{
			$result = $obj->getRadio($args);
		}
		$data = array();
		if($result['errorno'] == 1) {
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
new getRadio(RADIO_APP_SOURCE);
?>