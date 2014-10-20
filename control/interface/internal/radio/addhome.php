<?php
/**
 * Project:     电台管理后台接口
 * File:        addhome.php
 * 
 * 添加分类信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/addhome.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class addHome extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['pic_url'] = request::post('pic_url', 'STR');		// 轮播图的路径
		$this->para['link_url'] = request::post('link_url', 'STR');		// 轮播图链接地址
		$this->para['rid'] = request::post('rid', 'STR');				// 推荐的热门电台id
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		$this->para['type'] = request::post('type', 'STR');			// 是否是更新操作
		$this->para['static_nums'] = htmlspecialchars_decode(request::post('static_nums','STR'));//电台数据统计
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
			$obj->addRadioNotice($args);
		}

		//是更新操作需要先删除两个表中的数据
		if ($this->para['type'] == 'set') {
			$picInfoList = $obj->getPicInfo(array());
			
			//处理特殊的ID（特殊ID用作微电台的数据统计）
			foreach($picInfoList['result']['content'] as $key=>$value){
				if(RADIO_STATISTIC_PIC_ID == (int)$value['pic_id']){
					$radio_static_flag = 1;
					unset($picInfoList['result']['content'][$key]);
				}
			}
			$newPicList = $picInfoList['result']['content'];
			foreach ($newPicList as $value){
				$pic_ids[] = $value['pic_id'];
			}
			
			$hotRadioList = $obj->getHotRadio();
			$newHotList = $hotRadioList['result'];
			$rids = array_keys($newHotList);
			
			$tem_pic_ids = implode(',',$pic_ids);
			$tem_rids = implode(',',$rids);
			$rese = $obj->delPicInfo(array('pic_id'=>$tem_pic_ids));
			$res = $obj->delHotRadio(array('rid'=>$tem_rids));
			
		}
		
		//处理pic_url 和 link_url rid,然后分别入库操作
		$pic_url = $this->para['pic_url'];
		$link_url = $this->para['link_url'];
		$rid = $this->para['rid'];
		
		$pic_arr = explode(',',$pic_url);
		$link_arr = explode(',',$link_url);
		$rid_arr = explode(',',$rid);
		
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
		//循环插入热门电台信息
		foreach ($rid_arr as $kk=>$vv){
			$args = array(
				'rid' => $vv,
				'sort' => $kk+1,
				'upuid' => $this->para['upuid'],
			);
			$rs = $obj->addHotRadio($args);
			if($rs['errorno'] != 1){
				$result['errorno'] = $rs['errorno'];
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
			$dateeee = $obj->updatePicInfo($args);
		}else{
			//插入特殊处理的电台统计数据
			$args = array(
					'pic_id' => RADIO_STATISTIC_PIC_ID, 
					'img_url' => $this->para['static_nums'],
					'link_url' => '',
					'upuid' => $this->para['upuid']
			);
			$obj->addPicInfo($args);
		}
		
		
		$data = array();
		if($dateeee['errorno'] == 1) {			
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $dateeee
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
new addHome(RADIO_APP_SOURCE);
?>