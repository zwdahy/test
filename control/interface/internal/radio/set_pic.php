<?php
/**
 * Project:     电台管理后台接口
 * File:        set_pic.php
 * 
 * 默认轮播大图设置
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_pic.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setPic extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['pic_url'] = request::post('pic_url', 'STR');		// 轮播图的路径
		$this->para['link_url'] = request::post('link_url', 'STR');		// 轮播图链接地址
		
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		$this->para['type'] = request::post('type', 'STR');			// 是否是更新操作
		
		return true;
	}
	protected function action() {		
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	
		//是更新操作需要先删除pic表中的数据
		if ($this->para['type'] == 'set') {
			$picInfoList = $obj->getPicInfo(array());
			
			//处理特殊的ID（特殊ID用作微电台的数据统计）
			foreach($picInfoList['result']['content'] as $key=>$value){
				if(RADIO_STATISTIC_PIC_ID == (int)$value['pic_id']||6==(int)$value['pic_id']){
					$radio_static_flag = 1;
					unset($picInfoList['result']['content'][$key]);
				}
			}
			$newPicList = $picInfoList['result']['content'];
			foreach ($newPicList as $value){
				$pic_ids[] = $value['pic_id'];
			}
			
			$tem_pic_ids = implode(',',$pic_ids);
			$rese = $obj->delPicInfo(array('pic_id'=>$tem_pic_ids));
		}
		
		//处理pic_url 和 link_url rid,然后分别入库操作
		$pic_url = $this->para['pic_url'];
		$link_url = $this->para['link_url'];
		
		$pic_arr = explode(',',$pic_url);
		$link_arr = explode(',',$link_url);
	
		$need_pic_arr = array();
		$i = 0;
		foreach ($pic_arr as $value){
			$need_pic_arr[$i]['pic_url'] = $value;
			$i++ ;
		}
		$j = 0;
		foreach ($link_arr as $val){
			$need_pic_arr[$j]['link_url'] = $val;
			$j++ ;
		}
				
		//循环插入轮播大图数据库中
		foreach ($need_pic_arr as $key => $va){
			$args = array(
				'pic_id' => $key+1, 
				'img_url' => $va['pic_url'],
				'link_url' => $va['link_url'],
				'upuid' => $this->para['upuid'],
			);
			$rs = $obj->addPicInfo($args);
			if($rs['errorno'] != 1){
				$result['errorno'] = $rs['errorno'];
			}
		}
	
		$data = array();
		if($rs['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $rs
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
new setPic(RADIO_APP_SOURCE);
?>