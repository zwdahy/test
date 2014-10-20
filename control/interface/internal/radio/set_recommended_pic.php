<?php
/**
 * Project:     电台管理后台接口
 * File:        set_recommended_pic
 * 
 * 推荐图设置
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_recommended_pic.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setRecommendedPic extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
	
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		$this->para['type'] = request::post('type', 'STR');	
		$this->para['pic_arr'] = htmlspecialchars_decode(request::post('pic_arr','STR'));//推荐图的信息
		return true;
	}
	protected function action() {		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	
		//处理推荐图信息
		$pic_info = json_decode($this->para['pic_arr'],true);
		//先获取推荐图信息，如果存在，先删除，然后新增推荐图信息
		$type=array('type'=>$this->para['type']);
		$pic_result = $obj->getRecommendFromDB($type);
		if(count($pic_result) > 0){
			//存在数据，先执行删除操作
			$obj->delRadioRecommend($this->para['type']);
		}
		date_default_timezone_set('PRC');
		$now =time();
		$today = date("Y-m-d H:i:s",$now); 
		foreach($pic_info as $key=>$val){
			$args = array(
				'rid' => $val['radios'],
				'province_id' => $val['province'],
				's_time' => $val['pic_start_time'],
				'e_time' => $val['pic_end_time'],
				'url' => $val['pic_urls'],
				'sort' => $val['sort'],
				'week_day' => $val['week_day'],
				'upuid' => $this->para['upuid'],
				'uptime'=>$today,
				'type' => $this->para['type']
			);
			$res = $obj->addRadioRecommend($args);
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
new setRecommendedPic(RADIO_APP_SOURCE);
?>