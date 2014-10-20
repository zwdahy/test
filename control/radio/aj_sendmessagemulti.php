<?php
/**
 * 批量发送私信
 *
 * @copyright	(c) 2010, 新浪网 MiniBlog All rights reserved.
 * @author 		高超  gaochao@staff.sina.com.cn
 * @version		1.0 - 2011-06-17
 * @package		control
 */
class Aj_SendMessageMulti extends control {
	
	protected function checkPara(){
	 //判断来源合法性
           if(!Check::checkReferer()){
                        $this->setCError('M00004','Refer来源错误');
                        return false;
                }

                $person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
				$this->para['cuserInfo'] = $person->currentUser();
				
                if($this->para['cuserInfo'] === false) {
                        $this->setCError('M00003','未登录');
                        return false;
                }

                $this->para['name'] = request::post('name', 'STR');
                if(strlen(trim($this->para['name'])) < 1) {
                        $this->setCError('M00002','用户昵称不能为空！');
                        return false;
                }
                
                $name = urldecode($this->para['name']);                
                $mRadio = clsFactory::create(CLASS_PATH.'model/radio','mRadio','service');
                $userinfo = $mRadio->getUserInfoByName(array($name));                
                if(empty($userinfo[$name])){
                	$this->setCError('M00003','用户不存在！');
                }
				$this->para['toUidsArr'] = array($userinfo[$name]['uid']);
				
                $this->para['content'] = rawurldecode(request::post('content', 'STR'));
                if(strlen(trim($this->para['content'])) < 1) {
                        $this->setCError('M00001','内容不能为空');
                        return false;
                }
                if(strlen($this->para['content']) > 900) {
                        $this->setCError('E00002','参数错误');
                        return false;
                }
	}
	
	protected function action(){
				if($this->hasCError()) {
                        $errors = $this->getCErrors();
                        $this->display(array('code'=>$errors[0]['errorno']), 'json');
                        return false;
                }
                
                $mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
                
                $Ret = $mRadio->sendMessageMulti($this->para['cuserInfo']['uid'], $this->para['content'], $this->para['toUidsArr']);
                if($Ret) {
                        $data = array('code'=>'A00006', 'data'=>'');
                } else {
                        $data = array('code'=>'R01404', 'data'=>'');
                }
                $this->display($data, 'json');


	}
}
new Aj_SendMessageMulti(RADIO_APP_SOURCE);
?>
