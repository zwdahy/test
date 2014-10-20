<?php
/**
 * 微博电台-添加关注（ajax控制层）
 * 
 * @copyright	(c) 2010, 新浪网 MiniBlog All rights reserved.
 * @author       张倚弛6328<yichi@staff.sina.com.cn>
 * @package		control
 */
require_once SERVER_ROOT . 'config/radioconf.php';
class AddFollow extends control{
	protected function checkPara(){
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

	    //检测用户是否登录，并获取到用户信息
		$mPerson = clsFactory::create(CLASS_PATH . 'model','mPerson','service');
		//判断是否登录
		if($mPerson->isLogined() === false){
			$this->setCError('M00003','未登录');
			return false;
		}
		$this->para['uid'] = $mPerson->getCurrentUserUid();

		//获取参数
		$this->para['fromuid']   = strval($this->para['uid']);
		$this->para['targetuid'] = request::post('uid','STR');

		//参数验证
		if(empty($this->para['fromuid']) || !is_string($this->para['fromuid'])){
			$this->setCError ('E00002','参数错误');
			return false;
		}
	    if(empty($this->para['targetuid']) || !is_string($this->para['targetuid'])){
			$this->setCError ('E00002','参数错误');
			return false;
		}
	}
	
	protected function action(){
		//是否包含出错信息
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		
		//构建调用M层添加关注(addAttention)函数所需参数
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$args = array(
			'uid'=>$this->para['fromuid'],
			'fuid'=>array($this->para['targetuid']),
			'appid'=> RADIO_SOURCE_APP_ID
		);
		$result = $mPerson->addAttention($args);
		//状态返回
		if($result['flag'] === true){
			//记录用户行为记录
			$args = array(
						'time'	=>	date("Y-m-d H:i:s", time()), 
						'serviceip'	=>	$_SERVER['SERVER_ADDR'],
						'typeid'	=>	RADIO_USER_ADDFOLLOW,
						'clientip'	=>	check::getIp(),
						'cuid'    =>	strval($this->para['currUid']),
						'source'    =>	RADIO_SOURCE_APP_ID,
						'radioid'  =>	0
					);
			$args['extra'] = "from=>0,uid=>".$this->para['targetuid'];
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$mRadio->writeUserActionLog($args);
			$data['code'] = 'A00006';
		}else{
			if($GLOBALS ['SUB_ERROR_NO'] != false){
				$data['code'] = $GLOBALS ['SUB_ERROR_NO'];
			}else{
				$data['code'] = 'R01404';
			}
			$data['data'] = $result;
		}
		$this->display($data, 'json');
	}
}
new AddFollow(RADIO_APP_SOURCE);
?>
