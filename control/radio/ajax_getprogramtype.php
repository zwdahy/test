<?php
/**
 * Project:     radio
 * File:        ajax_getprogramtype.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class GetProgramType extends control {
	protected function checkPara() {
        //判断来源合法性
        if(!Check::checkReferer()){
            $this->setCError('M00004','Refer来源错误');
            return false;
        }
		//$this->para['all'] = intval(request::post('all', 'STR'));
        $this->para['program_id'] = intval(request::post('program_id', 'STR'));
        //@test
		//$this->para['program_id'] = 18602;
		//获取参数
		//$this->para['all'] = intval(request::post('all', 'STR'));
		//$this->para['sort'] = intval(request::post('sort', 'STR'));

		//测试数据
/*
		$this->para['rid'] = '31';		
*/		
//		//参数检测处理
//		if(empty($this->para['rid'])) {
//			$this->setCError('M00009', '参数错误');
//			return false;
//		}				
	}
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
        $program_id = $this->para['program_id'];
        if(!empty($program_id)){
            $radioProgramTypes = $mRadio->getRadioProgramType($program_id);	
        }else{
            $radioProgramTypes = $mRadio->getRadioProgramTypeList();
        }
        if(!empty($radioProgramTypes)){	
			$jsonArray['code'] = 'A00006';
			$jsonArray['data'] = $radioProgramTypes['result'];			
		}		
		else{
			$jsonArray['code'] = 'E00001';
			$jsonArray['data'] = $radioProgramTypes['result'];
		}
		$this->display($jsonArray, 'json');
	}
}

new GetProgramType(RADIO_APP_SOURCE);
?>
