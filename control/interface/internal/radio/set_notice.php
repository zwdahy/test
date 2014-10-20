<?php
/**
 * Project:     电台管理后台接口
 * File:        set_notice.php
 * 
 * 公告设置
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_notice.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setNotice extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
	
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		
		$this->para['notice_arr'] = htmlspecialchars_decode(request::post('notice_arr','STR'));//公告的信息
		return true;
	}
	protected function action() {		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	
		//处理公告信息
		$notice_info = json_decode($this->para['notice_arr'],true);
		//先获取公告信息，如果存在，先删除，然后新增公告信息
		$notice_result = $obj->getRadioNotice();
		if(count($notice_result) > 0){
			//存在数据，先执行删除操作
			$obj->delRadioNotice();
		}
		foreach($notice_info as $key=>$val){
			$args = array(
				'sort' => $val['sort'],
				'notice_content' => $val['notice_content'],
				'notice_start_time' => $val['notice_start_time'],
				'notice_end_time' => $val['notice_end_time'],
				'week_day' => $val['week_day'],
				'upuid' => $this->para['upuid']
			);
			$res = $obj->addRadioNotice($args);
		}

		$data = array();
		if($res['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $res
			//$result['result'],
			);
		} else {
			global $_LANG;
			$data = array(
				'errno' => -9,
				'errmsg' => $_LANG[$res['errorno']] != '' ? $_LANG[$res['errorno']] : $res['errorno']
			);
		}

		$this->display($data, 'json');
		return true;
	}
}
new setNotice(RADIO_APP_SOURCE);
?>