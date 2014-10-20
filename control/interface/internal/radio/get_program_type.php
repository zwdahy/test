<?php
/**
 * Project:     电台管理后台接口
 * File:        getdj.php
 * 
 * 获取主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getdjinfo.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getProgramType extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
        $this->para['program_id'] = intval(request::post('program_id', 'STR'));;
	}
	protected function action() {
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
        $program_id = $this->para['program_id'];
        if(!empty($program_id)){
            $radioProgramTypes = $mRadio->getRadioProgramType($program_id);	
        }else{
            $radioProgramTypes = $mRadio->getRadioProgramTypeList();
        }
        $result = $radioProgramTypes;
		$data = array();
		if($result['errorno'] == 1) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'count'  => count($result['result']),
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
new getProgramType(RADIO_APP_SOURCE);
