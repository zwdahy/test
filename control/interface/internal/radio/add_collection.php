<?php
/**
 * Project:     收藏电台
 * File:        add_collection.php
 * 
 * 
 * @link http://i.service.t.sina.com.cn/radio/radio/add_collection.php
 * @copyright sina.com
 * @author 杜启冰 <qibing@staff.sina.com.cn>
 * @package Sina
 * @version 1.0
 */
include_once(SERVER_ROOT.'config/area.php');
include_once SERVER_ROOT . 'config/radiostream.php';
class addCollection extends control {
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
    	//取出所有的在线电台rids,判断rid是否存在
        $radioList = $obj->getAllOnlineRadio();
		if(!empty($radioList['result'])){
			$radioList = $radioList['result'];
			foreach($radioList as $key => $val){
				if(isset($val['rid'])){
					$all_rids[] = $val['rid'];					
				}
			}
		}
		
        //首先查看新收藏的是否已经在收藏列表中
        $rids_array = $collection[$this->para['uid']]['rids'];
        $new_rids = array_keys($rids_array);
        $data = array();
        if(!in_array($this->para['rid'],$all_rids)){
        	// 提示rid电台是不存在的
            $data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-7,'error'=>'电台不存在，请核实参数');
    	}elseif(in_array($this->para['rid'],$new_rids)){
            // 提示已经存在于收藏列表中
            $data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-5,'error'=>'已存在于收藏列表中，请不要重复添加');
        }elseif(count($new_rids) >= RADIO_COLLECTION_MAX){
            // 提示已经超过了最大限制
            $data = array('request'=>$_SERVER['SCRIPT_URI'],'error_code'=>-6,'error'=>'已达到收藏上限，最多收藏20个电台哦！');
        }else{
            $rids_array[$this->para['rid']]['rid'] = $this->para['rid'];
            $rids = serialize($rids_array);
            $args = array('uid' => $this->para['uid']
                            ,'rids' => $rids);
            $result = $obj->addRadioCollection($args);
            if($result['errorno'] == 1 && count($result['result']) > 0){
                $data = array('status'=>1);
            }else{
				$data = array(
					'request' => $_SERVER['SCRIPT_URI'],
	                'error_code' => -9,
	                'error' => '添加收藏失败'
                 );
            }
        }

        $this->display($data, 'json');
        return true;

    }
}
new addCollection(RADIO_APP_SOURCE);
?>