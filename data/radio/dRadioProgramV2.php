<?php
/**
 *
 * 电台节目的data层 新
 *
 * @package
 * @author zhanghu<zhanghu@staff.sina.com.cn>
 * @copyright(c) 2013-12-17 
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
class dRadioProgramV2 extends dRadio{
	private static $db = null;//数据库句柄
	public $table_field = '`program_id`,`rid`,`program_name`,`day`,`begintime`,`endtime`,`pic_id`,`pic_path`,`intro`,`dj_info`,`topic`,`is_del`,`upuid`,`uptime`';
	public $table_name = "radio_program_v2";
	/**
	 * 根据电台id和星期几获取当天节目单
	 * @param int $rid		电台id
	 * @param string $day	星期几
	 * @param int $flag		是否从数据库提取
	 */
	
	public function getRadioProgram($rid,$day,$flag = false){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_V2,$rid,$day);
		$final_program = $this->getCacheData($mc_key);
		if($final_program == false || $flag == true){
			$is_del = 0;
			$program = $this->getProgramFromDB(array('rid' => $rid,'day' => $day,'is_del' => $is_del));
			$sort_begintime = array();
			$program_info = array();
			$n=0;
			foreach($program as $v){
				if(!empty($v['dj_info'])){
					$v['dj_info'] = array_values(unserialize($v['dj_info']));
				}
				$program_info[$n]['program_id'] = $v['program_id'];
				$program_info[$n]['program_name'] = $v['program_name'];
				$program_info[$n]['begintime'] = $v['begintime'];
				$program_info[$n]['endtime'] = $v['endtime'];
				$program_info[$n]['dj_info'] = !empty($v['dj_info']) ? $v['dj_info'] : array();
				$program_info[$n]['pid'] = $v['pic_id'];
				$program_info[$n]['pic_path'] = $v['pic_path'];
				$program_info[$n]['intro'] = $v['intro'];
				$program_info[$n]['topic'] = $v['topic'];
				$program_info[$n]['rid'] = $v['rid'];
				$program_info[$n]['day'] = $v['day'];
				$n++;
			}
			foreach($program_info as $k2=>$v2){
				$sort_begintime[$k2] = $v2['begintime'];
			}
			array_multisort($sort_begintime,SORT_ASC,$program_info);
			unset($sort_begintime);
			$final_program = array();
			$final_program['rid'] = $rid;
			$final_program['day'] = $day;
			$final_program['program_info'] = serialize($program_info);
            if(!empty($final_program)){
                $this->setCacheData($mc_key,$final_program,MC_TIME_RADIO_PROGRAM);
            }
		}
		return $final_program;
	}

	/**
	 * 根据节目名字获取节目信息
	 * @param string $name	节目名字
	 * @param int $day	星期
	 * @param int $flag	是否从数据库提取
	 */
	
	public function getRadioProgramByName($name,$day,$flag = false){
		$name_tmp = urlencode($name);
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_NAME_DAY,$name_tmp,$day);
		$program = $this->getCacheData($mc_key);
		if($program === false || $flag == true){
			$is_del = 0;
			$program = $this->getProgramFromDB(array('program_name' => $name,'day' => $day,'is_del' => $is_del));
			if(!empty($program)){
				foreach($program as &$v){
						$djinfo = unserialize($v['dj_info']);
						//补充dj信息
						$tmp =array();
						if(!empty($djinfo)){
							foreach($djinfo as $k2 => $v2){
								$tmp[$k2] = $this->getSimpleNameCard($v2['uid']);
							}
							$v['dj_info'] = $tmp;
						}else{
							$v['dj_info'] = $djinfo;
						}
				}
				unset($v);
				$this->setCacheData($mc_key,$program,MC_TIME_RADIO_PROGRAM_PID_NAME);
			}
		}
		return $program;
	}	
	
	/**
	 * 根据节目id获取节目信息
	 * @param string $program_id	节目program_id
	 * @param int $flag	是否从数据库提取
	 */
	
	public function getRadioProgramByProgramId($program_id,$flag = false){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_PID_V2,$program_id);
		$program = $this->getCacheData($mc_key);
		if($program == false || $flag == true || empty($program)){
			$is_del = 0;
			$program = $this->getProgramFromDB(array('program_id' => $program_id,'is_del' => $is_del));
			if(!empty($program)){
				$dRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
				//补充部分电台信息
				$radio_info = $dRadioInfo->getRadioInfoByRid(array($program[0]['rid']));
				foreach($program as &$v){
					//只保留需要的
					if($radio_info['errorno']==1){
						$radio_info = $radio_info['result'][$program[0]['rid']];
						$radio_name = explode('|',$radio_info['info']);
						$radio_name = $radio_name[0];
						$v['radio_name'] = $radio_name;
						$v['province_spell'] = $radio_info['province_spell'];
						$v['province_id'] = $radio_info['province_id'];
						unset($radio_info);
					}
					if(!empty($v['dj_info'])){
						$v['dj_info'] = unserialize($v['dj_info']);
					}
				}
				unset($v);
				$this->setCacheData($mc_key,$program,86400);
			}
		}
		return $program;
	}

	/**
	 * 根据电台名称搜索电台
	 * 
	 * @param radioName	电台名字
	 * @param page		页码
	 * @param pagesize	页面大小
	 * @return array array('errorno' => 1, 'result' => array())
	 */
    public function searchRadioInfoByProgramName($programName,$page=1,$pagesize=10,$fromdb=false){
		$tmp_name = urlencode($programName);
		$key = sprintf(MC_KEY_RADIO_SEARCH_TYPE_KEY_PAGE,'program_name',$tmp_name,$page);
		$res = $this->getCacheData($key);
		if(empty($res)||$fromdb==true){
			$programName = '%'.$programName.'%';
			$offset = ($page-1)*$pagesize;
			//$sql = 'SELECT '.$this->table_field.' FROM '.$this->table_name.' WHERE `is_del`= 0 AND `program_name` LIKE '."'%{$programName}%'".' GROUP BY `program_name` LIMIT '.$offset.' , '.$pagesize;
			$sql = "SELECT a.`program_id`,a.`rid`,a.`program_name`,a.`day`,a.`begintime`,a.`endtime`,a.`pic_id`,a.`pic_path`,a.`intro`,a.`dj_info`,a.`topic`,a.`is_del`,a.`upuid`,a.`uptime`,b.`domain`,b.`info`,b.`tag`,b.`recommend`,b.`img_path`,b.`intro`,b.`province_id`,b.`online`,b.`admin_uid`,b.`admin_url`,b.`program_visible` FROM radio_program_v2 a JOIN radio_info b WHERE a.`rid` = b.`rid` AND a.`is_del`= 0 AND b.`online`=1 AND a.`program_name` LIKE ? GROUP BY `program_name` ORDER BY b.`province_id`,b.`recommend`,a.`day` LIMIT $offset,$pagesize";

			$programInfo = $this->queryData($sql,array($programName));
			if(!empty($programInfo)){
				$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
				foreach($programInfo as &$v){
					if(!empty($v['dj_info'])){
						$v['dj_info'] = unserialize($v['dj_info']);
						$tmp = $objRadioInfo->getRadioInfoByRid(array($v['rid']));
						$tmp = $tmp['result'][$v['rid']];
						if($tmp['is_del']==0){
							$v['radio_info'] = $tmp;
						}
					}
				}
				unset($v);
				$res = array();
				$res['result'] = $programInfo;
				$res['pages']=ceil(count($programInfo)/$pagesize);
				$error=$this->setCacheData($key,$res,600);//缓存10分钟
				if($error===false){
					return 'fail to set mc';
				}
			}
		}
		return $res;
	}

	/**
	 * 根据dj名称搜索节目
	 * 
	 * @param radioName	电台名字
	 * @param page		页码
	 * @param pagesize	页面大小
	 * @return array array('errorno' => 1, 'result' => array())
	 */
    public function searchProgramInfoByDjName($djName,$page=1,$pagesize=5,$fromdb=false){
		$tmp_name = urlencode($djName);
		$key = sprintf(MC_KEY_RADIO_SEARCH_TYPE_KEY_PAGE,'dj_info',$tmp_name,$page);
		$res = $this->getCacheData($key);
		if(empty($res)||$fromdb==true){
			$offset = ($page-1)*$pagesize;
			//$sql = 'SELECT '.$this->table_field.' FROM '.$this->table_name.' WHERE `is_del`= 0 AND `dj_info` LIKE '."'%{$djName}%'".' GROUP BY `rid` LIMIT '.$offset.' , '.$pagesize;
			$sql = "SELECT a.`program_id`,a.`rid`,a.`program_name`,a.`day`,a.`begintime`,a.`endtime`,a.`pic_id`,a.`pic_path`,a.`intro`,a.`dj_info`,a.`topic`,a.`is_del`,a.`upuid`,a.`uptime`,b.`domain`,b.`info`,b.`tag`,b.`recommend`,b.`img_path`,b.`intro`,b.`province_id`,b.`online`,b.`admin_uid`,b.`admin_url`,b.`program_visible` FROM radio_program_v2 a JOIN radio_info b WHERE a.`rid` = b.`rid` AND a.`is_del`= 0 AND b.`online`=1 AND a.`dj_info` LIKE '%{$djName}%' GROUP BY a.`rid` ORDER BY b.`province_id`,b.`recommend`,a.`day` LIMIT {$offset},{$pagesize}";
			$programInfo =$this->_dbReadBySql($sql);
			if(!empty($programInfo)){
				$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
				foreach($programInfo as $k=>&$v){
					$v['dj_info'] = unserialize($v['dj_info']);
					if(empty($v['dj_info'])){
						unset($programInfo[$k]);
						continue;
					}
					$tmp = $objRadioInfo->getRadioInfoByRid(array($v['rid']));
					$tmp = $tmp['result'][$v['rid']];
					if($tmp['is_del']==0){
						$v['radio_info'] = $tmp;
					}
				}
				unset($v);
				$res = array();
				$res['result']=$programInfo;
				$res['pages']=ceil(count($programInfo)/$pagesize);
				$error=$this->setCacheData($key,$res,600);//缓存10分钟
				if($error===false){
					return 'fail to set mc';
				}
			}else{
				$res['result'] = array();
			}
		}
		return $res;
	}

    /**
     *用rid 和 天来删除电台节目
     *@param str $days  1,2,3,4
     *@param int $rid
     */
    public function delProgramByRidAndDay($rid, $days){
        $sqlArgs = $this->_makeUpdate($this->table_name, array('is_del'=>1), array('rid'=>$rid, 'day'=>explode(',', $days)));
        $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        $code = 'delProgramByRidAndDay error';
        if(false !== $result){
            $code =1;
        }
        return $this->returnFormat($code, $result);
    }

    /**
     *同步v2的数据到旧的节目单表  radio_program_v2 -> radio_program
     */
    public function syncProgramsToOldTable($rid, $day){
        if(!isset($rid) || !isset($day)){
            return false;
        }
        $program =  $this->getRadioProgram($rid, $day);

        $sqlArgs = $this->_makeSelect('radio_program', ' count(1) as cnt ', array('rid'=>$rid, 'day'=>$day));
        $cnt = $this->queryData($sqlArgs['sql'], $sqlArgs['data']);
        if($cnt[0]['cnt'] >=1){//存在数据则更新
            $sqlArgs = $this->_makeUpdate('radio_program', array('program_info'=>$program['program_info']), array('rid'=>$rid, 'day'=>$day));
            $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        }else{//不存在数据就插入
            $sqlArgs = $this->_makeInsert('radio_program', $program);
            $lastInsertId = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
        }

        $obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
        $obj->updateProgramMc($rid, $day);
        return $result;
    }

    /**
     *复制某天节目 到其他天
     *@param int $rid
     *@param int $from_day
     *@param str $to_day
     */
    public function copyProgram($rid, $from_day, $to_day){
        $this->_connectDb(1);
        $this->beginTransaction();

        $r = $this->delProgramByRidAndDay($rid, $to_day);

        if("1" != $r['errorno']){
            $this->rollBack();
            return $this->returnFormat('delProgramByRidAndDay erorr ', $r);
        }
        $programs = $this->getProgramFromDB(array('rid'=>$rid, 'day'=>$from_day, 'is_del' => 0));
        $objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
        foreach(explode(',', $to_day) as $day){
            foreach($programs as $program){
                $types = $objRadioProgramType->getRadioProgramType($program['program_id']);
                $types = $types['result'];
                $type_ids = array();
                foreach($types as $type){
                    $type_ids[$type['id']] = $type['id'];
                }
                unset($program['program_id']);
                $program['day'] = $day;
                $this->insertRadioProgram($program, $type_ids);
            }
        }
        $this->commit();
        return $this->returnFormat(1, $r);
    }
	
	/**
	 * 根据电台id获取该电台全部节目单信息 一周
	 * @param int $rid
	 */
	public function getProgramList($rid,$flag = false){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_LIST_NEW,$rid);
		$final_program = $this->getCacheData($mc_key);
		if($final_program == false || $flag == true){
			$is_del = '0';
			$programs = $this->getProgramFromDB(array('rid'=>$rid,'is_del' => $is_del));
			if($programs === false){
				$programs = array();
			}
			if(!empty($programs)){
				$program_info = array();
				$n = 0;
				foreach($programs as $v){
					if(!empty($v['dj_info'])){
						$v['dj_info'] = unserialize($v['dj_info']);
						/* $obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
						$dj_info = $obj->getUserInfoByUid(unserialize($v['dj_info']));
						if(!empty($dj_info)){
							$tmp_dj = array();
							foreach($dj_info as $key =>$val){
								$tmp_dj[$key]['uid'] = $val['uid'];
								$tmp_dj[$key]['url'] = $val['link_url'];
								$tmp_dj[$key]['screen_name'] = $val['screen_name'];
							}
						}
						$v['dj_info'] = NULL;
						$v['dj_info'] = $tmp_dj;
						unset($tmp_dj); */
					}
					$program_info[$v['day']-1][$n]['program_id'] = $v['program_id'];
					$program_info[$v['day']-1][$n]['program_name'] = $v['program_name'];
					$program_info[$v['day']-1][$n]['begintime'] = $v['begintime'];
					$program_info[$v['day']-1][$n]['endtime'] = $v['endtime'];
					$program_info[$v['day']-1][$n]['dj_info'] = !empty($v['dj_info']) ? $v['dj_info'] : array();
					$program_info[$v['day']-1][$n]['pid'] = $v['pic_id'];
					$program_info[$v['day']-1][$n]['pic_path'] = $v['pic_path'];
					$program_info[$v['day']-1][$n]['intro'] = $v['intro'];
					$n++;
				}
				$final_program = array();
				foreach($program_info as $k1=>$v1){
					$sort_begintime = array();
					foreach($v1 as $k2=>$v2){
						$sort_begintime[$k2] = $v2['begintime'];
					}
					array_multisort($sort_begintime,SORT_ASC,$v1);
					unset($sort_begintime);
					$final_program[$k1]['rid'] = $rid;
					$final_program[$k1]['day'] = $k1+1;
					$final_program[$k1]['program_info'] = $v1;
				}
				$this->setCacheData($mc_key,$final_program,MC_TIME_RADIO_PROGRAM_LIST);
			}
		}
		return $final_program;
	}
	
	
	/**
	 * 根据查询条件获取节目单信息（数据库）
	 * @param array $whereArgs
	 */
	public function getProgramFromDB($whereArgs){
		$sqlArgs = $this->_makeSelect($this->table_name, $this->table_field, $whereArgs, array());
//		echo '<pre>';
//		print_r($sqlArgs);exit;
		return $this->queryData($sqlArgs['sql'], $sqlArgs['data']);
	}
	
	/**
	 * 插入电台节目单信息
	 * @param array $args
	 */
	public function insertRadioProgram(Array $program, Array $types){
		$mc_key1 = sprintf(MC_KEY_RADIO_PROGRAM_V2, $program['rid'], $program['day']);
		$mc_key2 = sprintf(MC_KEY_RADIO_PROGRAM_LIST_NEW, $program['rid']);
		$mc_key3 = sprintf(MC_KEY_RADIO_PROGRAM_V2_2, $program['rid'], $program['day']);
		$sqlArgs = $this->_makeInsert($this->table_name, $program);
        $this->_connectDb(1);
        $this->beginTransaction();
        $lastInsertId = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
        if(is_numeric($lastInsertId)){
            $programId = $lastInsertId;
            $objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
            foreach($types as $typeId){
                $result = $objRadioProgramType->insertRadioProgramTypeMap(array('program_id'=>$programId, 'program_type_id'=>$typeId));
                if(!is_numeric($result['result'])){
                    $this->rollBack();
                    return $this->returnFormat('insertRadioProgramTypeMap error', $result);
                }
            }
        }else{
            $this->rollBack();
            return $this->returnFormat('insertRadioProgram error', $lastInsertId);
        }
        $this->commit();
        $this->delCacheData($mc_key1);
        $this->delCacheData($mc_key2);
        $this->delCacheData($mc_key3);
        $this->syncProgramsToOldTable($program['rid'], $program['day']);
		return $this->returnFormat(1, $result);
	}



	/**
	 * 更新电台节目单信息
	 * @param array $args
	 */
	public function updateRadioProgram(Array $args, $types){
		$mc_key1 = sprintf(MC_KEY_RADIO_PROGRAM_V2, $args['rid'], $args['day']);
		$mc_key2 = sprintf(MC_KEY_RADIO_PROGRAM_LIST_NEW, $args['rid']);
		$mc_key3 = sprintf(MC_KEY_RADIO_PROGRAM_V2_2, $args['rid'], $args['day']);
		//error_log(strip_tags(print_r($type, true))."\n", 3, "/tmp/err.log");
        $this->delCacheData($mc_key1);
        $this->delCacheData($mc_key2);
        $this->delCacheData($mc_key3);
        $this->_connectDb(1);
        $this->beginTransaction();
		$program_id = $args['program_id'];
		$whereArgs = array('program_id' => $program_id);
		unset($args['program_id']);
		$sqlArgs = $this->_makeUpdate($this->table_name, $args, $whereArgs);
        $r = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
        if(false === $r){
            $this->rollBack();
            return $this->returnFormat('updateRadioProgram error', $r);
        }
        if($args['is_del'] == "1"){//如果删除的话把is_del就弄成1就行了
            $this->commit();
            $this->syncProgramsToOldTable($args['rid'], $args['day']);
            return $this->returnFormat(1, $r);
        }
        $objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
        $r = $objRadioProgramType->delRadioProgramTypeMapByProgramId($program_id);
        if(!is_numeric($r['result'])){
            $this->rollBack();
            return $this->returnFormat('updateRadioProgram-delRadioProgramTypeMapByProgramId  error', $r);
        }

        foreach($types as $type_id){
            $result = $objRadioProgramType->insertRadioProgramTypeMap(array('program_id'=>$program_id, 'program_type_id'=>$type_id));
            if(!is_numeric($result['result'])){
                $this->rollBack();
                return $this->returnFormat('updateRadioProgram-insertRadioProgramTypeMap  error', $result);
            }
        }
        $this->commit();
        $this->delCacheData($mc_key1);
        $this->delCacheData($mc_key2);
        $this->delCacheData($mc_key3);
        $this->syncProgramsToOldTable($args['rid'], $args['day']);
		return $this->returnFormat(1, $r);
	}
	

	/**
	 * 根据电台id和星期几获取当天节目单
	 * @param int $rid		电台id
	 * @param string $day	星期几
	 * @param int $flag		是否从数据库提取
	 */
	public function getRadioProgram2($rid,$day,$flag = false){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_V2_2,$rid,$day);
		$program = $this->getCacheData($mc_key);
		if($program == false || $flag == true || empty($program)){
			$is_del = 0;
			$sql = "SELECT {$this->table_field} FROM {$this->table_name} WHERE `rid` = '{$rid}' AND `day` = '{$day}' AND `is_del`= 0 ORDER BY `begintime`";
			$program =$this->_dbReadBySql($sql);
			//print_r($program);exit;
			foreach($program as &$v){
				if(!empty($v['dj_info'])){
					$v['dj_info'] = unserialize($v['dj_info']);
					//补充dj信息
					unset($tmp);
					foreach($v['dj_info'] as $k2 => $v2){
						$tmp[$k2] = $this->getSimpleNameCard($v2['uid']);
					}
					$v['dj_info'] = $tmp;
				}
			}
			unset($v);
			if(!empty($program)){
				$this->setCacheData($mc_key,$program,MC_TIME_RADIO_PROGRAM);
			}
		}

		return $program;
	}
	/**
	 * 根据电台所有节目(专门提供节目话题)
	 * @param int $flag		是否从数据库提取
	 * 返回值	program_name rid program_id
	 */
	public function getAllRadioProgramForTag($flag = false){
//		$mc_key = sprintf(MC_KEY_RADIO_ALL_PROGRAM_V2);
//		$program = $this->getCacheData($mc_key);
//		if($program == false || $flag == true || empty($program)){
		$sql = "SELECT rid,program_id, program_name FROM radio_program_v2 WHERE is_del =0 GROUP BY program_name";
		$program =$this->_dbReadBySql($sql);
//			if(!empty($program)){
//				$this->setCacheData($mc_key,$program,3600);
//			}
//		}
		return $program;
	}


	/*
	 * 获取电台展示名片（微电台专用）
	* @param int $uid
	* @return array
	*/
	public function getRadioCardByUid($uid,$fromdb = false){
		//print_r($uid);
		//exit;
		//$uid=1829232864;
		$mc_key = sprintf(MC_KEY_RADIO_CARD,$uid);
		$result = $this->getCacheData($mc_key);
		//$fromdb = true;
		if($result== false|| $fromdb == true){
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
					$result='can not find';
				}
				foreach($user_info as $value){
					$result = array(
						'uid' => $value['uid'],
						'url' => $value['link_url'],
						'name' => $value['name'],
						'sex' => $value['gender'] == 'm' ? 'male' : 'female',
						'icon' => $value['avatar_large'],
						'description' => $value['description'],
						'relation' => 0,
						'user_type' => $value['user_type']
						//'program_name' => $user_program_info,
					);
				}
			//关注关系	
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$cuid = $person->getCurrentUserUid();
			if($cuid > 0){
				//1657022395 1657022397   3970089458
				$relation = $person->getRelation2($cuid,array($uid));
				$relation = $relation['result']['result'] ;
				if(!empty($relation)){
					$result['relation']='1';//表示已经关注
				}
			}

			if(!empty($result)){
				$this->setCacheData($mc_key,$result,MC_TIME_RADIO_CARD);
			}
		}
		return $result;
	}


//getRadioByDomainAndPro
	/*
	 * 获取用户简单信息名片
	 * @param int $uid
	 * @return array
	 */
	public function getSimpleNameCard($uid){
		$mc_key = sprintf(MC_KEY_RADIO_SIMPLE_NAME_CARD,$uid);
		$result = $this->getCacheData($mc_key);
		//$result = false;
		if(empty($result)||$result== false){
			$user_info = $this->getUserInfoByUid(array($uid));
			if(empty($user_info)){
				return false;
			}
			foreach($user_info as $value){
				$result = array(
					'uid' => $value['uid'],
					'description' => $value['description'],
					'url' => $value['link_url'],
					'location' => $value['location'],
					'name' => $value['name'],
					'icon' => $value['avatar_large'],
					'sex' => $value['gender'] == 'm' ? 'male' : 'female',
					'followers_count' => $value['followers_count'],
					'friends_count' => $value['friends_count'],
					'statuses_count' => $value['statuses_count'],
					'user_type' => $value['user_type'],
					'relation' => 0
				);
			}
		}
		$this->setCacheData($mc_key,$result,7200);
		//关注关系
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$cuid = $person->getCurrentUserUid();
		if($cuid > 0){
			//1657022395 1657022397   3970089458
			$relation = $person->getRelation2($cuid,array($uid));
			$relation = $relation['result']['result'] ;
			if(!empty($relation)){
				$result['relation']='1';//表示已经关注
			}
		}
		return $result;
	}

	/*
	 * 获取用户简单信息名片 批量获取
	 * @param array $uids
	 * @return array
	 */
	public function getSimpleNameCard2($uids){
		if(!is_array($uids) || empty($uids)){
			return false;
		}
		$keys = array();
		foreach($uids as &$v){
			$keys[] = sprintf(MC_KEY_RADIO_SIMPLE_NAME_CARD,$v);
		}
		unset($v);
		$result = $this->getMultiCacheData($keys);
		foreach($result as $k=>$v){
			if(empty($v)){
				foreach($uids as $v2){
					$this->getSimpleNameCard($v2);
					usleep(5000);
				}
				break;
			}
		}
		//关注关系
		$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
		$cuid = $person->getCurrentUserUid();
		if($cuid > 0){
			$relation = $person->getRelation2($cuid,$uids);
			$relation = $relation['result']['result'] ;
			foreach($relation as &$v){
				$v = $v['id'];
			}
			unset($v);
			if(!empty($relation)){
				foreach($result as &$v){
					if(in_array($v['uid'],$relation)){
						$v['relation']='1';
					}
				}
				unset($v);
			}
		}
		return $result;
	}

	
	/*
	 * 获取dj名片（微电台专用）
	 * @param int $uid
	 * @return array
	 */
	public function getNameCard($uid,$pname=''){
		$mc_key = sprintf(MC_KEY_RADIO_NAME_CARD,$uid);
		$result = $this->getCacheData($mc_key);
		//@test
		//$result=false;
		if($result == false||empty($result)){
			//用户主持过的节目信息
			//$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
			$user_program_info = $this->getProgramByUid($uid);
			$str=parse_url($user_program_info['radio_url'],PHP_URL_PATH);
			$str=explode('/',$str);	
			$domain=$str['2'];
			$province_spell=$str['1'];
			$dRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
			$radioInfo = $dRadioInfo->getRadioByDomainAndPro($domain,$province_spell);
			if($radioInfo['result']['online']==1){
				$tmp['rid']=$radioInfo['result']['rid'];
				$tmp['uid']=$radioInfo['result']['uid'];
				$tmp['url']=$radioInfo['result']['url'];
				$tmp['name']=$radioInfo['result']['name'];
				unset($radioInfo);
				$radioInfo=$tmp;
				unset($tmp);
			}else{
				$radioInfo=array();
			}
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
						'description' => $value['description'],
						'icon' => $value['avatar_large'],
						'sex' => $value['gender'] == 'm' ? 'male' : 'female',
						'followers_count' => $value['followers_count'],
						'friends_count' => $value['friends_count'],
						'statuses_count' => $value['statuses_count'],
						'user_type' => $value['user_type'],
						'radioInfo' => $radioInfo,
						'program_name' => $user_program_info,
						'relation' => 0
					);
				}
			$this->setCacheData($mc_key,$result,MC_TIME_RADIO_NAME_CARD);
			//关注关系
			$person = clsFactory::create(CLASS_PATH.'model','mPerson','service');
			$cuid = $person->getCurrentUserUid();
			if($cuid > 0){
				//1657022395 1657022397   3970089458
				$relation = $person->getRelation2($cuid,array($uid));
				$relation = $relation['result']['result'] ;
				if(!empty($relation)){
					$result['relation']='1';//表示已经关注
				}
			}
		}
		return $result;
	}


	/**
	 * 更新每天的热门节目（定时任务）
	 */
	public function updateHotProgramByDay2(){
		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')));
		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-68,date('Y')));
		$hot_endtime = $hot_begintime;
		$hotprogram = array();
		$result = parent::getHotProgram($hot_begintime,$hot_endtime,1,50,1);
		$pagecount = $result['pageCount'];
		unset($result);
		$day=date('N');
		if($pagecount > 0){
			for($page=1;$page<=$pagecount;$page++){
				$result = parent::getHotProgram($hot_begintime,$hot_endtime,1,50,$page);
				usleep(50000);
				$result = $result['rs'];
				foreach($result as $v){
					$tmp = self::getRadioProgramByName($v['pname'],$day,false);
					if(empty($tmp)){
						continue;
					}
					$tmp = $tmp[0];
					$tmp ['orders'] = $v['orders'];
					$hotprogram[]=$tmp;
				}
//				break;
			}
		}
		//存放下标 由于0-n 所以存放n就够了
		$keys = count($hotprogram);
		$mc_key = MC_KEY_RADIO_HOT_PROGRAM_DAY_KEY_V2;
		$res = $this->setCacheData($mc_key,$keys,864000);
		$types = array();//统计所有热门节目的分类
		$objRadioProgramType= clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioProgramType', 'service');
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		if(!empty($hotprogram)){
			foreach($hotprogram as $k=>&$v){
				$type=$objRadioProgramType->getRadioProgramType($v['program_id']);
				$type = $type['result'];
				if(!empty($type)){
					$v['type'] = $type;
					foreach($type as $v2){
						$types[$v2['sort']] = $v2;
					}
				}else{
					$v['type'] = array();
				}
				$radio_info=$objRadioInfo->getRadioInfoByRid(array($v['rid']));
				$v['radio_info'] = $radio_info['result'][$v['rid']];
				if(!empty($v)){
					$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_V2,$k);
					$mc_res = $this->setCacheData($mc_key,$v,864000);
				}
				if ($mc_res === false){
					$errinfo[] = $v['program_id'];
				}
			}
			unset($v);
		}
		$mc_key = MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES;//存放 所有热门节目的分类
		$this->setCacheData($mc_key,$types,86400);
		if(empty($hotprogram)){
				$errinfo[] = '无热门节目信息';
		}
		if(!empty($errinfo)){
			$str = implode("|",$errinfo);
			$this->writeRadioErrLog(array('更新失败信息',$str), 'RADIO_ERR');
		}
		echo '更新'.$keys."个节目完毕\n";
	}

//	public function updateHotProgramByDay2(){
//		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')));
////		@test 数据丢失时 往前推 取数据
//		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-34,date('Y')));
//		$hot_endtime = $hot_begintime;
//		$hotprogram = array();
//		$result = parent::getHotProgram($hot_begintime,$hot_endtime,1,50,1);
//		$pagecount = $result['pageCount'];
//		unset($result);
//		$day=date('N');
//		if($pagecount > 0){
//			$n = 0;
//			$keys = array();
//			for($page=1;$page<=$pagecount;$page++){
//				$result = parent::getHotProgram($hot_begintime,$hot_endtime,1,50,$page);
//				$result = $result['rs'];
//				if(!empty($result)){
//					foreach($result as $v){
//						$tmp=$this->getRadioProgramByName($v['pname'],$day);
//						if(empty($tmp)){
//							continue;
//						}
//
//						$key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_V2,$n);
//						$keys[] = $key;
//						$hotprogram[$key]=$tmp[0];
//						$hotprogram[$key]['orders']=$v['orders'];
//						$n++;
//					}
//				}
//				usleep(50000);
//			}
//		}
//
//		$mc_key = MC_KEY_RADIO_HOT_PROGRAM_DAY_KEY_V2;
//		$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
//		$this->setCacheData($mc_key,$keys,86400);
//		if(empty($hotprogram)){
//			$errinfo[] = '无热门节目信息';
//		}
//		$program_info = array();
//		$types = array();//统计所有热门节目的分类
//		foreach($hotprogram as $k=>&$v){
//			//$v['program_id'] = 123012;
//			//$mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
//			$type=$mRadio->getRadioProgramType($v['program_id']);
//			if(!empty($type['result'])){
//				foreach($type['result'] as $v2){
//					$types[$v2['sort']] = $v2;
//				}
//			}
//			//@test 强制添加分类 测试用
//			//$type=$mRadio->getRadioProgramType(1067);
//			$radio_info=$mRadio->getRadioInfoByRid(array($v['rid']));
//			$v['radio_info'] = $radio_info['result'][$v['rid']];
//			if(!empty($type['result'])){
//				$v['type']=$type['result'];
//			}else{
//				$v['type']=0;
//			}
//			if ($mc_res === false){
//				$errinfo[] = $v['program_id'];
//			}
//			$this->setCacheData($k,$v,86400);
//		}
//		unset($v);
//		$mc_key = MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES;//存放 所有热门节目的分类
//		$this->setCacheData($mc_key,$types,86400);
//		//@test 临时改时间为3000秒
////		$this->setMultiCacheData($hotprogram,MC_TIME_RADIO_HOT_PROGRAM_DAY);
//		
//		if(!empty($errinfo)){
//			$str = implode("|",$errinfo);
//			return "更新失败信息：".$str;
//		}
//		echo '更新'.$n."个节目完毕\n";
//		return $n;
//	}


	/*
	 * 通过时间获取当天热门节目
	 * @return array
	 */
	public function getHotProgramByDay2(){
		$mc_key = MC_KEY_RADIO_HOT_PROGRAM_DAY_KEY_V2;
		$keys = $this->getCacheData($mc_key);
		if($keys<1){
			$programInfo['error']='please update MC OR MC ERROR';
		}
		$mc_key = array();
		for($i=0;$i<$keys;$i++){
			$mc_key[] = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_V2,$i);
		}
		$programInfo = $this->getMultiCacheData($mc_key);
		//mc中备份一份 放10天 @test
//		$rand = rand(1,100);
//		if($programInfo && $rand<10){
//			$tmp = array_slice($programInfo,0,1000);
//			$mc_key = md5('getHotProgramByDay2.tmp');
//			$this->setCacheData($mc_key,$tmp,864000);
//		}
//		$programInfo =array();
//		if(empty($programInfo)){
//			$mc_key = md5('getHotProgramByDay2.tmp');
//			$programInfo = $this->getCacheData($mc_key);
//		}
		return $programInfo;
	}

	//获取所有节目的话题(即节目名称)
	public function getProgramTopic(){
		$sql="SELECT DISTINCT `program_name` FROM {$this->table_name} WHERE `is_del`=0";
		$res =$this->_dbReadBySql($sql);
		return $this->returnFormat(1,$res);
	}



	/**
	 * 更新计算每个省份的节目数量 
	 */
	public function updateAllProgramNumber(){
		$sql = 'SELECT count(*) `number`,b.`province_id` FROM radio_program_v2 a JOIN radio_info b WHERE a.`rid` = b.`rid` AND a.`is_del`= 0 AND b.`online`=1 group by b.`province_id`';
		$programNumber =$this->_dbReadBySql($sql);
		if(!empty($programNumber)){
			foreach($programNumber as $v){
				$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_NUM_PID,$v['province_id']);
				if($v['number']){
					$this->setCacheData($mc_key,$v['number'],86400);
				}else{
					$this->setCacheData($mc_key,0,86400);
				}
			}
		}
	}

	/**
	 * 获取各省份的节目数量（定时任务）按省分类
	 */
	 public function getProgramNumberByProvince($pid){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_NUM_PID,$pid);
		$programNumber = $this->getCacheData($mc_key);
		return $programNumber;
	}

	/**
	 * 更新热门节目（定时任务）按省分类 
	 */
	public function updateHotProgram2(){
		$today = getdate();
		$today['wday'] = $today['wday'] == 0 ? 7 : $today['wday'];
		$interval = 83+$today['wday'];//@test暂时改为62  需要改回6
		$begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$interval,date('Y')));
		$endtime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-$today['wday'],date('Y')));
		$hotprogram = array();
		$errinfo = array();
		$objRadioInfo = clsFactory::create(CLASS_PATH.'data/radio', 'dRadioInfo', 'service');
		$result = parent::getHotProgram($begintime,$endtime,2,50,1);
		if(!empty($result['rs'])){
			//获取节目top10
			$mc_key = MC_KEY_RADIO_PROGRAM_TOP10;
			$tmp = array();
			$tmp = array_slice($result['rs'],0,10);
			$total = $result['recordCount'];
			foreach($tmp as $k=>&$v){
				$day = date('N',strtotime($v['day_key']));//获取是星期几的节目
				$programInfo = self::getRadioProgramByName($v['pname'],$day);
				$v['program_info'] = $programInfo[0];//此处处理同一个节目一天播放多次 比如央广新闻 一天上下午各一次，此处取第一个
				$radioInfo = $objRadioInfo->getRadioInfoByRid(array($v['vradio_id']));
				if($radioInfo['errorno']==1){
					$v['radio_info'] = $radioInfo['result'][$v['vradio_id']];
				}
			}	
			unset($v);
			$program['result'] = $tmp ;
			$program['total'] = $total ;
			$this->setCacheData($mc_key,$program,864000);
			unset($program);
		}
		$pagecount = $result['pageCount'];
		if($pagecount > 0){
			$maxpage = $pagecount;			
			for($page=1;$page<=$maxpage;$page++){
				//分次获得排行列表
				$result = parent::getHotProgram($begintime,$endtime,2,50,$page);
				usleep(50000);
				if(empty($result['rs'])){
					break;
				}
				foreach($result['rs'] as &$v){
					//按省份将节目进行分类  每个省都只取前十
					if(count($res[$v['p_id']])>=10){
						continue;
					}
					$res[$v['p_id']][]=$v;
				}
				unset($v);
			}
			//取出每个省的前十名补充节目信息
			unset($result);
			foreach($res as $k=>&$v){
				foreach($v as $k2=>$v2){
					$day = date('N',strtotime($v2['day_key']));//获取是星期几的节目
					$programInfo = self::getRadioProgramByName($v2['pname'],$day);
					$v[$k2]['program_info'] = $programInfo[0];//此处处理同一个节目一天播放多次 比如央广新闻 一天上下午各一次，此处取第一个
					$radioInfo = $objRadioInfo->getRadioInfoByRid(array($v[$k2]['vradio_id']));
					if($radioInfo['errorno']==1){
						$v[$k2]['radio_info'] = $radioInfo['result'][$v[$k2]['vradio_id']];
					}
				}
				if(!empty($v)){
					$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_PID,$k);
					$mc_res=$this->setCacheData($mc_key,$v,MC_TIME_RADIO_HOT_PROGRAM_PID);
				}
			}
			unset($v);
		}
		return $mc_res;
	}


	/**
	 * 获取热门节目按省分类
	 */
	 public function getHotProgramRankByPid2($pid){
		$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_PID,$pid);
		$programInfo = $this->getCacheData($mc_key);
		if(!empty($programInfo)){
			$programInfo = array_slice($programInfo,0,10,true);
			//10天缓存 防过期 @test
//			if($programInfo){
//				$mc_key = md5('MC_KEY_RADIO_HOT_PROGRAM_PID'.$pid);
//				$this->setCacheData($mc_key,$programInfo,864000);
//			}else{
//				$mc_key = md5('MC_KEY_RADIO_HOT_PROGRAM_PID'.$pid);
//				$programInfo = $this->getCacheData($mc_key);
//			}
		}
		return $programInfo;
	}

	/**
	 * 获取热门节目（定时任务）所有电台中筛选出
	 */
	 public function getHotProgramTop10(){
		$mc_key = MC_KEY_RADIO_PROGRAM_TOP10;
		$programInfo = $this->getCacheData($mc_key);
		//10天缓存 防过期 @test
//		if($programInfo){
//			$mc_key = md5('MC_KEY_RADIO_PROGRAM_TOP10');
//			$this->setCacheData($mc_key,$programInfo,864000);
//		}else{
//			$mc_key = md5('MC_KEY_RADIO_PROGRAM_TOP10');
//			$programInfo = $this->getCacheData($mc_key);
//		}
		return $programInfo;
	}
	


////某电台按节目名称统计的节目信息define('MC_KEY_RADIO_PROGRAM_RID_NAME',CACHE_RADIO.'_program_rid_name_%s'.CACHE_RADIO_POSTFIX);  MC_TIME_RADIO_PROGRAM_RID_NAME
////某电台按节目dj统计的节目信息 define('MC_KEY_RADIO_PROGRAM_RID_DJ',CACHE_RADIO.'_program_rid_dj_%s'.CACHE_RADIO_POSTFIX);  
////按节目dj统计的节目信息define('MC_KEY_RADIO_PROGRAM_UID_DJ',CACHE_RADIO.'_program_uid_dj_%s'.CACHE_RADIO_POSTFIX); 
//
////获得所有电台节目的信息
//	public function updateAllRadioProgramV2(){
//		$objRadioInfo = clsFactory::create(CLASS_PATH . 'data/radio', 'dRadioInfo', 'service');
//		//获取按地区全部电台列表信息
//		$radioList = $objRadioInfo->getRadioList();
//		$radioList = $radioList['result'];
//		if(empty($radioList)){
//			return false;
//		}
//		$errinfo = array();
//		foreach($radioList as $province_id => $p_radiolist){
//			$programList = array();
//			$p_dj_info = array();
//			$p_info_by_name = array();
//			$begintime = array();
//			$endtime = array();
//			foreach($p_radiolist as $p_radioinfo){
//				if($p_radioinfo['online'] == '1' && $p_radioinfo['program_visible'] == '2'){
//					//获取某单个电台一周的全部节目单
//					$programs = $this->getProgramList($p_radioinfo['rid']);
//					if(!empty($programs)){
//						$order_begintime = array();
//						$order_endtime = array();
//						//按天遍历节目单信息
//						foreach($programs as $day => $p_info){
//							$program_info = unserialize($p_info['program_info']);
//							if(!empty($program_info)){
//								//对每天的节目单按节目遍历
//								foreach ($program_info as &$p_info_v){
//									if(!empty($p_info_v['program_name'])){
//										//按电台筛选节目详情信息
//										if(empty($p_info_by_name[$p_info_v['program_name']])){
//											$p_info_by_name[$p_info_v['program_name']]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
//											$p_info_by_name[$p_info_v['program_name']]['radio_name'] = $p_radioinfo['name'];
//											$p_info_by_name[$p_info_v['program_name']]['province_id'] = $p_radioinfo['province_id'];
//											$p_info_by_name[$p_info_v['program_name']]['name'] = $p_info_v['program_name'];
//											$p_info_by_name[$p_info_v['program_name']]['dj_info'] = $p_info_v['dj_info'];
//											$p_info_by_name[$p_info_v['program_name']]['pid'] = !empty($p_info_v['pid']) ? $p_info_v['pid'] : "";
//											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = !empty($p_info_v['pic_path']) ? $p_info_v['pic_path'] : "";
//											$p_info_by_name[$p_info_v['program_name']]['intro'] = !empty($p_info_v['intro']) ? $p_info_v['intro'] : "";
//										}
//										else{
//											if(!empty($p_info_v['dj_info'])){
//												foreach($p_info_v['dj_info'] as $p_djinfo_key => $p_djinfo_val){
//													$p_info_by_name[$p_info_v['program_name']]['dj_info'][$p_djinfo_key] = $p_djinfo_val;
//												}
//											}
//										}
//										//初始化数组 节目->时间->节目起始时间->节目结束时间
//										if(empty($p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']])){
//											$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']] = array(0,0,0,0,0,0,0);
//										}
//										if(empty($p_info_by_name[$p_info_v['program_name']]['pid']) && !empty($p_info_v['pid'])){
//											$p_info_by_name[$p_info_v['program_name']]['pid'] = $p_info_v['pid'];
//											$p_info_by_name[$p_info_v['program_name']]['pic_path'] = $p_info_v['pic_path'];
//										}
//										if(empty($p_info_by_name[$p_info_v['program_name']]['intro']) && !empty($p_info_v['intro'])){
//											$p_info_by_name[$p_info_v['program_name']]['intro'] = $p_info_v['intro'];
//										}
//
//										$p_info_by_name[$p_info_v['program_name']]['date'][$p_info_v['begintime']][$p_info_v['endtime']][$day] = 1;
//										//按照节目开始时间排序字段
//										if(empty($order_begintime[$p_info_v['program_name']])){
//											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
//										}
//										elseif($order_begintime[$p_info_v['program_name']] > strtotime($p_info_v['begintime'])){
//											$order_begintime[$p_info_v['program_name']] = strtotime($p_info_v['begintime']);
//										}
//										//按照节目开始时间排序字段
//										if(empty($order_endtime[$p_info_v['program_name']])){
//											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
//										}
//										elseif($order_endtime[$p_info_v['program_name']] > strtotime($p_info_v['endtime'])){
//											$order_endtime[$p_info_v['program_name']] = strtotime($p_info_v['endtime']);
//										}
//
//										//按电台筛选电台DJ主持的节目
//										if(!empty($p_info_v['dj_info'])){
//											foreach($p_info_v['dj_info'] as $p_dj_info_key => $p_dj_info_v){
//												if(empty($p_dj_info[$p_dj_info_key])){
//													$p_dj_info[$p_dj_info_key] = $p_dj_info_v;
//													$p_dj_info[$p_dj_info_key]['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
//												}
//												if(!in_array($p_info_v['program_name'],$p_dj_info[$p_dj_info_key]['program_name'])){
//													$p_dj_info[$p_dj_info_key]['program_name'][] = $p_info_v['program_name'];
//												}
//											}
//										}
//
//										$p_info_v['rid'] = $p_radioinfo['rid'];
//										$p_info_v['name'] = $p_radioinfo['name'];
//										$p_info_v['radio_url'] = RADIO_URL."/".$p_radioinfo['province_spell'].'/'.$p_radioinfo['domain'];
//										$begintime[$day][] = strtotime($p_info_v['begintime']);
//										$endtime[$day][] = strtotime($p_info_v['endtime']);
//									}
//								}
//								unset($p_info_v);
//								if(empty($programList[$day])){
//									$programList[$day] = $program_info;
//								}
//								else{
//									$programList[$day] = array_merge($programList[$day],$program_info);
//								}
//							}
//						}
//						//按节目开始时间进行排序
//						array_multisort($order_begintime,$order_endtime,$p_info_by_name);
//						unset($order_begintime);
//						unset($order_endtime);
//						//种按电台筛选节目详情信息
//						$week = array(1 => "一",2 => "二",3 => "三",4 => "四",5 => "五",6 => "六",7 => "日");
//						$programInfoByName = array();
//						$order = 1;
//						foreach($p_info_by_name as $key => $value){
//							if(is_array($value['date'])){
//								$programInfoByName[$key] = $value;
//								foreach($value['date'] as $ary_begintime => $p_date_val){
//									foreach ($p_date_val as $ary_endtime => $ary_day){
//										$p_date = array();
//										$p_date_key = 0;
//										for ($n=0;$n<7;$n++){
//											$day_val = array_shift($ary_day);
//											if($day_val == 1){
//												$p_date[$p_date_key][] = $week[$n+1];
//											}
//											else{
//												if($n > 0){
//													$p_date_key++;
//												}
//											}
//										}
//										$date_info = array();
//										foreach ($p_date as $tmp_k => $tmp_v){
//											$tmp_count = count($tmp_v);
//											if($tmp_count < 3){
//												$date_info[] = "周".implode(',',$tmp_v);
//											}
//											else{
//												$date_info[] = "周".$tmp_v[0]."至周".$tmp_v[$tmp_count-1];
//											}
//										}
//
//										$programInfoByName[$key]['showtime'][] = implode(',',$date_info).' '.$ary_begintime.'-'.$ary_endtime;
//									}
//									$programInfoByName[$key]['showtime_info'] = implode(' ',$programInfoByName[$key]['showtime']);
//									$programInfoByName[$key]['showtime_count'] = count($programInfoByName[$key]['showtime']);
//								}
//							}
//							$programInfoByName[$key]['order'] = $order;
//							$order++;
//						}
//						$p_info_by_name_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_NAME,$p_radioinfo['rid']);
//						$mc_res = $this->setCacheData($p_info_by_name_mc_key,$programInfoByName,MC_TIME_RADIO_PROGRAM_RID_NAME);
//						if ($mc_res === false){
//							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
//						}
//						unset($p_info_by_name);
//						//种按电台筛选电台DJ主持的节目
//						$p_dj_info_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_RID_DJ,$p_radioinfo['rid']);
//						$mc_res = $this->setCacheData($p_dj_info_mc_key,$p_dj_info,MC_TIME_RADIO_PROGRAM_RID_DJ);
//						if ($mc_res === false){
//							$errinfo[] = "电台id：".$p_radioinfo['rid'].",名称：".$p_radioinfo['name'];
//						}
//						foreach($p_dj_info as $p_dj_info_key => $p_dj_info_val){
//							if(!empty($p_dj_info_val['program_name'])){
//								$p_dj_info_uid_mc_key = sprintf(MC_KEY_RADIO_PROGRAM_UID_DJ,$p_dj_info_key);
//								$mc_res = $this->setCacheData($p_dj_info_uid_mc_key,$p_dj_info_val,MC_TIME_RADIO_PROGRAM_UID_DJ);
//								if ($mc_res === false){
//									$errinfo[] = "用户id：".$p_dj_info_key;
//								}
//							}
//						}
//						unset($p_dj_info);
//					}
//				}
//			}
//			for($n=0;$n<7;$n++){
//				array_multisort($begintime[$n],SORT_ASC,$endtime[$n],SORT_ASC,$programList[$n]);
//				$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_PID_DAY,$province_id,$n+1);
//				$mc_res = $this->setCacheData($mc_key,$programList[$n],MC_TIME_RADIO_PROGRAM_PID_DAY);
//				if ($mc_res === false){
//					$errinfo[] = "地区id：".$province_id.",星期".$day+1;
//				}
//			}
//		}
//		if(!empty($errinfo)){
//			$str = implode("|",$errinfo);
//			return "更新失败信息：".$str;
//		}
//		return true;
//	}
					











    //-----------下面的方法都不可用....
	
	/**
	 * 更新电台节目单信息
	 * @param array $args
	 */
	//public function updateRadioProgram($args){
	//	$db = $this->initDb();
	//	if(false == $db) {
	//		return $this->returnFormat('RADIO_D_DB_00001');
	//	}
	//	$program_id = $args['program_id'];
	//	$whereArgs = array('program_id' => $program_id);
	//	unset($args['program_id']);
	//
	//	$sqlArgs = $this->_makeUpdate($this->table_name, $args, $whereArgs);
	//	$st = $db->prepare($sqlArgs['sql']);
	//	if(false == $st->execute($sqlArgs['data'])) {
	//		$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
	//		return $this->returnFormat('RADIO_D_DB_00002');
	//	}
	//	// 更新指定天的电台节目单
	//	return $this->returnFormat(1);
	//}
	
	/**
	 * 更新用户电台节目单缓存
	 * @param int $rid
	 * @param string $day
	 * @param array $data
	 */
	public function updateProgramMc($rid,$day){
		//新增更新纬度的电台节目单
		$this->updateSimpleRadioProgram($rid);
		return $this->getRadioProgram($rid,$day,true);
	}
	
	/**
	 * 更新用户电台节目单缓存
	 * @param int $rid
	 * @param string $day
	 * @param array $data
	 */
	public function updateProgramListMc($rid){
		//更新节目单
		return $this->getProgramList($rid,true);
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
				if(!empty($value['dj_info'])){
					foreach($value['dj_info'] as $key => $val){
						if(!in_array($key,$dj_uids)){
							$dj_uids[] = $key;
						}
					}
				}
			}
			if(!empty($dj_uids)){
				$dj_infos = $this->getUserInfoByUid($dj_uids);
				if($dj_infos === false){
					return false;
				}
				foreach($program_info as &$value){
					if(!empty($value['dj_info'])){
						foreach($value['dj_info'] as $key => &$val){
							$val['screen_name'] = !empty($val['screen_name']) ? $val['screen_name'] : $dj_infos[$key]['name'];
							$val['userinfo'] = $dj_infos[$key];
						}
					}
				}
			}
			$result = $program_info;
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
	
//	/**
//	 * 更新热门节目（定时任务）
//	 */
//	public function updateHotProgramByDay(){
//		$hot_begintime = date('Y-m-d',mktime(0,0,0,date('m'),date('d')-7,date('Y')));
//		$hot_endtime = $hot_begintime;
//
//		$hotprogram = array();
//
//		$result = parent::getHotProgram($hot_begintime,$hot_endtime,1,50,1);
//		//var_dump($result);
//		//@test
//		//print_r($result);
//		//exit;
//		$pagecount = $result['pageCount'];
//		if($pagecount > 0){
//			$today = getdate();
//			$today['wday'] = $today['wday'] == 0 ? 7 : $today['wday'];
//			$wday = $today['wday']-1;
//			for($page=1;$page<=$pagecount;$page++){
//				$result = $this->getHotProgram($hot_begintime,$hot_endtime,1,50,$page);
//				foreach($result['rs'] as $rs_value){
//					$programinfo = $this->getProgramForNameByRid($rs_value['vradio_id'],0);
//					$rs_value['pinfo'] = !empty($programinfo[$rs_value['pname']]) ? $programinfo[$rs_value['pname']] : array();
//
//					$begintime = array_keys($rs_value['pinfo']['date']);
//					foreach($begintime as $b_val){
//						$begin_hour = intval(date('H',strtotime($b_val)));
//
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
//		}
//		if(empty($hotprogram)){
//			return false;
//		}
//
//		ksort($hotprogram);
//		foreach($hotprogram as $hour => $value){
//			$mc_key = sprintf(MC_KEY_RADIO_HOT_PROGRAM_DAY_PID,$hour);
//			$mc_res = $this->setCacheData($mc_key,$value,MC_TIME_RADIO_HOT_PROGRAM_DAY_HOUR);
//			if ($mc_res === false){
//				$errinfo[] = $hour."点";
//			}
//		}
//
//		if(!empty($errinfo)){
//			$str = implode("|",$errinfo);
//			return "更新失败信息：".$str;
//		}
//
//		return true;
//	}

	/*
	 * 根据用户id获取其主持的节目信息
	*/
	public function getProgramByUid($uid){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_UID_DJ,$uid);
		$result = $this->getCacheData($mc_key);	
		return $result;
	}
	
	/**
	 * 初始化当前可用数据库句柄，避免重复连接数据库资源
	 * @return resource $db 数据库句柄 
	 */
	private function initDb(){
		if (self::$db==null){
			self::$db = $this->_connectDb();
		}
		return self::$db;
	}
}
