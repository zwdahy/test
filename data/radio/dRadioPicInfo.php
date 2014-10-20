<?php
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioPicInfo extends dRadio{
	public $table_field = 'pic_id,img_url,link_url,upuid,uptime';
	public $table_name = 'radio_pic_info';
	
	/**
	 * 添加统一推荐管理中的 轮播图片信息
	 * @param array $args rid link_url pic_url upuid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addPicInfo($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db_res = $this->dbInsert($args);	
		if($db_res == false){
			return $this->returnFormat('RADIO_D_DB_00001');
		}else{
			return $this->returnFormat(1);
		}
	}
	
	/**
	 * 获得轮播大图的信息
	 * @param array $whereArgs pic_id,img_url,link_url
	 * @param string $postfixArgs 排序以及分页
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getPicInfo($whereArgs = array(), $postfixArgs = array()) {
		$content = $this->dbRead($whereArgs,$postfixArgs);
		if(false === $content) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		return $this->returnFormat(1, array('content' => $content));
	}
	
	/**
	 *
	 * 获取首页左侧推荐图
	 */
	public function getLeftPic($fromdb = false){
		//从缓存中获取
		$key = MC_KEY_RADIO_LEFT_PIC;
		$leftPic = $this->getCacheData($key);
		//print_r($leftPic);exit;
		//$fromdb = true;
		if($leftPic['empty']==1 || $leftPic== false || $fromdb == true){
			//从数据库取数据
			$leftPic = false;
			$pic = $this->getPicInfo(array('pic_id'=>6));
			if(!empty($pic['result']['content'][0])){
				$leftPic['img'] = $pic['result']['content'][0]['img_url'];
				$leftPic['link'] = $pic['result']['content'][0]['link_url'];
			}else{
				$leftPic['empty'] = 1;
			}
			$this->setCacheData($key, $leftPic, MC_TIME_RADIO_LEFT_PIC);
		}
		return $this->returnFormat(1,$leftPic);
	}

	/**
	 * 删除轮播图片
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delPicInfo($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeDelete($this->_radioPicInfo, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		return $this->returnFormat(1);
	}
	
	/**
	 * 编辑统计数据信息
	 * @param array $args 
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function updatePicInfo($data, $where = array()) {
		if(empty($data) || !is_array($data)) {
			return $this->returnFormat(-4);
		}
		$db_res = $this->dbUpdate($data,$where);
		if($db_res == false){
			return $this->returnFormat(-1);
		}
		return $this->returnFormat(1);
	}

		
	/**
	 * 数据库SELECT数据库操作
	 * @param $where
	 * @param $order
	 */
	public function dbRead($where = array(), $order = array()){
		return $this->_dbRead($this->table_name,$this->table_field,$where,$order);
	}
	
	/**
	 * 数据库INSERT操作
	 * @param $data
	 * @return bool
	 */
	public function dbInsert($data){		
		return $this->_dbInsert($this->table_name,$data);
	}
	
	/**
	 * 数据库UPDATE操作
	 * @param array $set
	 * @param array $where
	 */
	public function dbUpdate($set,$where = array()){
		return $this->_dbUpdate($this->table_name,$set,$where);
	}
	
	
	
}
?>