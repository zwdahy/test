<?php
/**
 * Project:     电台管理后台接口
 * File:        set_stats.php
 * 
 * 设置微电台概况
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_stats.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setStats extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		$this->para['static_nums'] = htmlspecialchars_decode(request::post('static_nums','STR'));//电台数据统计
		return true;
	}
	protected function action() {		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	
		//获取有无特殊ID的信息
		$picInfoList = $obj->getPicInfo(array());
		//处理特殊的ID（特殊ID用作微电台的数据统计）
		foreach($picInfoList['result']['content'] as $key=>$value){
			if(RADIO_STATISTIC_PIC_ID == (int)$value['pic_id']){
				$radio_static_flag = 1;
			}
		}
		
		//存在的基础上就是更新数据
		if(1 == $radio_static_flag){
			$args = array(
					'pic_id' => RADIO_STATISTIC_PIC_ID, 
					'img_url' => $this->para['static_nums'],
					'link_url' => '',
					'upuid' => $this->para['upuid']
			);
			$result = $obj->updatePicInfo($args);
		}else{
			//插入特殊处理的电台统计数据
			$args = array(
					'pic_id' => RADIO_STATISTIC_PIC_ID, 
					'img_url' => $this->para['static_nums'],
					'link_url' => '',
					'upuid' => $this->para['upuid']
			);
			$result = $obj->addPicInfo($args);
		}
		
		
		$data = array();
		if($result['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result
			//$result['result'],
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
new setStats(RADIO_APP_SOURCE);
?>