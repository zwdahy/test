<?php
/**
 * Project:     电台管理后台接口
 * File:        add_area_pic.php
 * 
 * 添加地区首页
 * 
 * @link http://i.service.t.sina.com.cn/radio/add_area_pic.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */

include_once SERVER_ROOT . 'config/radioconf.php';
class addAreaPic extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['upuid'] = request::post('upuid', 'STR');						//更新人UID
		$this->para['province_id'] = request::post('province_id', 'INT');			//省级id


		$this->para['sort_pics'] = request::post('sort_pics', 'STR');				//推荐图序号
		$this->para['check_pics'] = request::post('check_pics', 'STR');				//推荐图checkbox
		$this->para['select_pics'] = request::post('select_pics', 'STR');			//推荐图select下拉框
		$this->para['picpaths'] = request::post('picpaths', 'STR');					//推荐图路径
		$this->para['link_urls'] = request::post('link_urls', 'STR');				//推荐图链接

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
		//对轮播大图的处理
		$sort_pics = explode(',',$this->para['sort_pics']);
		$check_pics = explode(',',$this->para['check_pics']);
		$select_pics = explode(',',$this->para['select_pics']);
		$pic_urls = explode(',',$this->para['picpaths']);
		$link_urls = explode(',',$this->para['link_urls']);
		$pic_arr = array();
		$i = 0;
		foreach ($sort_pics as $value){
			$pic_arr[$i]['pic_sort'] = $value;
			$i++;
		}
		$j = 0;
		foreach ($check_pics as $value){
			$pic_arr[$j]['pic_check'] = $value;
			$j++;
		}
		$k = 0;
		foreach ($select_pics as $value){
			$pic_arr[$k]['pic_select'] = $value;
			$k++;
		}
		$h = 0;
		foreach ($pic_urls as $value){
			$pic_arr[$h]['pic_path'] = $value;
			$h++;
		}
		$m = 0;
		foreach ($link_urls as $value){
			$pic_arr[$m]['pic_link'] = $value;
			$m++;
		}
		
		
		//重新排序，把后填写的序号放到重复的前面
		// 取得列的列表
		foreach ($pic_arr as $key => $val) {
		    $pic_sort[$key] = $val['pic_sort'];
		    $pic_key[$key] = $key;
		}
		array_multisort($pic_sort, SORT_ASC,$pic_key, SORT_DESC, $pic_arr);
		
		$ke = 0;
		foreach ($pic_arr as $key=>$value){
			$pic_arr[$ke]['pic_sort'] = $key+1;
			$ke++;
		}
	
		$rolling_picture = serialize($pic_arr);
		
	
		$args = array(
			'province_id' => $this->para['province_id'],
			'rolling_picture' => $rolling_picture,
			'online' => 2,//默认不上线
			'upuid' => $this->para['upuid']
		);
		$result = $obj->addRadioProvince($args);
		
		
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

new addAreaPic(RADIO_APP_SOURCE);
?>