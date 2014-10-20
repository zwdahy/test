<?php
/**
 *
 * 电台节目分类的data层   包含关联表 radio_program_type_map
 *
 * @package
 * @author runxi<runxi@staff.sina.com.cn>
 * @copyright(c) Fri Dec 20 10:38:24 CST 2013
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
class dRadioProgramType extends dRadio{
	private static $db = null;//数据库句柄
	public $table_field = '`id`,`program_type`,`sort`';
	public $table_name = "radio_program_type";
	
	/**
	 * 根据电台的节目id获取节目分类
	 * @param int $program_id		电台节目id
     * 暂不支持 @param Array $program_id		电台节目ids  不支持传入数组
	 */
	public function getRadioProgramType($program_id,$fromdb=false){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE,$program_id);
		$program_types = $this->getCacheData($mc_key);
		//$fromdb=true;
        if(false === $program_types||$fromdb){
            $sql = 'SELECT a.id,b.program_id,a.program_type,a.sort FROM `'.$this->table_name.'` a JOIN `radio_program_type_map` b on (a.id = b.program_type_id) WHERE b.program_id IN (:program_id)';
            $program_types = $this->queryData($sql, array(':program_id' => $program_id));
            if(false !== $program_types){
                $this->setCacheData($mc_key,$program_types,MC_TIME_RADIO_PROGRAM);
            }
        }
        return $this->returnFormat(1,$program_types);
	}

	/**
	 * 根据电台的节目id获取节目分类
	 * @param int $program_id		电台节目id
     * 暂不支持 @param Array $program_id		电台节目ids  支持传入数组(*) wenda@ 新增根据program_id 批量获取 基于目前节目分类少 查不到 不去单独查 后期节目分类全了以后 此处需要优化
	 */
	public function getRadioProgramType2($program_ids,$fromdb=false){
		if(!is_array($program_ids) || empty($program_ids)){
			return $this->returnFormat(-4);
		}
		$keys = array();
		//构造key数组
		foreach($program_ids as &$v){
			$keys[] = sprintf(MC_KEY_RADIO_PROGRAM_TYPE,$v);
		}
		unset($v);
		$program_types = $this->getMultiCacheData($keys);
        if(false === $program_types||$fromdb){
			foreach($program_ids as &$v){
				$this->getRadioProgramType($v,$fromdb);
			}
			unset($v);
        }
        return $this->returnFormat(1,$program_types);
	}

    public function getRadioProgramTypeList(){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, __FUNCTION__);
		$typeList = $this->getCacheData($mc_key);
		//$typeList = false;
        if( false === $typeList){
            $sqlArgs = $this->_makeSelect($this->table_name, $this->table_field, array(), array('order'=>'ASC', 'field'=>'sort'));
            $typeList = $this->queryData($sqlArgs['sql']);
            if(false !== $typeList){
                $this->setCacheData($mc_key,$typeList,MC_TIME_RADIO_PROGRAM);
            }
        }
        return $this->returnFormat(1,$typeList);
    }

	/**
	 * 根据节目id 和节目分类id 删除节目的一个分类
	 * @param int $program_id		电台节目id
     * @param int $program_type_id  电台节目类型id 
	 */
    public function delRadioProgramTypeMap($program_id, $program_type_id){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE,$program_id);
        $sql =  'DELETE FROM `radio_program_type_map`  where `program_type_id`=:program_type_id AND `program_id` = :program_id';
        $this->delCacheData($mc_key);
        return $this->operateData($sql, array(':program_type_id' => $program_type_id, ':program_id'=>$program_id ));
    }

	/**
	 * 插入节目的对应分类
	 * @param Array array('program_id'=>$program_id,'program_type_id'=>$program_type_id)		电台节目id,电台节目类型id
	 */
    public function insertRadioProgramTypeMap($args){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, $args['program_id']);
        $sqlArgs = $this->_makeInsert('radio_program_type_map', $args);
        $r = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        $errorno = 1;
        if( false === $r){
            $errorno = -1;
        }
        $this->delCacheData($mc_key);
        return $this->returnFormat(1, $r);
    }

    public function delRadioProgramTypeMapByProgramId($program_id){
        $sqlArgs = $this->_makeDelete('radio_program_type_map', array('program_id' => $program_id ));
        $r = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
        $errorno = 1;
        if( false === $r){
            $errorno = -1;
        }
        return $this->returnFormat($errorno, $r);
    }

    public function delRadioProgramTypeMapByTypeId($type_id){
        $sqlArgs = $this->_makeDelete('radio_program_type_map', array('program_type_id' => $type_id));
        $r = $this->operateData($sqlArgs['sql'],$sqlArgs['data']);
        $errorno = 1;
        if( false === $r){
            $errorno = -1;
        }
        return $this->returnFormat($errorno, $r);
    }

    /**
     *新增一个节目分类名称
     *@param string $program_type  节目分类名称
     */
    public function insertRadioProgramType($program_type){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, 'getRadioProgramTypeList');
        $sqlArgs = $this->_makeInsert($this->table_name, array('program_type' => $program_type));
        $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        $errorno = 1;
        if(false === $result){
            $errorno = -1;
        }
        $this->delCacheData($mc_key);
        return $this->returnFormat($errorno, $result);
    }


    /**
     *更改一个节目分类
     *@param int $program_type_id  节目分类id
     *@param string $program_type  节目分类名称
     */
    public function updateRadioProgramType($program_type_id, $program_type){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, 'getRadioProgramTypeList');
        $sqlArgs = $this->_makeUpdate($this->table_name, array('program_type'=>$program_type), array('id'=>$program_type_id));
        $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        $errorno = 1;
        if(false === $result){
            $errorno = -1;
        }
        $this->delCacheData($mc_key);
        return $this->returnFormat($errorno, $result);
    }
    /**
     *更改一个节目分类排序
     *@param int $program_type_id  节目分类id
     *@param int $sort 节目分类排序
     */
    public function updateRadioProgramTypeSort($program_type_id, $sort){
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, 'getRadioProgramTypeList');
        $sqlArgs = $this->_makeUpdate($this->table_name, array('sort'=>$sort), array('id'=>$program_type_id));
        $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        $errorno = 1;
        if(false === $result){
            $errorno = -1;
        }
        $this->delCacheData($mc_key);
        return $this->returnFormat($errorno, $result);
    }


    /**
     *删除一个分类
     *@param int $program_type_id  节目分类id
     */
    public function delRadioProgramType($program_type_id){
        $errorno = 1;
		$mc_key = sprintf(MC_KEY_RADIO_PROGRAM_TYPE, 'getRadioProgramTypeList');
        $sqlArgs = $this->_makeDelete($this->table_name, array('id' => $program_type_id ));
        $result = $this->operateData($sqlArgs['sql'], $sqlArgs['data']);
        if(false === $result){
            $errorno = -1;
        }
        $r = $this->delRadioProgramTypeMapByTypeId($program_type_id);//删除这个分类的所有关联 (节目与分类的关联)
        if($r['errorno'] != 1){
            $errorno =  $r['errorno'];
        }
        $this->delCacheData($mc_key);
        return $this->returnFormat($errorno, $result);
    }

}
