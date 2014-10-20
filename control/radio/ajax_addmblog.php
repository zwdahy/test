<?php


/**
 * Project:     radio
 * File:        ajax_addblog.php
 * 
 * 发微博
 * 
 * @link http://www.sina.com.cn
 * @copyright sina.com
 * @author 张倚弛 <yichi@staff.sina.com.cn>
 * @package radio
 * @date 2010-9-27
 * @version 1.1
 */

include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class AddBlog extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}

		//获取参数
		$this->para['content'] = request::post('content', 'str');
		$this->para['pid']     = request::post('pic', 'str');
		$this->para['rid']  = intval(request::post('rid', 'str'));
		$this->para['playtype'] = intval($_POST['playtype']);//来自哪个页面的分享 1直播页 2回放页
		//$this->para['playtype'] = 2;

		
		//登录检测 TODO
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		if($mPerson->isLogined()) {			
			$this->para['currUid'] = $mPerson->getCurrentUserUid();
		} else {
			$this->setCError('M00003','未登录');
			return false;
		}		

		//参数检测处理
		$this->para['playtype'] = !empty($this->para['playtype'])?$this->para['playtype']:1;
//		if(empty($this->para['content'])
//			|| empty($this->para['playtype'])) {
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
		//获取发微博时的电台自己的元数据
		$annotations = $mRadio->getRadioAnnotations($this->para['rid']);


		//拼凑URL供特殊feed解析 
		date_default_timezone_set('PRC');
		$now =time();
		$week = date("N",$now);
		$time = date("H:i",$now); 
		
		$programs =  $mRadio->getRadioProgram($this->para['rid'],$week);
		$programs = unserialize($programs['program_info']);

		$program_now = null;
		
		if(!empty($programs)){
			foreach($programs as $v){
				if($time>=$v['begintime']&&$time<=$v['endtime']){
					$program_now = $v['begintime'];
					$program_time = explode(':',$program_now);
					break;
				}
			}
		 }
		$tmp_annotations = json_decode(htmlspecialchars_decode($annotations));
		foreach($tmp_annotations[0] as $k=>$v){
			$radio_info[$k] = $v;
		}
		
		//直播
		if($program_time){
		$url = $radio_info['url'].'_'.$week.$program_time[0].$program_time[1];
		}else{
			$url = $radio_info['url'];
		}
//		$short = $mRadio->long2short_url($url);
//		$short_url = $short['urls'][0]['url_short'];
		//回放
		if($this->para['playtype'] == 2){
			$short_url = urlencode($_SERVER['HTTP_REFERER']);
			$short_url = $mRadio->long2short_url($short_url);
			$short_url = $short_url['urls'][0]['url_short'];
		}else{
			$short = $mRadio->long2short_url($url);
			$short_url = $short['urls'][0]['url_short'];
		}
		
		$args = array('status' => $this->para['content'].' '.$short_url
					,'annotations' => $annotations
					,'is_encoded' => 0
					);
		if(!empty($this->para['pid'])){
			$args['pic_id'] = $this->para['pid'];
		}
		$result = $mRadio->addMblog($args);
		$mid = $result['mid'];
		//error_log(strip_tags(print_r($result, true))."\n", 3, "/tmp/err.log");
		$jsonArray = array('code'=>'M00004','data'=>'');
		if(empty($result['error_code'])) {
			//获取当前发微博的数据 制造假数据
			//$contentArr = $mRadio->formatFeed(array($result));
			//$contentArr = $contentArr[0];
			//$params = array();
			//$params['data'] = array($contentArr);
			//$now = insert_radio_feedlist($params, $smarty);
		//判断是否在线dj
		$isCurrentDj = $mRadio->isCurrentDj($this->para['currUid'],$this->para['rid']);
		if($isCurrentDj !== false){
			$isCurrentDj = 1;
			//获取当天的节目单用来确定mc生命周期
			$today = date('N');
			$programList = $mRadio->getRadioProgram2($this->para['rid'],$today);
			$time = time();
			foreach($programList as &$v){
				if( strtotime( $v['begintime'] )<=$time&&strtotime( $v['endtime'] )>$time ){
					$liveTime = strtotime($v['endtime'])-strtotime($v['begintime']);
						break;//找到一个就ok啦
				}
			}
			unset($v);
			$mRadio->addDjFeed(array($mid),$this->para['rid'],$liveTime);
		}else{
			$isCurrentDj = 0;
		}
		//记录用户行为记录
		
		$args = array(
			'time'	=>	date("Y-m-d H:i:s", time()), 
			'serviceip'	=>	$_SERVER['SERVER_ADDR'],
			'typeid'	=>	RADIO_USER_ADDMBLOG,
			'clientip'	=>	check::getIp(),
			'cuid'    =>	strval($this->para['currUid']),
			'source'    =>	RADIO_SOURCE_APP_ID,
			'radioid'  =>	$this->para['rid']
		);
		$args['extra'] = "from=>0,mid=>".$mid.",isTransmit=>0,is_dj=>".$isCurrentDj;
		$mRadio->writeUserActionLog($args);
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = $result;
		} else {
			if(20021==$result['error_code']){
				$result['error_code'] = 'R10004';
			}
			if ($result['error_code'] == '20032'){
				$result['error_code'] = 'M02006';
				// '评论已经提交，请耐心等待管理员审核，谢谢！';
			}
			
			if ($result['error_code'] == '20019'){
				$result['error_code'] = 'M02021';
				// '请不要重复发类同内容！';
			}
			$jsonArray['code'] = $result['error_code'];//$result['errorcode']?$result['errorno']:'';
			$jsonArray['data'] = '发微薄失败';
		}
		$display = clsFactory::create('framework/tools/display','DisplaySmarty');
        $smarty = $display->getSmartyObj();		
		$this->display($jsonArray, 'json');
	}
}

new AddBlog(RADIO_APP_SOURCE);
?>
