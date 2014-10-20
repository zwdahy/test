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

//针对ticket&retcode的特殊处理，解决IE6下URL带有ticket时无法登录
if(request::get('ticket', 'STR')!= ''){
	header("Location:" . RADIO_URL);
}

class RadioAjaxRank extends control{
	protected function checkPara() {
		//判断来源合法性
		if(!Check::checkReferer()){
			$this->setCError('M00004','Refer来源错误');
			return false;
		}		//获取参数
		$this->para['type'] = intval($_POST['type']);//要获取的排行榜类型 1:radio/2:program  电台/节目
		$this->para['province_id'] = intval($_POST['province_id']);//所在省份

		if(empty($this->para['type'])) {
			$this->setCError('M00009', '参数错误');
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
		$data = array();
		//电台收听榜
		if($this->para['province_id']!==0){
			if($this->para['type']==1){
				$radio = $mRadio->getListenRankByPid($this->para['province_id']);
				$res = $mRadio->getRadioInfoByPid(array($this->para['province_id']));
				if($res['errorno']==1){
					$res = $res['result'][$this->para['province_id']];
					$total = count($res);
					if($total<10){
						$total = count($radio);
					}
				}else{
					$total = 0;
				}
				$data['total']=$total;
				$data['radio']=$radio;
			}
			//节目收听榜
			if($this->para['type']==2){
				$program = $mRadio->getHotProgramRankByPid2($this->para['province_id']);
				$total = $mRadio->getProgramNumberByProvince($this->para['province_id']);
				$data['total'] = $total?$total:0;
				$data['program'] = $program;
			}
		}else{
			//全部电台和节目的榜单
			if($this->para['type']==1){
				$radio = $mRadio->getListenRank(500);
				if($radio){
					$res = $res['result'][$this->para['province_id']];
					$total = count($radio);
					if($total<10){
						$total = count($radio);
					}
				}else{
					$total = 0;
				}
				$radio = array_slice($radio,0,10);
				$data['total']=$total;
				$data['radio']=$radio;
			}
			//节目收听榜
			if($this->para['type']==2){
				$program = $mRadio->getHotProgramTop10();
				$total = $program['total']*5;
				$program = $program['result'];
				$data['total'] = $total?$total:0;
				$data['program'] = $program;
			}
		}
		$jsonArray['code'] = 'A00006';
		$jsonArray['data'] = $data;
		$this->display($jsonArray, 'json');
	}
}
new RadioAjaxRank(RADIO_APP_SOURCE);
?>
