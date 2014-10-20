<?php
/**
 * Project:     电台管理后台接口
 * File:        gettranscoderadioinfo.php
 * 
 * 获取转码后的电台信息
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/gettranscoderadioinfo.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once SERVER_ROOT . 'config/radioconf.php';
class getTranscodeRadioInfo extends control {
	protected function checkPara() {
		$this->para['rid'] = request::post('rId', 'INT');				// 电台ID
		$this->para['continue'] = request::post('continue', 'INT');		// 值为1时表示续期，创建时不需要传此参数
		$this->para['rName'] = request::post('rName', 'STR');
		$this->para['rFm'] = request::post('rFm', 'STR');
		$this->para['mms'] = request::post('mms', 'STR');
		return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $obj->transcodeRadio2($this->para);			
		$data = array();
		if($result['code'] == 'A0001') {
			$data = array(
				'errno' => 1,
				'errmsg' => '成功',
				'result' => $result['data']
			);
		} else {
			$data = array(
				'errno' => $result['code'],
				'errmsg' => '音频流转码接口异常'
			);
		}
		$this->display($data, 'json');
		return true;
	}
}
new getTranscodeRadioInfo(RADIO_APP_SOURCE);
?>
