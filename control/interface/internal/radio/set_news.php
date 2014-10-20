<?php
/**
 * Project:     电台管理后台接口
 * File:        set_news.php
 * 
 * 推荐图设置
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/set_news.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setNews extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
	
		$this->para['upuid'] = request::post('upuid', 'STR');			// 更新人UID
		$this->para['type'] = request::post('type', 'STR');				//操作类型
		$this->para['news_arr'] = htmlspecialchars_decode(request::post('news_arr','STR'));//新闻的信息
		return true;
	}
	protected function action() {		
	
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		//处理新闻信息
		$news_info = json_decode($this->para['news_arr'],true);
		//获取本地时间
		date_default_timezone_set('PRC');
		$now =time();	
		$today = date("Y-m-d H:i:s",$now); 
		//增加到新闻列表页
		if('add'==$this->para['type']){
			if(!empty($news_info)){
				foreach($news_info as $key=>$val){
					$args = array(
						'title' => $val['title'],
						'url' => $val['url'],
						'list'=> $val['list'],
						'upuid' => $this->para['upuid'],
						'uptime' => $today
					);
					$res = $obj->addRadioNews($args);
					}
				}
			}
		if('set'==$this->para['type']){
			$del = array(
				'field'=>'list',
				'value'=>0
			);
			$res = $obj->delRadioNews($del);
			if(!empty($news_info)){
				foreach($news_info as $key=>$val){
					$args = array(
						'title' => $val['title'],
						'url' => $val['url'],
						'list'=> 0,
						'subtitle'=>!empty($val['subtitle']) ? $val['subtitle'] : '',
						'pic_url'=>!empty($val['pic_url']) ? $val['pic_url'] : '',
						'focus'=>!empty($val['focus']) ? $val['focus'] : 0,
						'sort'=>!empty($val['sort']) ? $val['sort'] : 0,
						'upuid' => $this->para['upuid'],
						'uptime' => $today
					);
					$res = $obj->addRadioNews($args);
					if($val['list']==1){
						$args = array(
							'title' => $val['title'],
							'url' => $val['url'],
							'list'=> $val['list'],
							'subtitle'=>!empty($val['subtitle']) ? $val['subtitle'] : '',
							'pic_url'=>!empty($val['pic_url']) ? $val['pic_url'] : '',
							'focus'=>!empty($val['focus']) ? $val['focus'] : 0,
							'sort'=>!empty($val['sort']) ? $val['sort'] : 0,
							'upuid' => $this->para['upuid'],
							'uptime' => $today
						);
						$res = $obj->addRadioNews($args);
					}
					}
				}
				 $obj->getNewsForIndex(true);
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
new setNews(RADIO_APP_SOURCE);
?>