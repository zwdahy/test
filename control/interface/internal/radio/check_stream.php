<?php
/**
 * 检测各个电台经产品部转码后的m3u8流是否正常
 *
 * @author wenda<wenda@staff.sina.com.cn>
 *
 * @link http://i.service.t.sina.com.cn/sapps/radio/check_stream.php
 *
 * @copyright(c) 2014/5/16, 新浪网 MiniBlog All rights reserved.
 */

include_once SERVER_ROOT . "config/radioconf.php";
class CheckRadioStream extends control{
	protected function checkPara() {
		if(!Check::allow_visit_ip(false, ALLOW_VISIT_IP_DIR)) {
			$this->display(array('errno' => -1, 'errmsg' => 'IP受限'), 'json');
			exit;
		}
		$this->para['type']=strtolower(request::post('type','STR'));	//检测类型 all表示全部
		//$this->para['type']='all';	//检测类型 all表示全部
		//参数检测
		if(empty($this->para['type'])){
			$this->display(array('errno' => -1, 'errmsg' => '参数错误'), 'json');
			exit;
		}
	}

	protected function action(){
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		if($this->para['type']=='all'){
			//检测全部电台
			//获取当前电台的所有epgid
			$res = $mRadio->checkAllStream();
			$data = array( 
				'errno'		=>	'1',
				'errmsg'	=>	'成功',
				'data'		=>	$res
				); 
			//取出有问题的电台信息
		}else{
			//检测某一特定电台
		}
		$this->display($data, 'json');
		return true;
	}
}
new CheckRadioStream(RADIO_APP_SOURCE);
?>