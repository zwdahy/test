<?php
/**
 * Project:     电台管理后台接口
 * File:        set_area_recommend.php
 * 
 * 设置地区首页右侧推荐
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_area_recommend.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setAreaRecommend extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['upuid'] = request::post('upuid', 'STR');						//更新人UID
		$this->para['province_id'] = request::post('province_id', 'INT');			//省级id

		$this->para['radio_right'] = request::post('radio_right', 'STR');			//右侧推荐图单选按钮值
		$this->para['right_pic_url'] = request::post('right_pic_url', 'STR');		//右侧图片路径
		$this->para['right_link_url'] = request::post('right_link_url', 'STR');		//右侧链接地址

		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['province_id'] == 0){
			$error = array('errmsg' => '请选择电台地区');
			$this->display($error, 'json');
			return true;
		}
		
		//参数的处理
		//右侧推荐图是否显示
		if ('show' == $this->para['radio_right']) {
			//输入的右侧的地址和链接
			$temRightArr = array();
			$temRightArr['right_pic_url'] = $this->para['right_pic_url'];
			$temRightArr['right_link_url'] = $this->para['right_link_url'];
			$right_picture = serialize($temRightArr);
		}else{
			$right_picture = '';
		}
		
		$args = array(
			'province_id' => $this->para['province_id'],
			'right_picture' => $right_picture,
			'upuid' => $this->para['upuid']
		);
		
		$result = $obj->setRadioProvince($args);
		
		$data = array();
		if($result['errorno'] == 1) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
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

new setAreaRecommend(RADIO_APP_SOURCE);
?>