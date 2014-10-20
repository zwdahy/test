<?php
/**
 * Project:     电台管理后台接口
 * File:        getdj.php
 * 
 * 获取主持人信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/getdjinfobyuid.php
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getDjInfoByUid extends control {
	protected function checkPara() {	
		$this->para['uid'] = request::post('uid', 'INT');		// 电台ID
		if($this->para['uid'] > 0){
			return true;
		}		
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
		$result = $obj->getDjInfoByUids(array($this->para['uid']));
		$data = array();
		if($result['errorno'] == 1) {
			$result = array_pop($result['result']);
			$result = $result['djinfo'];
			$rid = $result['rid'];			
			if(!empty($rid)){
				$radioinfo = $obj->getRadioInfoByRid(array($rid));
				$radioinfo = $radioinfo['result'];
				$radio_link = RADIO_URL."/".$radioinfo[$rid]['province_spell']."/".$radioinfo[$rid]['domain'];
			}else{
				$radioinfo = $obj->getRadioByUid(array($this->para['uid']),$fromdb=false);
				$radioinfo = $radioinfo['result'];
				$radio_link = RADIO_URL."/".$radioinfo[$this->para['uid']]['province_spell']."/".$radioinfo[$this->para['uid']]['domain'];
			}
			$publink = $result['publink'];
			$struids = $result['uids'];
			$djinfo = array();
			$dj_tmp = explode(',',$struids);
			foreach($dj_tmp as $val){
				$tmp = explode('|',$val);
				$djinfo[] = array('uid'=>$tmp[0]
								,'url'=>$tmp[1]
								,'screen_name'=>$tmp[2]
								,'intro'=>$tmp[3]);
			}
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'radio_link' =>$radio_link,
				'publink'  => $publink,
				'djinfo' => $djinfo
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
new getDjInfoByUid(RADIO_APP_SOURCE);
?>