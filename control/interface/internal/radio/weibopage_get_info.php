<?php
/**
 * Project:    手机客户端微博pageinfo信息
 * File:       weibopage_get_info.php
 * 
 * 获取电台信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/weibopage_get_info.php
 * @copyright sina.com
 * @author 张旭 <zhangxu5@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class weiboPageInfo extends control {
	const VERSION5 = 5;
	protected function checkPara() {		
		$this->para['pageid'] = $_REQUEST['page_id'];		//电台id
		$this->para['uid'] = $_REQUEST['uid'];		//用户id
		$this->para['cardid'] = $_REQUEST['cardid'];  //card类型
		$this->para['containerid'] = $_REQUEST['containerid'];
		$this->para['vp'] = $_REQUEST['v_p'];

		if ($this->para['vp'] >= self::VERSION5) {
			$arr = explode('_-_', $this->para['containerid']);
			$this->para['pageid'] = $arr[0];
			$this->para['cardid'] = $arr[1];
		}
		if(empty($this->para['pageid'])){
			$this->display(array('errno'=>-4,'errmsg'=>'page_id参数错误'), 'json');
			exit();
		}
		return true;
	}
	public function xssCallBackCheck($content){
		$strlen = strlen($content);
		$return = '';
		$is_html_start = false;
		for($i = 0; $i < $strlen; $i++) {
			if($content{$i} == '<') {
				$is_html_start = true;
			}
			if($is_html_start && $content{$i} == '>') {
				$is_html_start = false;
				continue;
			}
			if(!$is_html_start) {
				$return .= $content{$i};
			}
		}
		return $return;
	}
	protected function action() {
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$vp = $this->para['vp'];
		$pageid = $this->para['pageid'];
		$ids = explode('_',$pageid);
		$cur_rid = $ids[1];
		$cur_uid = $this->para['uid'];
		//电台相关信息
		$radioInfo = $mRadio->getRadioInfoByRid(array($cur_rid));
		$radioInfo = $radioInfo['result'][$cur_rid]; 

		if ($vp >= self::VERSION5) {
			$mobile = array(
				'containerid'=>$pageid,
				'url'=>array(
					'status'=>1,
					'scheme'=>'sinaweibo://pageinfo?containerid='.$pageid,
					'name'=>$radioInfo['name'],
				),
				'card'=>array(
					'status'=>1,
					'type'=>0,
					'is_asyn'=>0,
					'scheme'=>'sinaweibo://pageinfo?containerid='.$pageid,
					'contents'=>array(
						$radioInfo['name'],
						$this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro']))
					),
					'pic'=>$radioInfo['img_path'],
				),
			);
			$urlScheme = 'sinaweibo://pageinfo?containerid='.$pageid;
			$cardScheme = 'sinaweibo://pageinfo?containerid='.$pageid;
		} else {
			$mobile = array(
				'page_id'=>$pageid,
				'url'=>array(
					'status'=>1,
					'scheme'=>'sinaweibo://pageinfo?pageid='.$pageid,
					'name'=>$radioInfo['name'],
				),
				'card'=>array(
					'status'=>1,
					'type'=>0,
					'is_asyn'=>0,
					'scheme'=>'sinaweibo://pageinfo?pageid='.$pageid,
					'contents'=>array(
						$radioInfo['name'],
						$this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro']))
					),
					'pic'=>$radioInfo['img_path'],
				),
			);
		}

		//拼凑数组
		$pageInfo = array(
			'id'=>'1022:'.$pageid,
					'object_type'=>'broadcast',
					'display_name'=>$radioInfo['name'],
					'image'=>array('url'=>$radioInfo['img_path'],'width'=>'180','height'=>'90'),
					'summary'=>$this->xssCallBackCheck(htmlspecialchars_decode($radioInfo['intro'])),
					'url'=>'http://weibo.cn/p/'.$pageid,
					'mobile'=>$mobile
		);

		$data = array();
		if(!empty($pageInfo)) {
			$data= $pageInfo;
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
new weiboPageInfo(RADIO_APP_SOURCE);
?>
