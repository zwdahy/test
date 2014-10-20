<?php

/**
 * Project:     radio
 * File:        search.php
 * 
 * 获取seek电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once SERVER_ROOT."config/config.php";
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."config/area.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";

class RadioSearch extends control {
	protected function checkPara() {
		//判断来源合法性
//		if(!Check::checkReferer()){
//			$this->setCError('M00004','Refer来源错误');
//			return false;
//		}
		//获取参数
		$this->para['words'] = trim(request::get('words', 'STR'));//搜索关键字
//		var_dump($this->para['words']."<br>");
		$trags = array('.','*');//过滤字符
		$this->para['words'] = str_replace($trags,"",$this->para['words']);
//		var_dump($this->para['words']);exit;
		//$this->para['words'] = request::get('words', 'STR');//搜索关键字
		$this->para['search_type'] = request::get('type', 'INT');//搜索类型 //分页用 实际不分类搜索
		//$this->para['words'] = '音乐';
		$this->para['page'] = request::get('page', 'INT');//显示第几页
		//$this->para['page'] = 1;
		$this->para['search_type'] = empty($this->para['search_type'])?1:$this->para['search_type'];
		$this->para['page'] = empty($this->para['page'])?1:$this->para['page'];
		//参数检测处理
		$types = array(1,2,3);
		if(!in_array($this->para['search_type'],$types)) {
			$this->setCError('M00009', '参数错误');
			return false;
		}
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$cur_rid = $this->para['rid'];	//当前电台id
		$data=$mRadio->formatScope($cur_rid);
		$data['page_title'] = sprintf(RADIO_TITLE, "微电台搜索");
		$data['backurl'] = RADIO_URL.'/search';
		$data['search_type'] = $this->para['search_type'];//搜索分类
		$data['page'] = $this->para['page'];//当前页码
		$data['words'] = htmlspecialchars($this->para['words']);
		$this->para['words'] = mb_ereg_replace('%','',$this->para['words']);
		if(empty($this->para['words'])){
			$data['code'] = 'A00011';//没有输入搜索内容
			$data['radioInfo_number'] = 0;
			$data['programInfo_number'] = 0;
			$data['djInfo_number'] = 0;
		}else{
			$data['code'] = 'A00006';//成功
			$this->para['words'] = strip_tags($this->para['words']);
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			//根据电台名称搜索电台
			$radioInfo = $mRadio->searchRadioInfoByRadioName($this->para['words'],$this->para['page']);
			$radioInfo = $radioInfo['result'];
			if(!empty($radioInfo)){
				$preg="{$this->para['words']}";
				foreach($radioInfo as &$v){
					$v['name_new']=mb_ereg_replace($preg,'<em>'.$this->para['words'].'</em>',$v['name'],'x');
				}
				unset($v);
			}else{
				$radioInfo=array();
			}
			$data['radioInfo'] = $radioInfo;
			$data['radioInfo_number'] = count($radioInfo);
			$data['radioInfo_pages'] = ceil($data['radioInfo_number']/10);

			//根据节目名称搜索节目
			$programInfo = $mRadio->searchRadioInfoByProgramName($this->para['words'],$this->para['page']);
			$programInfo = $programInfo['result'];
			if(!empty($programInfo)){
				$preg="{$this->para['words']}";
				foreach($programInfo as &$v){
					$v['program_name_new']=mb_ereg_replace($preg,'<em>'.$this->para['words'].'</em>',$v['program_name'],'x');
				}
				unset($v);
			}else{
				$programInfo = array();
			}
			$data['programInfo'] = $programInfo;
			$data['programInfo_number'] = count($programInfo);
			$data['programInfo_pages'] = ceil($data['programInfo_number']/10);
//			print '<pre>';
//			print_r($programInfo);
//			exit;
			
	//		根据dj名称搜索dj
			//$djInfo = $mRadio->searchRadioInfoByDjName($this->para['words'],$this->para['page']);
			$djInfo = $mRadio->searchProgramInfoByDjName($this->para['words'],$this->para['page']);
			$djInfo = $djInfo['result'];
//			print '<pre>';
//			print_r($djInfo);
//			exit;
			if(!empty($djInfo)){
				foreach($djInfo as $k=>$v){
					foreach($v['dj_info'] as $v2){
						if(mb_strpos($v2['screen_name'],$this->para['words'])!==false){
							$preg="{$this->para['words']}";
							break;
						}
					}
					$djInfo[$k]['dj_info'] = $mRadio->getSimpleNameCard($v2['uid']);
					$djInfo[$k]['screen_name_new']=mb_ereg_replace($preg,'<em>'.$this->para['words'].'</em>',$v2['screen_name'],'x');
				}
			}else{
				$djInfo = array();
			}
			if($djInfo){
				$data['search_type'] = 3;
			}
			if($programInfo){
				$data['search_type'] = 2;
			}
			if($radioInfo){
				$data['search_type'] = 1;
			}
//			print '<pre>';
//			print_r($djInfo);
//			exit;
			$data['djInfo'] = $djInfo;
			$data['djInfo_number'] = count($djInfo);
			$data['djInfo_pages'] = ceil($data['djInfo_number']/10);
//			print '<pre>';
//			print_r($data);
//			exit;
		}
		//分配搜索数据
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/search.html' ), 'data' => $data ), 'html' );

	}
	
}

new RadioSearch(RADIO_APP_SOURCE);
?>
