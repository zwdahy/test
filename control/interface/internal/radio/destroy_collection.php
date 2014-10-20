<?php
/**
 * Project:     取消收藏电台
 * File:        destroy_collection.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/destroy_collection.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT . 'config/radiostream.php';
class destroyCollection extends control {
	protected function checkPara() {
		$this->para['uid'] = request::get('uid', 'STR');                    //登录用户的ID
        $this->para['rid'] = request::get('rid', 'STR');                    //电台的rid
        if(empty($this->para['uid']) || empty($this->para['rid']) || !is_numeric($this->para['rid']) || !is_numeric($this->para['uid'])){
            $this->display(array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-4,'error'=>'参数错误'), 'json');
            exit();
        }
        return true;
	}
	protected function action() {
		$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
        $collection = $obj->getRadioCollection(array($this->para['uid']));
        
        //判断rid是否已经从收藏列表中删除了
        $rids_array = $collection[$this->para['uid']]['rids'];
		foreach($rids_array as $key=>$value){
			if($this->para['rid'] == $value['rid']){
				$rid_flag =  $value['rid'];
			}
		}
		if(isset($rid_flag)){
			//把rid从列表中去除，然后在update收藏列表
			unset($rids_array[$rid_flag]);
			$rids = serialize($rids_array);
			$args = array('uid' => $this->para['uid'],'rids' => $rids);
			$result = $obj->updateRadioCollection($args);		
			if($result['errorno'] == 1){        	
				$data = array('status'=>1);
			}else{
				$data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-9,'error'=>'取消收藏失败');
			}
		}else{
			//返回rid已经取消了
			$data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-5,'error'=>'该电台已取消收藏，请不要重复操作');
		}

        $this->display($data, 'json');
        return true;
	}
}
new destroyCollection(RADIO_APP_SOURCE);
?>
