<?php
/**
 * Project:     电台管理后台接口
 * File:        delclassification.php
 * 
 * 删除节目单分类
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/delprogramtype.php
 * @copyright sina.com
 * @author zhanghu<zhanghu@staff.sina.com.cn>
 * @package Sina
 * @version 2.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class delProgramType extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['id'] = request::post('id', 'INT');
        if(empty($this->para['id'])){
			$this->display(array('errno' => -1, 'errmsg' => 'need id'), 'json');
        }
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');	
		$result = $obj->delRadioProgramType($this->para['id']);
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
new delProgramType(RADIO_APP_SOURCE);
?>
