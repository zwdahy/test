<?php
/**
 *
 * 电台信息的data层
 *
 * @package
 * @author 高超6928<gaochao@staff.sina.com.cn>
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
class dRadioProgram extends dRadio{
	public $table_field = 'rid,day,program_info,topic';
	/**
	 * 添加电台节目单
	 * @param array $args
	 * @return array
	 */
	public function addRadioProgram($args){
		$rid = $args['rid'];
		$day = $args['day'];
		if(empty($rid) || empty($day)){
			return $this->returnFormat(-4);
		}
		$where = array('rid' => $rid,'day' => $day);
		$programs = $this->getProgramFromDB($where);

		//存在则执行update操作，不存在执行insert操作
		if(!empty($programs)){
			return $this->updateRadioProgram($args);
		}
		else{
			return $this->insertRadioProgram($args);
		}
	}

	/**
	 * 根据电台id和星期几获取当天节目单
	 * @param int $rid		电台id
	 * @param string $day	星期几
	 * @param int $flag		是否从数据库提取
	 */
	public function getRadioProgram($rid,$day,$flag = false){
		$key = sprintf(MC_KEY_RADIO_PROGRAM,$rid,$day);
		$program = $this->getCacheData($key);
		if($program == false || $flag == true){
			$program = $this->getProgramFromDB(array('rid' => $rid,'day' => $day));
			$program = $program[0];

			$this->setCacheData($key,$program,MC_TIME_RADIO_PROGRAM);
		}
		return $program;
	}

	/**
	 * 根据电台id获取该电台全部节目单信息
	 * @param int $rid
	 */
	public function getProgramList($rid,$flag = false){
		$key = sprintf(MC_KEY_RADIO_PROGRAM_LIST,$rid);
		$programs = $this->getCacheData($key);
		if($programs == false || $flag == true){
			$dRadioInfo = clsFactory::create(CLASS_PATH . "data/radio","dRadioInfo","service");
			$programs = $this->getProgramFromDB(array('rid'=>$rid));

			$this->setCacheData($key,$programs,MC_TIME_RADIO_PROGRAM_LIST);
		}
		return $programs;
	}

	/**
	 * 根据查询条件获取节目单信息（数据库）
	 * @param array $whereArgs
	 */
	public function getProgramFromDB($whereArgs){
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}

		$sqlArgs = $this->_makeSelect($this->_radioProgram, $this->table_field, $whereArgs, array());
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
	 * 更新电台节目单信息
	 * @param array $args
	 */
	public function updateRadioProgram($args){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$rid = $args['rid'];
		$day = $args['day'];
		$program_info = $args;
		$whereArgs = array('rid' => $rid
							,'day' => $day);
		unset($args['rid'],$args['day']);
		$sqlArgs = $this->_makeUpdate($this->_radioProgram, $args, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		//更新一周电台节目单
		$this->getProgramList($rid,true);
		// 更新指定天的电台节目单
		return $this->returnFormat(1, $this->updateProgramMc($rid,$day));
	}

	/**
	 * 插入电台节目单信息
	 * @param array $args
	 */
	public function insertRadioProgram($args){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}

		$sqlArgs = $this->_makeInsert($this->_radioProgram, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		//更新一周电台节目单
		$this->getProgramList($args['rid'],true);
		// 更新指定天的电台节目单
		return $this->returnFormat(1, $this->updateProgramMc($args['rid'],$args['day']));
	}

	/**
	 * 更新用户收藏电台id缓存
	 * @param int $rid
	 * @param string $day
	 * @param array $data
	 */
	public function updateProgramMc($rid,$day){
		//新增更新纬度的电台节目单
		$key = sprintf(MC_KEY_RADIO_PROGRAM_LIST,$rid);
        $this->delCacheData($key);
		$this->updateSimpleRadioProgram($rid);
		return $this->getRadioProgram($rid,$day,true);
	}

	/**
	 * 解析数组返回节目单信息
	 * @param array $args
	 */
	public function getProgramInfo($program_info){
		$result = array();
		if(!empty($program_info) && is_array($program_info)){
			$dj_uids = array();
			foreach($program_info as $value){
				foreach($value['dj_info'] as $key => $val){
					if(!in_array($key,$dj_uids)){
							$dj_uids[] = $key;
					}
				}
			}
			if(!empty($dj_uids)){
				$dj_infos = $this->getUserInfoByUid($dj_uids);
				if($dj_infos === false){
					return false;
				}
				foreach($program_info as &$value){
					foreach($value['dj_info'] as $key => &$val){
						$val['screen_name'] = !empty($val['screen_name']) ? $val['screen_name'] : $dj_infos[$key]['name'];
						$val['userinfo'] = $dj_infos[$key];
					}
				}
			}
			$result = $program_info;
		}
		return $result;
	}

	/**
	 * 更新所有电台节目单缓存
	 */
	public function updateAllRadioProgram(){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');

		//获取按地区全部电台列表信息
		$radioList = $objRadioInfo->getRadioList();
		$radioList = $radioList['result'];
		if(empty($radioList)){
			return false;
		}
		$errinfo = array();
		foreach($radioList as $province_id => $p_radiolist){
			$programList = array();
			$p_dj_info = array();
			$p_info_by_name = array();
			$begintime = array();
			$endtime = array();
			foreach($p_radiolist as $p_radioinfo){
				if($p_radioinfo['online'] == '1' && $p_radioinfo['program_visible'] == '2'){
					//获取某单个电台一周的全部节目单
					$programs = $this->getProgramList($p_radioinfo['rid']);
					if(!empty($programs)){
						$order_begintime = array();
						$order_endtime = array();
						//按天遍历节目单信息
						foreach($programs as $day => $p_info){
							$program_info = unserialize($p_info['program_info']);
							if(!empty($program_info)){
								//对每天的节目单按节目遍历
								foreach ($program_info as &$p_info_v){
									if(!empty($p_info_v['program_name'])){
										//按电台筛选节目详情信息
										if(empty($p_info_by_name[$p_info_v['program_name']])){
											$p_info_by_name[$p_info_v['program_name']]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
											$p_info_by_name[$p_info_v['program_name']]['radio_name'] = $p_radioinfo['name'];
											$p_info_by_name[$p_info_v['program_name']]['province_id'] = $p_radioinfo['province_id'];
											$p_info_by_name[$p_info_v['program_name']]['name'] = $p_info_v['program_name'];
											$p_info_by_name[$p_info_v['program_name']]['dj_info'] = $p_info_v['dj_info'];
											$p_info_by_name[$p_info_v['program_name']]['pid'] = !empty($p_info_v['pid']) ? $p_info_v['pid'] : "";
											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = !empty($p_info_v['pic_path']) ? $p_info_v['pic_path'] : "";
											$p_info_by_name[$p_info_v['program_name']]['intro'] = !empty($p_info_v['intro']) ? $p_info_v['intro'] : "";
										}
										else{
											if(!empty($p_info_v['dj_info'])){
												foreach($p_info_v['dj_info'] as $p_djinfo_key => $p_djinfo_val){
													$p_info_by_name[$p_info_v['program_name']]['dj_info'][$p_djinfo_key] = $p_djinfo_val;
												}
											}
										}
										//初始化数组 节目->时间->节目起始时间->节目结束时间
										if(empty($p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']])){
											$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']] = array(0,0,0,0,0,0,0);
										}
										if(empty($p_info_by_name[$p_info_v['program_name']]['pid']) && !empty($p_info_v['pid'])){
											$p_info_by_name[$p_info_v['program_name']]['pid'] = $p_info_v['pid'];
											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = $p_info_v['pic_path'];
										}
										if(empty($p_info_by_name[$p_info_v['program_name']]['intro']) && !empty($p_info_v['intro'])){
											$p_info_by_name[$p_info_v['program_name']]['intro'] = $p_info_v['intro'];
										}

										$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']][$day] = 1;
										//按照节目开始时间排序字段
										if(empty($order_begintime[$p_info_v['program_name']])){
											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
										}
										elseif($order_begintime[$p_info_v['program_name']] > strtotime($p_info_v['begintime'])){
											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
										}
										//按照节目开始时间排序字段
										if(empty($order_endtime[$p_info_v['program_name']])){
											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
										}
										elseif($order_endtime[$p_info_v['program_name']] > strtotime($p_info_v['endtime'])){
											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
										}

										//按电台筛选电台DJ主持的节目
										if(!empty($p_info_v['dj_info'])){
											foreach($p_info_v['dj_info'] as $p_dj_info_key => $p_dj_info_v){
												if(empty($p_dj_info[$p_dj_info_key])){
													$p_dj_info[$p_dj_info_key] = $p_dj_info_v;
													$p_dj_info[$p_dj_info_key]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
												}
												if(!in_array($p_info_v['program_name'],$p_dj_info[$p_dj_info_key]['program_name'])){
													$p_dj_info[$p_dj_info_key]['program_name'][] = $p_info_v['program_name'];
												}
											}
										}

										$p_info_v['rid'] = $p_radioinfo['rid'];
										$p_info_v['name'] = $p_radioinfo['name'];
										$p_info_v['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
										$begintime[$day][] = strtotime($p_info_v['begintime']);
										$endtime[$day][] = strtotime($p_info_v['endtime']);
									}
								}
								if(empty($programList[$day])){
									$programList[$day] = $program_info;
								}
								else{
									$programList[$day] = array_merge($programList[$day],$program_info);
								}
							}
						}
						//按节目开始时间进行排序
						array_multisort($order_begintime,$order_endtime,$p_info_by_name);
						unset($order_begintime);
						unset($order_endtime);
						//种按电台筛选节目详情信息
						$week = array(1 => "一",2 => "二",3 => "三",4 => "四",5 => "五",6 => "六",7 => "日");
						$programInfoByName = array();
						$order = 1;
						foreach($p_info_by_name as $key => $value){
							if(is_array($value['date'])){
								$programInfoByName[$key] = $value;
								foreach($value['date'] as $ary_begintime => $p_date_val){
									foreach ($p_date_val as $ary_endtime => $ary_day){
										$p_date = array();
										$p_date_key = 0;
										for ($n=0;$n<7;$n++){
											$day_val = array_shift($ary_day);
											if($day_val == 1){
												$p_date[$p_date_key][] = $week[$n+1];
											}
											else{
												if($n > 0){
													$p_date_key++;
												}
											}
										}
										$date_info = array();
										foreach ($p_date as $tmp_k => $tmp_v){
											$tmp_count = count($tmp_v);
											if($tmp_count < 3){
												$date_info[] = "周".implode(',',$tmp_v);
											}
											else{
												$date_info[] = "周".$tmp_v[0]."至周".$tmp_v[$tmp_count-1];
											}
										}

										$programInfoByName[$key]['showtime'][] = implode(',',$date_info).' '.$ary_begintime.'-'.$ary_endtime;
									}
									$programInfoByName[$key]['showtime_info'] = implode(' ',$programInfoByName[$key]['showtime']);
									$programInfoByName[$key]['showtime_count'] = count($programInfoByName[$key]['showtime']);
								}
							}
							$programInfoByName[$key]['order'] = $order;
							$order++;
						}
						$p_info_by_name_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_NAME,$p_radioinfo['rid']);
						$mc_res = $this->setCacheData($p_info_by_name_mc_key,$programInfoByName,MC_TIME_RADIO_PROGRAM_RID_NAME);
						if ($mc_res === false){
							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
						}
						unset($p_info_by_name);
						//种按电台筛选电台DJ主持的节目
						$p_dj_info_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_DJ,$p_radioinfo['rid']);
						$mc_res = $this->setCacheData($p_dj_info_mc_key,$p_dj_info,MC_TIME_RADIO_PROGRAM_RID_DJ);
						if ($mc_res === false){
							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
						}
						foreach($p_dj_info as $p_dj_info_key => $p_dj_info_val){
							if(!empty($p_dj_info_val['program_name'])){
								$p_dj_info_uid_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_UID_DJ,$p_dj_info_key);
								$mc_res = $this->setCacheData($p_dj_info_uid_mc_key,$p_dj_info_val,MC_TIME_RADIO_PROGRAM_UID_DJ);
								if ($mc_res === false){
									$errinfo[] = "用户id：".$p_dj_info_key;
								}
							}
						}
						unset($p_dj_info);
					}
				}
			}
			for($n=0;$n<7;$n++){
				array_multisort($begintime[$n],SORT_ASC,$endtime[$n],SORT_ASC,$programList[$n]);
				$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_PID_DAY,$province_id,$n+1);
				$mc_res = $this->setCacheData($mc_key,$programList[$n],MC_TIME_RADIO_PROGRAM_PID_DAY);
				if ($mc_res === false){
					$errinfo[] = "地区id：".$province_id.",星期".$day+1;
				}
			}
		}
		if(!empty($errinfo)){
			$str = implode("|",$errinfo);
			return "更新失败信息：".$str;
		}
		return true;
	}

	/**
	 * 根据地区id获取当前正在直播的节目
	 * @param int pid
	 * @return array
	 */
	public function getProgramNowByPid($pid,$wday = -1){
		if($wday == -1){
			$date = getdate();
			$day = $date['wday'] == 0 ? 7 : $date['wday'];
		}
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_PID_DAY,$pid,$day);
		$programinfo = $this->getCacheData($mc_key);
		if(empty($programinfo)){
			$min_endtime = mktime(date('G')+1,0,0,date('m'),date('d'),date('Y'));
			$result['min_endtime'] = $min_endtime - time();
			return $result;
		}

		$programNow = array();
		$time = time();
		$min_endtime = 0;
		foreach($programinfo as $value){
			if($time < strtotime($value['begintime'])){
				//break; 这个为什么要这样做呢。
				continue;
			}
			if($time >= strtotime($value['begintime']) && $time <= strtotime($value['endtime'])){
				$programNow['program_info'][] = $value;
				if($min_endtime == 0){
					$min_endtime = strtotime($value['endtime']);
				}
				$min_endtime = $min_endtime <= strtotime($value['endtime']) ? $min_endtime : strtotime($value['endtime']);
			}
		}
		if($min_endtime == 0){
			$min_endtime = mktime(date('G')+1,0,0,date('m'),date('d'),date('Y'));
		}
		$programNow['min_endtime'] = $min_endtime - time();
		return $programNow;
	}

	/**
	 * 根据地区id获取当前正在直播的dj
	 * @param int pid
	 * @return array
	 */
	public function getDjNowByPid($pid,$wday = -1){
		$programNow = $this->getProgramNowByPid($pid,$wday);
		if(empty($programNow['program_info'])){
			return array();
		}
		$programNow = $programNow['program_info'];
		$dj_info = array();
		$dj_uids = array();
		foreach($programNow as $value){
			if(!empty($value['dj_info'])){
				foreach ($value['dj_info'] as $dj_info_uid => $dj_info_value){
					if(!in_array($dj_info_uid,$dj_uids)){
						$dj_uids[] = $dj_info_uid;
					}
					$dj_info[$dj_info_uid] = $dj_info_value;
					$dj_info[$dj_info_uid]['program_name'] = $value['program_name'];
					$dj_info[$dj_info_uid]['begintime'] = $value['begintime'];
					$dj_info[$dj_info_uid]['endtime'] = $value['endtime'];
					$dj_info[$dj_info_uid]['rid'] = $value['rid'];
					$dj_info[$dj_info_uid]['name'] = $value['name'];
					$dj_info[$dj_info_uid]['radio_url'] = $value['radio_url'];
				}
			}
		}
		$userInfo = $this->getUserInfoByUid($dj_uids);
		if($userInfo == false){
			return false;
		}
		foreach($userInfo as $key => $value){
			$dj_info[$key]['userinfo'] = $value;
			$followers_count[$key] = $value['followers_count'];
		}
		array_multisort($followers_count,SORT_DESC,$dj_info);
		$result = array();
		foreach($dj_info as $key => $value){
			$result['userinfo'][$value['uid']] = array('uid' => $value['uid']
												,'nick' => $value['screen_name']
												,'domain' => $value['url']
												,'portrait' => $value['userinfo']['profile_image_url']
												,'user' => $userInfo[$value['uid']]
												,'followers_count' => $value['userinfo']['followers_count']
												,'program_name' => $value['program_name']
												,'radio_url' => $value['radio_url']);
		}
		return $result;
	}

	/**
	 * 根据电台id获取电台节目为维度的节目单数据
	 * @param int rid
	 * @return array
	 */
	public function getProgramForNameByRid($rid,$type = 1){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_NAME,$rid);
		$p_info_by_name = $this->getCacheData($mc_key);
		if(empty($p_info_by_name)){
			return false;
		}
		if($type == 1){
			$uids = array();
			foreach($p_info_by_name as $key => $value){
				if(empty($value['pic_path'])){
					$p_info_by_name[$key]['pic_path'] =  RADIO_IMG_PATH.'img_5050_defult.jpg';
				}
				if(!empty($value['dj_info'])){
					foreach($value['dj_info'] as $k => &$val){
						if(!in_array($k,$uids)){
							$uids[] = $k;
						}
					}
				}
			}
			$userinfo = $this->getUserInfoByUid($uids);
			if(!empty($userinfo)){
				foreach($p_info_by_name as $key => $value){
					if(!empty($value['dj_info'])){
						foreach($value['dj_info'] as $k => &$val){
							$p_info_by_name[$key]['dj_info'][$k]['userinfo'] = $userinfo[$k];
						}
					}
				}
			}
		}
		return $p_info_by_name;
	}

	/**
	 * 根据电台id获取电台dj主持的节目信息
	 * @param int rid
	 * @return array
	 */
	public function getDjProgramByRid($rid){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_DJ,$rid);
		$p_info_by_name = $this->getCacheData($mc_key);
		if(empty($p_info_by_name)){
			return false;
		}
		return $p_info_by_name;
	}

	/**
	 * 更新热门节目（定时任务）
	 */
	public function updateHotProgramByDay(){
		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		$hot_endtime = $hot_begintime;
		$hotprogram = array();
		$result = $this->getHotProgram($hot_begintime,$hot_endtime,1,50,1);
		//@test
		//var_dump($result);
		/*
		
                    [tip_users] => 70
                    [day_key] => 2014-04-28
                    [end_date] => 2014-04-28
                    [tip_nums] => 5765
                    [vradio_id] => 848
                    [pname] => 天空传情
                    [orders] => 50
                    [type] => 1
                    [p_id] => 2
                    [start_date] => 2014-04-28
		*/
		$pagecount = $result['pageCount'];
		unset($result);
		if($pagecount > 0){
//			$today = getdate();
//			$today['wday'] = $today['wday'] == 0 ? 7 : $today['wday'];
//			$wday = $today['wday']-1;
			$today = date('N');
			$day = $today==1?7:$today-1; 
			$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$n=0;
			for($page=1;$page<=$pagecount;$page++){
				$result = $mRadio->getHotProgram($hot_begintime,$hot_endtime,1,50,$page);
				$result = $result['rs'];
				foreach($result as $v){
					$tmp=$mRadio->getRadioProgramByName($v['pname'],$day);
					if(empty($tmp)){
						continue;
					}
					$program_info[$n]=$tmp[0];
					$program_info[$n]['orders']=$v['pname'];
					$n++;
				}
			}
//			unset($result);
//			print_r($program_info);
//			exit;

//			for($page=1;$page<=$pagecount;$page++){
//				$result = $this->getHotProgram($hot_begintime,$hot_endtime,1,50,$page);
//				foreach($result['rs'] as $rs_value){
//					$programinfo = $this->getProgramForNameByRid($rs_value['vradio_id'],0);
//					if(empty($rs_value['pinfo'])){
//						$rs_value['pinfo'] =array();
//						break;
//					}
//					$begintime = array_keys($rs_value['pinfo']['date']);
//					foreach($begintime as $b_val){
//						$begin_hour = intval(date('H',strtotime($b_val)));
//						$endtime = array_keys($rs_value['pinfo']['date'][$b_val]);
//						foreach($endtime as $e_val){
//							if($rs_value['pinfo']['date'][$b_val][$e_val][$wday] == 1){
//								$rs_value['pinfo']['date'] = array('begintime'=>$b_val,'endtime'=>$e_val);
//								$hotprogram[$begin_hour][] = $rs_value;
//
//								$program_hour_length = floor( (strtotime($e_val) - mktime($begin_hour,0,0,date('m'),date('d'),date('Y') ) ) / 3600 );
//								if($program_hour_length > 1){
//									for($_i = 1; $_i < $program_hour_length; $_i++){
//										$hotprogram[$begin_hour+$_i][] = $rs_value;
//									}
//								}
//								unset($program_hour_length);
//							}
//						}
//					}
//				}
//			}
		}
		if(empty($hotprogram)){
			return false;
		}
		ksort($hotprogram);
		foreach($hotprogram as $hour => $value){
			$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_HOUR,$hour);
			$mc_res = $this->setCacheData($mc_key,$value,MC_TIME_RADIO_HOT_PROGRAM_DAY_HOUR);
			if ($mc_res === false){
				$errinfo[] = $hour."点";
			}
		}

		if(!empty($errinfo)){
			$str = implode("|",$errinfo);
			return "更新失败信息：".$str;
		}

		return true;
	}

	/*
	 * 通过时间获取热门节目
	 * @param $pid
	 * @return array
	 */
	public function getHotProgramByDay($hour = -1){
		if($hour == -1){
			$hour = intval(date('H',time()));
		}
		$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_HOUR,$hour);
		$common_mc_key =md5("radio.dradioprogram.gethotprogrambyday".$hour);

		$programInfo = $this->getCacheData($mc_key);
//		print_r($programInfo);
//		exit;
//		print '<pre>';
//		print_r($programInfo);
//		exit;
		if ($programInfo){
			$this->setCacheData($common_mc_key, $programInfo,259200);//防止空白，缓存三天
		}else{
			$programInfo_old = $programInfo;
			$programInfo = $this->getCacheData($common_mc_key);
			if (!$programInfo){
				$programInfo =$programInfo_old;
			}
		}
		if(empty($programInfo)){
			$min_endtime = mktime(date('G')+1,0,0,date('m'),date('d'),date('Y'));
			$result['min_endtime'] = $min_endtime - time();
			$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_HOUR,$hour-1);
			$programInfo = $this->getCacheData($mc_key);
			$common_mc_key =md5("radio.dradioprogram.gethotprogrambyday".($hour-1));
			
			if ($programInfo){
				$this->setCacheData($common_mc_key, $programInfo,259200);//防止空白，缓存三天
			}else{
				$programInfo_old = $programInfo;
				$programInfo = $this->getCacheData($common_mc_key);
				if (!$programInfo){
					$programInfo =$programInfo_old;
				}
			}
			//return $result;
		}
		$result = array();
		$cur_time = time();
		$min_endtime = 0;
		foreach($programInfo as $key => $value){
			if(count($result['program_info']) >= 20){
				break;
			}
			if($cur_time >= strtotime($value['pinfo']['date']['begintime']) && $cur_time <= strtotime($value['pinfo']['date']['endtime'])){
				if($min_endtime == 0){
					$min_endtime = strtotime($value['pinfo']['date']['endtime']);
				}
				$min_endtime = $min_endtime <= strtotime($value['pinfo']['date']['endtime']) ? $min_endtime : strtotime($value['pinfo']['date']['endtime']);
				$result['program_info'][] = $value;
			}
		}	 	
		if($min_endtime == 0){
			$min_endtime = mktime(date('G'),date('i')+1,0,date('m'),date('d'),date('Y'));
		}
		$result['min_endtime'] = $min_endtime - time();

		return $result;
	}

	/**
	 * 更新热门节目（定时任务）全部
	 */
	public function updateHotProgram(){
		$today = getdate();
		$today['wday'] = $today['wday'] == 0 ? 7 : $today['wday'];
		$interval = 6+$today['wday'];
		$begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$interval,date('Y')));
		$endtime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$today['wday'],date('Y')));
		$hotprogram = array();
		$hotProgramByPid = array();
		$errinfo = array();
		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
		$result = $this->getHotProgram($begintime,$endtime,2,50,1);
		$pagecount = $result['pageCount'];
		if($pagecount > 0){
			$maxpage = $pagecount;			
			for($page=1;$page<=$maxpage;$page++){
				$result = $mRadio->getHotProgram($begintime,$endtime,2,50,$page);
				foreach($result['rs'] as $rs_value){
					$programinfo = $mRadio->getProgramForNameByRid($rs_value['vradio_id'],0);
					if(!empty($programinfo[$rs_value['pname']])){
						$rs_value['pinfo'] = !empty($programinfo[$rs_value['pname']]) ? $programinfo[$rs_value['pname']] : array();
						if($page < 3){
							$hotprogram[] = $rs_value;
						}
						if(count($hotProgramByPid[$rs_value['pinfo']['province_id']]) >= 100){
							continue;
						}
						$hotProgramByPid[$rs_value['pinfo']['province_id']][] = $rs_value;						
					}
				}
			}
		}
		if(empty($hotprogram)){
			return false;
		}

		$mc_key = MC_KEY_RADIO_HOT_PROGRAM;
		$mc_res = $this->setCacheData($mc_key,$hotprogram,MC_TIME_RADIO_HOT_PROGRAM);

		if(!empty($hotProgramByPid)){
			foreach($hotProgramByPid as $pid => $value){
				$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_PID,$pid);
				$this->setCacheData($mc_key,$value,MC_TIME_RADIO_HOT_PROGRAM_PID);
			}
		}

		return $mc_res;
	}

	/*
	 * 获取热门节目排行榜
	 * @return array
	 */
	public function getHotProgramRank($num = 20){
		$mc_key = MC_KEY_RADIO_HOT_PROGRAM;
		
		$programInfo = $this->getCacheData($mc_key);
		
		$common_mc_key = md5('data.dradioprogram.gethotprogramrank');
		
		if (!empty($programInfo)){
			$this->setCacheData($common_mc_key, $programInfo,259200);//三天缓存。防止为空
		}else{
			$programInfo_old = $programInfo;
			$programInfo = $this->getCacheData($common_mc_key);
			if (empty($programInfo)){
				$programInfo = $programInfo_old;
			}
		}
		if($num > 0){
			$programInfo = array_slice($programInfo,0,$num,true);
		}

		return $programInfo;
	}

	/*
	 * 获取热门节目排行榜
	 * @return array
	 */
	public function getHotProgramRankByPid($pid){
		$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_PID,$pid);
		$programInfo = $this->getCacheData($mc_key);
		if(!empty($programInfo)){
			$programInfo = array_slice($programInfo,0,10,true);
		}

		return $programInfo;
	}

	/*
	 * 根据用户id获取其主持的节目信息
	 */
	public function getProgramByUid($uid){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_UID_DJ,$uid);
		$result = $this->getCacheData($mc_key);
		return $result;
	}

	/*
	 * 获取用户名片（微电台专用）
	 * @param int $uid
	 * @return array
	 */
	public function getNameCard($uid,$pname = ''){
		$mc_key = sprintf(MC_KEY_RADIO_NAME_CARD,$uid);
		$result = $this->getCacheData($mc_key);
		if($result == false||empty($result)){
			//用户主持过的节目信息
			$user_program_info = $this->getProgramByUid($uid);
			//BaseModelCommon::debug($user_program_info,'user_program_info');
			if(!empty($user_program_info['program_name'])){
				$user_program_info = $user_program_info['program_name'];
			}else{
				$user_program_info=array();
			}
			/*
			Array
			(
				[uid] => 1735657905
				[url] => http://weibo.com/lovelychloe
				[screen_name] => 可乐姨
				[radio_url] => http://radio.weibo.com/beijing/am774
				[program_name] => Array
					(
						[0] => 听世界
					)
			)
			*/
			//获取其他信息
			//$user_program_info = $this->getProgramByUid($uid);
				//用户信息
				$user_info = $this->getUserInfoByUid(array($uid));
				if(empty($user_info)){
					return false;
				}
				foreach($user_info as $value){
					$result = array(
						'uid' => $value['uid'],
						'url' => $value['link_url'],
						'name' => $value['name'],
						'sex' => $value['gender'] == 'm' ? 'male' : 'female',
						'icon' => $value['profile_image_url'],
						'description' => $value['description'],
						'user_type' => $value['user_type'],
						'program_name' => $user_program_info,
						'relation' => ''
					);
				}
			//关注关系
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$cuid = $person->getCurrentUserUid();
			if($cuid > 0){
				//1657022395 1657022397   3970089458
				$relation = $person->getRelation2($cuid,array($uid));
				$relation = $relation['result']['result'] ;
				if(!empty($result)){
					$result['relation']='1';//表示已经关注
				}
			}
		}

		$this->setCacheData($mc_key,$result,MC_TIME_RADIO_NAME_CARD);
		return $result;
	}
	
	
	/**
	 * 单独 更新某个 电台的  纬度节目单
	 * 
	 */
	public function updateSimpleRadioProgram($rid){
		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');

		//获取按地区全部电台列表信息
		$radioList = $objRadioInfo->getRadioInfoByRid(array($rid));	
		$radioTemList = $radioList['result'];
		$tem_province_id = $radioTemList[$rid]['province_id'];
		$radioList = array("$tem_province_id"=>$radioTemList);
		if(empty($radioList)){
			return false;
		}
		$errinfo = array();

		foreach($radioList as $province_id => $p_radiolist){
			$programList = array();
			$p_dj_info = array();
			$p_info_by_name = array();
			$begintime = array();
			$endtime = array();
			foreach($p_radiolist as $p_radioinfo){
				if($p_radioinfo['online'] == '1' && $p_radioinfo['program_visible'] == '2'){
					//获取某单个电台一周的全部节目单
					$programs = $this->getProgramList($p_radioinfo['rid']);
					if(!empty($programs)){
						$order_begintime = array();
						$order_endtime = array();
						//按天遍历节目单信息
						foreach($programs as $day => $p_info){
							$program_info = unserialize($p_info['program_info']);
							if(!empty($program_info)){
								//对每天的节目单按节目遍历
								foreach ($program_info as &$p_info_v){
									if(!empty($p_info_v['program_name'])){
										//按电台筛选节目详情信息
										if(empty($p_info_by_name[$p_info_v['program_name']])){
											$p_info_by_name[$p_info_v['program_name']]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
											$p_info_by_name[$p_info_v['program_name']]['radio_name'] = $p_radioinfo['name'];
											$p_info_by_name[$p_info_v['program_name']]['province_id'] = $p_radioinfo['province_id'];
											$p_info_by_name[$p_info_v['program_name']]['name'] = $p_info_v['program_name'];
											$p_info_by_name[$p_info_v['program_name']]['dj_info'] = $p_info_v['dj_info'];
											$p_info_by_name[$p_info_v['program_name']]['pid'] = !empty($p_info_v['pid']) ? $p_info_v['pid'] : "";
											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = !empty($p_info_v['pic_path']) ? $p_info_v['pic_path'] : "";
											$p_info_by_name[$p_info_v['program_name']]['intro'] = !empty($p_info_v['intro']) ? $p_info_v['intro'] : "";
										}
										else{
											if(!empty($p_info_v['dj_info'])){
												foreach($p_info_v['dj_info'] as $p_djinfo_key => $p_djinfo_val){
													$p_info_by_name[$p_info_v['program_name']]['dj_info'][$p_djinfo_key] = $p_djinfo_val;
												}
											}
										}
										//初始化数组 节目->时间->节目起始时间->节目结束时间
										if(empty($p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']])){
											$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']] = array(0,0,0,0,0,0,0);
										}
										if(empty($p_info_by_name[$p_info_v['program_name']]['pid']) && !empty($p_info_v['pid'])){
											$p_info_by_name[$p_info_v['program_name']]['pid'] = $p_info_v['pid'];
											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = $p_info_v['pic_path'];
										}
										if(empty($p_info_by_name[$p_info_v['program_name']]['intro']) && !empty($p_info_v['intro'])){
											$p_info_by_name[$p_info_v['program_name']]['intro'] = $p_info_v['intro'];
										}

										$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']][$day] = 1;
										//按照节目开始时间排序字段
										if(empty($order_begintime[$p_info_v['program_name']])){
											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
										}
										elseif($order_begintime[$p_info_v['program_name']] > strtotime($p_info_v['begintime'])){
											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
										}
										//按照节目开始时间排序字段
										if(empty($order_endtime[$p_info_v['program_name']])){
											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
										}
										elseif($order_endtime[$p_info_v['program_name']] > strtotime($p_info_v['endtime'])){
											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
										}

										//按电台筛选电台DJ主持的节目
										if(!empty($p_info_v['dj_info'])){
											foreach($p_info_v['dj_info'] as $p_dj_info_key => $p_dj_info_v){
												if(empty($p_dj_info[$p_dj_info_key])){
													$p_dj_info[$p_dj_info_key] = $p_dj_info_v;
													$p_dj_info[$p_dj_info_key]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
												}
												if(!in_array($p_info_v['program_name'],$p_dj_info[$p_dj_info_key]['program_name'])){
													$p_dj_info[$p_dj_info_key]['program_name'][] = $p_info_v['program_name'];
												}
											}
										}

										$p_info_v['rid'] = $p_radioinfo['rid'];
										$p_info_v['name'] = $p_radioinfo['name'];
										$p_info_v['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
										$begintime[$day][] = strtotime($p_info_v['begintime']);
										$endtime[$day][] = strtotime($p_info_v['endtime']);
									}
								}
								if(empty($programList[$day])){
									$programList[$day] = $program_info;
								}
								else{
									$programList[$day] = array_merge($programList[$day],$program_info);
								}
							}
						}
						//按节目开始时间进行排序
						array_multisort($order_begintime,$order_endtime,$p_info_by_name);
						unset($order_begintime);
						unset($order_endtime);
						//种按电台筛选节目详情信息
						$week = array(1 => "一",2 => "二",3 => "三",4 => "四",5 => "五",6 => "六",7 => "日");
						$programInfoByName = array();
						$order = 1;
						foreach($p_info_by_name as $key => $value){
							if(is_array($value['date'])){
								$programInfoByName[$key] = $value;
								foreach($value['date'] as $ary_begintime => $p_date_val){
									foreach ($p_date_val as $ary_endtime => $ary_day){
										$p_date = array();
										$p_date_key = 0;
										for ($n=0;$n<7;$n++){
											$day_val = array_shift($ary_day);
											if($day_val == 1){
												$p_date[$p_date_key][] = $week[$n+1];
											}
											else{
												if($n > 0){
													$p_date_key++;
												}
											}
										}
										$date_info = array();
										foreach ($p_date as $tmp_k => $tmp_v){
											$tmp_count = count($tmp_v);
											if($tmp_count < 3){
												$date_info[] = "周".implode(',',$tmp_v);
											}
											else{
												$date_info[] = "周".$tmp_v[0]."至周".$tmp_v[$tmp_count-1];
											}
										}

										$programInfoByName[$key]['showtime'][] = implode(',',$date_info).' '.$ary_begintime.'-'.$ary_endtime;
									}
									$programInfoByName[$key]['showtime_info'] = implode(' ',$programInfoByName[$key]['showtime']);
									$programInfoByName[$key]['showtime_count'] = count($programInfoByName[$key]['showtime']);
								}
							}
							$programInfoByName[$key]['order'] = $order;
							$order++;
						}

						$p_info_by_name_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_NAME,$p_radioinfo['rid']);
						$mc_res = $this->setCacheData($p_info_by_name_mc_key,$programInfoByName,MC_TIME_RADIO_PROGRAM_RID_NAME);
						if ($mc_res === false){
							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
						}
						unset($p_info_by_name);
						//种按电台筛选电台DJ主持的节目
						$p_dj_info_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_DJ,$p_radioinfo['rid']);
						$mc_res = $this->setCacheData($p_dj_info_mc_key,$p_dj_info,MC_TIME_RADIO_PROGRAM_RID_DJ);
						if ($mc_res === false){
							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
						}
						foreach($p_dj_info as $p_dj_info_key => $p_dj_info_val){
							if(!empty($p_dj_info_val['program_name'])){
								$p_dj_info_uid_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_UID_DJ,$p_dj_info_key);
								$mc_res = $this->setCacheData($p_dj_info_uid_mc_key,$p_dj_info_val,MC_TIME_RADIO_PROGRAM_UID_DJ);
								if ($mc_res === false){
									$errinfo[] = "用户id：".$p_dj_info_key;
								}
							}
						}
						unset($p_dj_info);
					}
				}
			}
			for($n=0;$n<7;$n++){
				array_multisort($begintime[$n],SORT_ASC,$endtime[$n],SORT_ASC,$programList[$n]);
				$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_PID_DAY,$province_id,$n+1);
				$mc_res = $this->setCacheData($mc_key,$programList[$n],MC_TIME_RADIO_PROGRAM_PID_DAY);
				if ($mc_res === false){
					$errinfo[] = "地区id：".$province_id.",星期".$day+1;
				}
			}
		}
		
		return true;
	}
	
	
	
}
