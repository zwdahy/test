<?php
/**
 * Project:     电台管理后台接口
 * File:        setdj.php
 * 
 * 编辑主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/setdj.php
 * @copyright sina.com
 * @author 刘焘 <liutao3@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class setDjInfo extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}		
		$this->para['rid'] = request::post('rid', 'INT');		// 电台ID
		$this->para['publink'] = request::post('publink', 'STR');		// 名人堂连接		
		$this->para['urls'] = request::post('urls', 'STR');		// 主持人url列表
		$this->para['unames'] = request::post('unames', 'STR');		// 主持人显示名称列表
		$this->para['uintros'] = request::post('uintros', 'STR');		// 主持人简介列表
		$this->para['type'] = request::post('type', 'STR');		// 操作类型（'set','add'）
		$this->para['upuid'] = request::post('upuid', 'STR');		// 更新人UID
		
		// $this->para['uptime'] = request::post('uptime', 'STR');		// 更新时间
		$this->para['djsort'] = request::post('djsort', 'STR');			//DJ的sort序号
		$this->para['sort_type'] = request::post('sort_type', 'STR');		//DJ排序规则
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$urls = explode(',',$this->para['urls']);
		$unames = explode(',',$this->para['unames']);
		$uintros = explode(',',$this->para['uintros']);
		$disorts = explode(',',$this->para['djsort']);
		$count = count($urls);
		$uid_array = array();
		
		$dj_arr = array();
		for($n=0;$n < $count;$n++){
			$tmp = explode('/',$urls[$n]);
			$domain = '';
			$uid = 0;
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
				}
			}
                        if($this->para['urls'])
                        {
                            $userinfo = $obj->getUserInfoByUid(array($uid));
                            if(empty($userinfo[$uid])){
                                    $this->display(array('errmsg' => "编号：".($n+1)."、主持人url不存在"),'json');
                                    return true;
                            }
                        }
			
			$tmp = array($uid,$urls[$n],$unames[$n],$uintros[$n],'dj_sort'=>$disorts[$n]);
			$dj_arr[] = $tmp;
			//$uid_array[$n] = implode('|',$tmp);	
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
		usort($dj_arr, array("setDjInfo", "arrDjCmp"));
		foreach ($dj_arr as $key=>$val){
			$dj_arr[$key]['dj_sort'] = $key+1;
		}
	*/
		
		//整理出最终的数组，然后传入数据库中去。
		foreach ($dj_arr as $key=>$val){
			$uid_array[] = implode('|',$val);
		}
		
		
		$uids = $this->para['urls']?implode(',',$uid_array):'';
		$data = array(			
			'rid' => $this->para['rid'],
			'publink' => $this->para['publink'],
			'uids' => $uids,
			'upuid' => $this->para['upuid'],
			'sort_type' => $this->para['sort_type'],
			// 'uptime' => $this->para['uptime']
		);
               //print_r($data);
               //var_dump($this->para['type']);exit;
		if($this->para['type'] == 'set'){
			$result = $obj->setDjInfo($data);
		}
		else{
			$result = $obj->addDjInfo($data);
		}
		$tmp = $data;
		$data = array();
		if($result['errorno'] == 1) {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['result'],
				'data' => $tmp,
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
	
	//排序Dj-输入的序号
	private function arrDjCmp($a,$b){
		if($a['dj_sort'] == $b['dj_sort']){
			return 0;
		}
		return($a['dj_sort']<$b['dj_sort']) ? -1 : 1;
	}	
	
	
}
new setDjInfo(RADIO_APP_SOURCE);
?>