<?php
/**
 * Project:     电台管理后台接口
 * File:        addRadioPage.php
 * 
 * 添加，修改，删除电台页面上某区域信息
 * 
 * @link http://i.service.t.sina.com.cn/sapps/radio/manageRadioPage.php
 * @copyright sina.com
 * @author  wenda<wenda@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class manageRadioPage extends control {
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['manage_type'] = request::post('manage_type', 'STR');	//操作类型 增加add 删除del 修改update
		$this->para['type'] = request::post('type', 'INT');					//页面id 添加到哪个页面 首页为1
		$this->para['block_name'] = request::post('block_name', 'STR');		//对应的区域名称
		$this->para['rid'] = request::post('rid', 'INT');		//电台id
		$this->para['block_pic'] = request::post('block_pic', 'STR');	//对应的区域内容
		$this->para['block_uid'] = request::post('block_uid', 'STR');	//dj uid
		$this->para['block_text'] = request::post('block_text', 'STR');	//对应的区域内容
		$this->para['upuid'] = request::post('upuid', 'STR');				//更新人UID
		$this->para['is_del'] = request::post('is_del', 'INT');				//是否删除 1表示删除
		$this->para['start_time'] = request::post('start_time', 'STR');				//有效期的开始时间
		$this->para['end_time'] = request::post('end_time', 'STR');				//有效期的结束时间
		$this->para['extra'] = request::post('extra', 'STR');				//额外信息 保留使用
		$this->para['visable'] = request::post('visable', 'INT');				//是否隐藏
		$this->para['id'] = request::post('id', 'INT');					//主键id 真删除时使用
		//error_log(strip_tags(print_r($this->para['block_text'], true))."\n", 3, "/tmp/err.log");

		$this->para['start_time'] = !empty($this->para['start_time'])?$this->para['start_time']:'';
		$this->para['end_time'] = !empty($this->para['end_time'])?$this->para['end_time']:'';
		$this->para['rid'] = !empty($this->para['rid'])?$this->para['rid']:0;
		$this->para['block_pic'] = !empty($this->para['block_pic'])?$this->para['block_pic']:'';
		$this->para['block_uid'] = !empty($this->para['block_uid'])?$this->para['block_uid']:'';
		$this->para['block_text'] = !empty($this->para['block_text'])?$this->para['block_text']:'';
		$this->para['extra'] = !empty($this->para['extra'])?$this->para['extra']:'';
		$this->para['visable'] = !empty($this->para['visable'])?$this->para['visable']:0;
		$this->para['is_del'] = !empty($this->para['is_del'])?$this->para['is_del']:0;

		foreach($this->para as &$v){
			$v = urldecode($v);
		}
		unset($v);
//		$this->para['manage_type'] = 'update';	//操作类型 增加add 删除del 修改update
//		$this->para['type'] = 1;					//页面id 添加到哪个页面 首页为1
//		$this->para['block_name'] = 'radio_rank';		//对应的区域名称
//		$this->para['rid'] = 10;	//对应的区图片
//		$this->para['block_pic'] = 'http://www.sinaimg.cn/blog/miniblog/shoujit/yidong.jpg';	//对应的区图片
////		$this->para['block_uid'] = '1701619985';	//对应的区图片
//		$this->para['block_text'] = 'this is listen radio info';	//对应的区域内容
//		$this->para['upuid'] = 'wenda1';				//更新人UID
////		$this->para['start_time'] = date('Y-m-d H:i:s',1404930231);				//有效期的开始时间
////		$this->para['end_time'] = date('Y-m-d H:i:s',1404934831);				//有效期的结束时间
////		$this->para['extra'] = 'hshahdfa';				//额外信息 保留
////		$this->para['visable'] = 1;				//额外信息 保留
////		$this->para['is_del'] = 1;				//额外信息 保留
//		$this->para['id'] = 96;					//主键id 
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if(!in_array($this->para['manage_type'],array('add','del','update'))){
			$error = array('errmsg' => 'manage_type参数错误');
			$this->display($error, 'json');
			return true;
		}
		$this->para['visable'] = !empty($this->para['visable']) ? $this->para['visable']:0;
		if($this->para['manage_type'] == 'add'){
			//参数的处理
			$args = array(
				'type' => $this->para['type'],
				'block_name' => $this->para['block_name'],
				'rid' => $this->para['rid'],
				'block_pic' => $this->para['block_pic'],
				'block_uid' => $this->para['block_uid'],
				'block_text' => $this->para['block_text'],
				'upuid' => $this->para['upuid'],
				'start_time' => $this->para['start_time'],
				'end_time' => $this->para['end_time'],
				'extra' => $this->para['extra'],
				'visable' => $this->para['visable']
			);
			//print_r($args);exit;
			$result = $obj->addRadioPage($args);
		}

		if($this->para['manage_type'] == 'del'){
			if(empty($this->para['id'])){
				$error = array('errmsg' => '参数错误');
				$this->display($error, 'json');
				return true;
			}
			$args = array(
				'id' => $this->para['id'],
				'upuid' => $this->para['upuid']
			);
//			print_r($args);
//			exit;
			$result = $obj->delRadioPage($args);
		}

		if($this->para['manage_type'] == 'update'){
			if(empty($this->para['id'])){
				$error = array('errmsg' => '参数错误');
				$this->display($error, 'json');
				return true;
			}
			$args = array(
				'type' => $this->para['type'],
				'block_name' => $this->para['block_name'],
				'rid' => $this->para['rid'],
				'block_pic' => $this->para['block_pic'],
				'block_uid' => $this->para['block_uid'],
				'block_text' => $this->para['block_text'],
				'upuid' => $this->para['upuid'],
				'start_time' => $this->para['start_time'],
				'end_time' => $this->para['end_time'],
				'extra' => $this->para['extra'],
				'visable' => $this->para['visable'],
				'id' => $this->para['id']
			);
			$result = $obj->updateRadioPage($args);
		}

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

new manageRadioPage(RADIO_APP_SOURCE);
?>