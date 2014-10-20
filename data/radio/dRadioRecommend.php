<?php
/**
 * 
 * 电台推荐图信息的data层
 * 
 * @package 
 * @author 张旭6928<zhangxu5@staff.sina.com.cn>
 * @copyright(c) 2010, 新浪网 MiniBlog All rights reserved.
 * 
 * 返回的结果数组结构如下
 * 
 * 返回正确的结果数组
 * array(
 * 'errorno'   => 1,
 * 'result'  => array()
 * )	
 * 
 * 返回错误的结果数组
 * array(
 * 'errorno'   => 错误代码,
 * 'result' => 
 * )
 * 
 */
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioRecommend extends dRadio{
	public $table_recommend = "radio_recommend_pic";
	public $table_field = "`id`,`rid`,`province_id`,`s_time`,`e_time`,`url`,`sort`,`week_day`,`upuid`,`uptime`,`type`";
	
	
	/**
	 * 获取电台推荐图信息
	 */
	public function getRecommendFromDB($type){
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->table_recommend, $this->table_field, $type, array());
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$result = $st->fetchALL(PDO::FETCH_ASSOC);
		if($result === false){
			$this->writeRadioErrLog(array('获取数据失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '方法：fetchALL'), 'RADIO_ERR');
			return false;
		}
		return $result;
	}
	
	
	/**
	 * 插入电台推荐图信息
	 * @param array $args
	 */ 
	public function addRadioRecommend($args){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		
		$sqlArgs = $this->_makeInsert($this->table_recommend, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}		
		// 更新MC
		$this->getAllRecommend($fromdb = true);
		return $this->returnFormat(1);
	}
	
	/**
	 * 获取电台推荐图信息
	 */ 
	public function getAllRecommend($fromdb = false){
		$mc_key = MC_KEY_RADIO_RECOMMEND_PIC;
		$result = $this->getCacheData($mc_key);
		if($result == false || $fromdb == true){
			//获取固定推荐信息
			$type = array('type'=>'1');
			$pic_info1 = $this->getRecommendFromDB($type);
			//获取临时推荐信息
			$type = array('type'=>'2');
			$pic_info2 = $this->getRecommendFromDB($type);
			//获取服务器时间
			date_default_timezone_set('PRC');
			$now =time();
			$today = date("Y-m-d",$now); 
			$week = date('N',$now);
			$time =	date("H:i",$now); 
			//获取当前时段推荐内容
			$tmp_sort = 0;
			$tmp_recommended = array();
			if(!empty($pic_info2)){
				foreach($pic_info2 as $v){
					$s_date = explode(' ',$v['s_time']);
					$e_date = explode(' ',$v['e_time']);
					if(strtotime($today)==strtotime($s_date[0])){
						if(strtotime($time)>=strtotime($s_date[1])&&strtotime($time)<=strtotime($e_date[1])){
							$tmp_recommended[] = $v; 
							$tmp_sort++;
						}
					}
				}
			}
			
			if(!empty($pic_info1)){
				foreach($pic_info1 as $v){
					$pic_week = explode(',',$v['week_day']);
					$s_date = explode(' ',$v['s_time']);
					$e_date = explode(' ',$v['e_time']);
					if(in_array($week,$pic_week)){
						if(strtotime($time)>=strtotime($s_date[1])&&strtotime($time)<=strtotime($e_date[1])){
							$v['sort'] = $v['sort']+$tmp_sort;
							$tmp_recommended[] = $v; 
						}
					}
				}
			}
			$now_recommended = array();
			if(!empty($tmp_recommended)){
				foreach($tmp_recommended as $k=>$v){
						$rid = array('rid'=>$v['rid']);
						$args = array(
								'order_field' => "",
								'order' => "",
								'search_key' => "rid",
								'search_value' => $rid,			
								'page' => "",
								'pagesize' => ""
							);
						if(preg_match('/,/',$this->para['rid'])){
							$args['search_type'] = "IN";
						}
						else{
							$args['search_type'] = "=";
						}
						$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');		
						$rinfo = $obj->getRadio($args);	
						
						$tmp = array(
								'name' => $rinfo['result']['content'][0]['info'],
								'rid' => $v['rid'],
								'province_id' =>$v['province_id'],
								's_time' => $v['s_time'],
								'e_time' => $v['e_time'],
								'p_url' => $v['url'],
								'r_url' => 'radio.weibo.com'.'/'.$rinfo['result']['content'][0]['province_spell'].'/'.$rinfo['result']['content'][0]['domain'],
								'sort' => $v['sort']
							);
							$now_recommended[] = $tmp;
					}
				}
			if(!empty($now_recommended)){
			foreach($now_recommended as $k=>$v){
					$sort_tmp[$k] = $v['sort']; 
				}
				array_multisort($sort_tmp,SORT_ASC,$now_recommended);
				foreach($now_recommended as $k=>&$val){
					 $val['sort'] = $k+1;
				}
			}
			$result = $now_recommended;
			$this->setCacheData($mc_key, $result, MC_TIME_RADIO_RECOMMEND_PIC);
		}	
			return $this->returnFormat(1,$result);
	}
	
	/**
	 * 获取电台推荐图信息(新方法)
	 */
	public function getRecommendPic($type,$fromdb = false){
		$mc_key = sprintf(MC_KEY_RADIO_RECOMMEND_PIC_TYPE,$type);
		$result = $this->getCacheData($mc_key);
		//$fromdb =true;
		if($res == false || $fromdb == true){
			$res = $this->getRecommendFromDB2($type);
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			//$radio_info= $mRadio->getRadioInfoByRid(array($res[1]['rid']));
			//0-23为一维1-7为二维构造二维数组
			$result = array();
			$hour = date('H',time());
			if($type ==2){
				$str = '(临)';
			}else{
				$str = '';
			}
			for($i = 0;$i<24;$i++){
				foreach ($res as $key => $value) {
					//补充电台信息
					$radio_info = $mRadio->getRadioInfoByRid(array($value['rid']));
					$radio_info = $radio_info['result'][$value['rid']];
					$value['info'] = $radio_info['info'];
					$value['radio_name'] = $str.$radio_info['name'];
					$value['radio_url'] = $radio_info['radio_url'];
					$value['province_id'] = $radio_info['province_id'];
					if(date('H',strtotime($value['s_time']))<=$i&&date('H',strtotime($value['e_time']))>=$i){
						$day = explode(',',$value['week_day']);
						if(!empty($day)){
							foreach($day as $v){
								$result[$i][$v][] = $value;
							}
						}
					}
				 } 
			}
			//补充信息
			$this->setCacheData($mc_key, $res, 3600);
		}
		return $this->returnFormat(1,$result);
	}
	
	/**
	 * 获取电台当前时段的推荐图信息)
	 */
	public function getRecommendPicNow($fromdb = false){
		$mc_key = MC_KEY_RADIO_RECOMMEND_PIC_NOW;
		$result = $this->getCacheData($mc_key);
		if($result == false || $fromdb == true){
			$result = $this->getAllRecommendPic2($fromdb);
			//print_r($result);exit;
			$day = date('N');
			$hour = ltrim(date('H'),'0');
			$result = $result['result'][$hour][$day];
			//error_log(strip_tags(print_r($hour, true))."\n", 3, "/tmp/err.log");
			$this->setCacheData($mc_key, $result, 3600);
		}
		return $this->returnFormat(1,$result);
	}

	/**
	 * 获取电台全部推荐图信息(新方法)
	 */
	public function getAllRecommendPic2($fromdb = false){
		$mc_key = MC_KEY_RADIO_RECOMMEND_PIC_ALL;
		$result = $this->getCacheData($mc_key);
		//$fromdb = true;
		//error_log(strip_tags(print_r($result, true))."\n", 3, "/tmp/err.log");
		if($result == false || $fromdb == true){
			$res_temp = $this->getRecommendFromDB2(2);
			$res_regular = $this->getRecommendFromDB2(1);
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			//0-23为一维1-7为二维构造三维数组
			$result = array();
			$hour = date('H',time());
			$str = '(临)';
			for($i = 0;$i<24;$i++){
				//零时推荐图
				foreach ($res_temp as $value) {
					//补充电台信息
					$radio_info = $mRadio->getRadioInfoByRid(array($value['rid']));
					$radio_info = $radio_info['result'][$value['rid']];
					$value['id'] = $value['id'];
					$value['info'] = $str.$radio_info['info'];
					$value['radio_name'] = $radio_info['name'];
					$value['radio_url'] = $radio_info['radio_url'];
					$value['province_id'] = $radio_info['province_id'];
					if(date('H',strtotime($value['s_time']))<=$i&&date('H',strtotime($value['e_time']))>=$i){
						$day = explode(',',$value['week_day']);
						if(!empty($day)){
							foreach($day as $v){
								$result[$i][$v][] = $value;
							}
						}
					}
				 } 
				 //固定推荐图
				 foreach ($res_regular as $value) {
				 	$radio_info = $mRadio->getRadioInfoByRid(array($value['rid']));
					$radio_info = $radio_info['result'][$value['rid']];
					$value['id'] = $value['id'];
					$value['info'] = $radio_info['info'];
					$value['radio_name'] = $radio_info['name'];
					$value['radio_url'] = $radio_info['radio_url'];
					$value['province_id'] = $radio_info['province_id'];
					if(date('H',strtotime($value['s_time']))<=$i&&date('H',strtotime($value['e_time']))>=$i){
						$day = explode(',',$value['week_day']);
						if(!empty($day)){
							foreach($day as $v){
								$result[$i][$v][] = $value;
							}
						}
					}
				 }
			}
			$this->setCacheData($mc_key, $result, 3600);
		}
		return $this->returnFormat(1,$result);
	}

	/**
	 * 删除电台的推荐图信息
	 */
	public function delRadioRecommend($type){		
		$sql .= "DELETE FROM ".$this->table_recommend." WHERE type=".$type;
		$db_res = $this->_dbWriteBySql($sql);
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		return $this->returnFormat(1);
	}

	/**
	 * 获取电台推荐图信息
	 */
	public function getRecommendFromDB2($type){
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->table_recommend, $this->table_field, array('type'=>$type), array());
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$result = $st->fetchALL(PDO::FETCH_ASSOC);
		if($result === false){
			$this->writeRadioErrLog(array('获取数据失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '方法：fetchALL'), 'RADIO_ERR');
			return false;
		}
		return $result;
	}





	
}
