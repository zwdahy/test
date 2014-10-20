<?php
/**
 * 
 * 行为日志ajax请求
 * 
 * @package 
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * 
 */
include_once SERVER_ROOT . "config/radioconf.php";
class userActionLog extends control {
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}
		
		//获取参数
		$this->para['typeid']  = request::get('typeid', 'str');  //行为编码
		$this->para['radioid'] = request::get('radioid', 'INT'); //电台id
		$this->para['radioname'] = request::get('radioname', 'str'); //电台id
		$this->para['from']    = intval(request::get('from', 'INT'));    //日志来源  
//		$this->para['mblogid'] = request::get('mblogid', 'str'); //宿主id，发微博时的微博id
		//$this->para['uids']    = request::get('uids', 'INT');    //加关注uid 
		$this->para['result']  = request::get('result', 'INT');    //判断操作成功或失败
		//$this->para['province_id'] = request::get('province_id', 'INT');    //地区id
		$this->para['program_id'] = request::get('program_id', 'INT');    //节目id
		$this->para['program_name'] = request::get('program_name', 'str');    //节目名称
		$this->para['playtype'] = request::get('playtype', 'INT');    //0：直播页面  1：回听页面
		$this->para['source'] = RADIO_SOURCE_APP_ID;    //来源参数	

		//参数检测处理
		if($this->para['typeid'] === '' || $this->para['from'] === ''){
			$this->setCError('M00009', '参数错误');
			return false;
		}
		if($this->para['result']!=1){
			$this->setCError('M00009', '前端操作失败,请重新尝试');
			return false;
		}

		//获取其他的日志参数
		//时间
		$this->para['time'] = date("Y-m-d H:i:s", time());
		//前端机ip
		$this->para['serviceip'] = $_SERVER['SERVER_ADDR'];
		//客户端ip
		$this->para['clientip'] = check::getIp();
		//uid
		
		//其他参数检测处理
		if(empty($this->para['time']) || empty($this->para['serviceip']) || empty($this->para['clientip'])){
			$this->setCError('M00009', '自取参数获取失败');
			return false;
		}
	}
	
	protected function action() {
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno'],'data'=>$errors[0]['errormsg']), 'json');
			return false;
		}
		
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$cuid = $mPerson->getCurrentUserUid();
		$cuid = $cuid?$cuid:'';
		$args = array(
			'time'	=>	$this->para['time'], 
			'serviceip'	=>	$this->para['serviceip'],
			'typeid'	=>	$this->para['typeid'],
			'clientip'	=>	$this->para['clientip'],
			'cuid'    =>	strval($cuid),
			'source'    =>	$this->para['source'],
			'radioid'  =>	$this->para['radioid']
		);

		//获取节目信息
//		$program_info = $mRadio->getRadioProgramByProgramId($this->para['program_id']);

		//拼接拓展字段
		//回听或者直播
		$extra = "from=>".$this->para['from'];
		//电台名称
//		if(RADIO_USER_PLAY == $this->para['typeid']){
//			//播放情况需要记录的额外信息 合并到换台记录
//			//直播
//			if($this->para['playtype'] == 1){
//				$radio_info = $mRadio->getRadioInfoByRid(array($this->para['radioid']));
//				$radio_info = $radio_info['result'][$this->para['radioid']];
//				if(empty($radio_info)){
//					$this->setCError('M00009', '获取电台信息失败');
//					return false;
//				}
//				$extra .= ",radioname=>".$radio_info['name'].",areaname=>".$radio_info['province_spell'].",areacode=>".$radio_info['province_id'].",playtype=>".$this->para['playtype'];
//			}
//			//回听
//			if($this->para['playtype'] == 2){
//				$program_info = $mRadio->getRadioProgramByProgramId($this->para['program_id']);
//				$program_info = $program_info[0];
//				$extra .= ",radioname=>".$program_info['radio_name'].",pid=>".$program_info['program_id'].",pname=>".$program_info['program_name'].",areaname=>".$program_info['province_spell'].",areacode=>".$program_info['province_id'].",playtype=>".$this->para['playtype'];
//			}
//		}

		if(RADIO_USER_PLAY_PROGRAM == $this->para['typeid']){
			//播放打点需要记录的额外信息
			//直播
			if($this->para['playtype'] == 1){
				$radio_info = $mRadio->getRadioInfoByRid(array($this->para['radioid']));
				$radio_info = $radio_info['result'][$this->para['radioid']];
				if(empty($radio_info)){
					$this->setCError('M00009', '获取电台信息失败');
					return false;
				}
				$extra .= ",radioname=>".$radio_info['name'].",areaname=>".$radio_info['province_spell'].",areacode=>".$radio_info['province_id'].",playtype=>".$this->para['playtype'];
				$day = date('N');
				$now = time();
				$programinfo = array();
				$programList=$mRadio->getRadioProgram2($this->para['radioid'],date('N'));
				foreach($programList as $v){
					if(strtotime($v['begintime'])<$now&&strtotime($v['endtime'])>$now){
						$programinfo =  $v;
						break;
					}
				}
				if($programinfo){
					$extra .=',pid=>'.$programinfo['program_id'].',pname=>'.$programinfo['program_name'];
				}
			}
			//回听
			if($this->para['playtype'] == 2){
				$program_info = $mRadio->getRadioProgramByProgramId($this->para['program_id']);
				$program_info = $program_info[0];
				$extra .= ",radioname=>".$program_info['radio_name'].",pid=>".$program_info['program_id'].",pname=>".$program_info['program_name'].",areaname=>".$program_info['province_spell'].",areacode=>".$program_info['province_id'].",playtype=>".$this->para['playtype'];
			}
		}

		if(RADIO_USER_CHANGECHANNEL == $this->para['typeid'] || RADIO_USER_PLAY == $this->para['typeid']){
			//换台需要记录的额外信息
			$radio_info = $mRadio->getRadioInfoByRid(array($this->para['radioid']));
			$day = date('N');
			$now = time();
			$programinfo = array();
			$programList=$mRadio->getRadioProgram2($this->para['radioid'],$day);
			foreach($programList as $v){
				if(strtotime($v['begintime'])<$now&&strtotime($v['endtime'])>$now){
					$programinfo =  $v;
					break;
				}
			}
			$radio_info = $radio_info['result'][$this->para['radioid']];
			if(empty($radio_info)){
				$this->setCError('M00009', '获取电台信息失败');
				return false;
			}
			$extra .= ",radioname=>".$radio_info['name'].",areaname=>".$radio_info['province_spell'].",areacode=>".$radio_info['province_id'].",playtype=>".$this->para['playtype'];
			if($programinfo){
				$extra .=',pid=>'.$programinfo['program_id'].',pname=>'.$programinfo['program_name'];
			}
		}
		$args['extra'] = $extra;
		$result = $mRadio->writeUserActionLog($args);
		if($result['errorno'] == 1){
			$data['code'] = 'A00006';
			$data['data'] = $result;
		}else{
			$data['code'] = 'A00013';
		}

		$this->display($data,'json');
	}
	
}
new userActionLog(RADIO_APP_SOURCE);
