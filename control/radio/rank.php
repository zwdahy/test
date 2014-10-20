<?php
/**
 * 电台排行榜(control层)
 *
 * @author 高超<gaochao@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 */
header("Cache-Control: no-cache");
header("X-FRAME-OPTIONS:DENY");
include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "config/radioareaspell.php";
include_once SERVER_ROOT . "config/area.php";
include_once SERVER_ROOT."control/radio/insertFunc.php";


//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}

class RadioRank extends control{
	protected function checkPara() {
		//判断来源合法性
//		if(!Check::checkReferer()){
//			$this->setCError('M00004','Refer来源错误');
//			return false;
//		}		
		//获取参数
//		/*
//			1.电台收听榜//1和2 有单独接口
//			2.节目收听榜//
//			3.收藏排行榜
//			4.dj活跃榜
//			5.用户活跃榜
//			6.电台影响力排行榜
//			7.主播活跃榜
//			8.听友活跃榜
//		*/
//	if(empty($this->para['type'])) {
//		$this->setCError('M00009', '参数错误');
//			return false;
//		}
	}

	protected function action(){
		if($this->hasCError()) {
			$errors = $this->getCErrors();
			$this->display(array('code'=>$errors[0]['errorno']), 'json');
			return false;
		}
		$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
		$cuid=$mPerson->getCurrentUserUid();
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$data=$mRadio->formatScope();
//		print_r($data);
//		exit;
		$data['page_title'] = sprintf(RADIO_TITLE, "微电台排行榜");
		$data['backurl'] = RADIO_URL.'/rank';
		//获取dj活跃榜
		$dj_active_rank = $mRadio->getActiveDjRank(10,$cuid);
		if(!empty($dj_active_rank)){
			$data['dj_active_rank'] = $dj_active_rank;
		}else{
			$data['dj_active_rank'] = array();
		}
//		echo '<pre>';
//		print_r($dj_active_rank);
//		exit;
		//获取用户活跃榜
		$user_active_rank = $mRadio->getActiveUserRank(10,$cuid);
		if(!empty($user_active_rank)){
			$data['user_active_rank'] = $user_active_rank;
		}else{
			$data['user_active_rank'] = array();
		}
//		echo '<pre>';
//		print_r($user_active_rank);
//		exit;
		//获取电台收藏排行榜
		$collection_rank = $mRadio->getCollectionRank(10);
		if(!empty($collection_rank)){
			$data['collection_rank'] = $collection_rank;
		//微电台排行榜增加new标签
			foreach($data['collection_rank'] as &$val){
				$val['info']['isnew'] = $mRadio->checkRadioIsNew($val['info']['first_online_time']);
			}
			unset($val);
		}else{
			$data['collection_rank'] = array();
		}
//		print_r($collection_rank);
//		exit;
		//获取电台影响力排行榜
		$influence_rank = $mRadio->getInfluenceRank(10,$cuid);
		if(!empty($influence_rank)){
			$data['influence_rank'] = $influence_rank;
		}else{
			$data['influence_rank'] = array();
		}
//		print '<pre>';
//		print_r($influence_rank);
//		exit;

		//电台收听榜
		//$listen_rank=$mRadio->getListenRank();
		//节目收听榜
		//$data['areaList'] = $areaList;
		//$data['cur_pid'] = $cur_pid;
		//$data['dj_active_rank'] = $dj_active_rank;
		//$data['user_active_rank'] = $user_active_rank;
		//$data['collection_rank'] = $collection_rank;
		//$data['influence_rank'] = $influence_rank;
//		print '<pre>';
//		print_r($data);
//		exit;
		include_once PATH_ROOT.'framework/tools/display/DisplaySmarty.php';
		DisplaySmarty::getSmartyObj();
		DisplaySmarty::$smarty->left_delimiter = '{=';
		DisplaySmarty::$smarty->right_delimiter = '=}';
		$this->display ( array ('tpl' => array ('radio/rank.html' ), 'data' => $data ), 'html' );
	}
}
new RadioRank(RADIO_APP_SOURCE);
?>