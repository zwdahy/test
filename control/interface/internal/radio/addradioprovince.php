<?php
/**
 * Project:     电台管理后台接口
 * File:        addradioprovince.php
 * 
 * 添加地区首页
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/addradioprovince.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */

include_once SERVER_ROOT . 'config/radioconf.php';
class addRadioProvince extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['upuid'] = request::post('upuid', 'STR');						//更新人UID
		$this->para['province_id'] = request::post('province_id', 'INT');			//省级id
		$this->para['dj_publink'] =request::post('dj_publink', 'STR');				//名人堂链接
		$this->para['dj_radio_order'] = request::post('dj_radio_order', 'STR');		//dj设置单选按钮值
		$this->para['radio_right'] = request::post('radio_right', 'STR');			//右侧推荐图单选按钮值
		$this->para['right_pic_url'] = request::post('right_pic_url', 'STR');		//右侧图片路径
		$this->para['right_link_url'] = request::post('right_link_url', 'STR');		//右侧链接地址

		$this->para['sort_pics'] = request::post('sort_pics', 'STR');				//推荐图序号
		$this->para['check_pics'] = request::post('check_pics', 'STR');				//推荐图checkbox
		$this->para['select_pics'] = request::post('select_pics', 'STR');			//推荐图select下拉框
		$this->para['picpaths'] = request::post('picpaths', 'STR');					//推荐图路径
		$this->para['link_urls'] = request::post('link_urls', 'STR');				//推荐图链接
		
		$this->para['dj_sorts'] = request::post('dj_sorts', 'STR');					//dj序号
		$this->para['dj_urls'] = request::post('dj_urls', 'STR');					//dj链接
		$this->para['dj_names'] = request::post('dj_names', 'STR');					//dj显示名
		$this->para['dj_descs'] = request::post('dj_descs', 'STR');					//dj描述
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
		
		
		/*
		//对其输入的sort输入并键入排序值
		usort($pic_arr, array("setRadioProvince", "arrPicCmp"));
		*/
		
		
		
		$ke = 0;
		foreach ($pic_arr as $key=>$value){
			$pic_arr[$ke]['pic_sort'] = $key+1;
			$ke++;
		}
	
		$rolling_picture = serialize($pic_arr);
		
		
		
    	//DJ信息
    	//选择自定义输入的DJ的相关信息时，有DJ的信息，不选择就默认是推荐的9个DJ信息
		if ('define' == $this->para['dj_radio_order']) {
			$dj_sorts = explode(',',$this->para['dj_sorts']);
			$dj_urls = explode(',',$this->para['dj_urls']);
			$dj_names = explode(',',$this->para['dj_names']);
			$dj_descs = explode(',',$this->para['dj_descs']);
			
			
			$dj_arr = array();
			$b = 0;
			//检验dj_url是否存在
			foreach ($dj_urls as $key=>$value){
				$tmp = explode('/',$value);
				$domain = '';
				foreach($tmp as $k => $v){
					if($v == 't.sina.com.cn' || $v == 'weibo.com'){
						if($tmp[$k+1] == 'u'){
							$uid = $tmp[$k+2];
						}
						else{
							$domain = $tmp[$k+1];
						}
						break;
					}
				}
				if(empty($uid)){		
					if(preg_match('/^uc([0-9]{5,9})$/',$domain,$match)){
						$uid = $match[1];
					}
					else{
						$rs = $obj->getUserInfoByDomain($domain);
						if($rs['id'] > 0){
							$uid = $rs['id'];
						}
						else{
							$this->display(array('errmsg' => "用户的uid不存在"),'json');
							return true;
						}
					}
				}				
				$dj_arr[$b]['dj_url'] = $value;
				$dj_arr[$b]['dj_uid'] = $uid;
				$b++;
			}
			
			
			$a = 0;
			foreach ($dj_sorts as $value){
				$dj_arr[$a]['dj_sort'] = $value;
				$a++;
			}
	
			$c = 0;
			foreach ($dj_names as $value){
				$dj_arr[$c]['dj_name'] = $value;
				$c++;
			}
			$d = 0;
			foreach ($dj_descs as $value){
				$dj_arr[$d]['dj_desc'] = $value;
				$d++;
			}
			
			//重新排序，把后填写的序号放到重复的前面
			// 取得列的列表
			foreach ($dj_arr as $key => $val) {
			    $dj_sort[$key] = $val['dj_sort'];
			    $dj_key[$key] = $key;
			}
			array_multisort($dj_sort, SORT_ASC,$dj_key, SORT_DESC, $dj_arr);
			
			
			/*
			//对其输入的sort输入并键入排序值
			usort($dj_arr, array("setRadioProvince", "arrDjCmp"));
			*/
			
			
			$he = 0;
			foreach ($dj_arr as $key=>$value){
				$dj_arr[$he]['dj_sort'] = $key+1;
				$he++;
			}
			
			$dj_info = serialize($dj_arr);	
		}else{
			$dj_info = '';
		}
		
		
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
			'rolling_picture' => $rolling_picture,
			'publink' => $this->para['dj_publink'],
			'dj_info' => $dj_info,
			'right_picture' => $right_picture,
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
	
	
	private function arrPicCmp($a,$b){
		if($a['pic_sort'] == $b['pic_sort']){
			return 0;
		}
		return($a['pic_sort']<$b['pic_sort']) ? -1 : 1;
	}
	
	private function arrDjCmp($a,$b){
		if($a['dj_sort'] == $b['dj_sort']){
			return 0;
		}
		return($a['dj_sort']<$b['dj_sort']) ? -1 : 1;
	}
	
	
}

new addRadioProvince(RADIO_APP_SOURCE);
?>