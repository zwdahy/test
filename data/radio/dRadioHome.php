<?php
include_once SERVER_ROOT."data/radio/dRadio.php";
class dRadioHome extends dRadio{
	public static $RADIO_PIC_INFO_FIELDS = 'pic_id,img_url,link_url,upuid,uptime';
	public static $RADIO_HOT_INFO_FIELDS = 'rid,sort,upuid,uptime';
	
	/**
	 * 添加统一推荐管理中的 轮播图片信息
	 * @param array $args rid link_url pic_url upuid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addPicInfo($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeInsert($this->_radioPicInfo, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$lastId = $db->lastInsertId();
		return $this->returnFormat(1, array('pic_id' => $lastId));
	}
	
	/**
	 * 获得轮播大图的信息
	 * @param array $whereArgs pic_id,img_url,link_url
	 * @param string $postfixArgs 排序以及分页
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getPicInfo($whereArgs = array(), $postfixArgs = array()) {
		if(!is_array($whereArgs) && !is_array($postfixArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb();
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->_radioPicInfo, '*', $whereArgs, $postfixArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$content = $st->fetchALL(PDO::FETCH_ASSOC);
		if(is_numeric($whereArgs['pic_id'])) {
			return $this->returnFormat(1, $content);
		} else {
			// 计算总数
			$sqlArgs = $this->_makeSelect($this->_radioPicInfo, 'COUNT(*) AS count', $whereArgs, array());
			$st = $db->prepare($sqlArgs['sql']);
			if(false === $st->execute($sqlArgs['data'])) {
				$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
				return $this->returnFormat('RADIO_D_DB_00003');
			}
			$count = $st->fetch(PDO::FETCH_ASSOC);
			return $this->returnFormat(1, array('count' => $count['count'], 'content' => $content));
		}
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
		//$djArgs = $this->getDj($whereArgs);		// 获取此条记录的DJ信息
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
		/*
		// 获取RID操作MC
		$didArgs = is_numeric($whereArgs['did']) ? $djArgs['result'] : $djArgs['result']['content'];
		foreach($didArgs as $v) {
			$this->updateDjMC($v['rid']);// 更新MC
		}
		*/
		return $this->returnFormat(1);
	}
	
		
	
	/**
	 * 添加统一推荐管理中的 热门电台信息
	 * @param array $args rid link_url pic_url upuid
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addHotRadio($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeInsert($this->_radioHotInfo, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$lastId = $db->lastInsertId();
		return $this->returnFormat(1, array('rid' => $lastId));
	}
	

	/**
	 * 获得推荐的热门电台信息
	 * @param array $whereArgs pic_id,img_url,link_url
	 * @param string $postfixArgs 排序以及分页
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getHotRadio($whereArgs = array(), $postfixArgs = array()) {
		if(!is_array($whereArgs) && !is_array($postfixArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb();
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->_radioHotInfo, '*', $whereArgs, $postfixArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$content = $st->fetchALL(PDO::FETCH_ASSOC);
		if(is_numeric($whereArgs['pic_id'])) {
			return $this->returnFormat(1, $content);
		} else {
			// 计算总数
			$sqlArgs = $this->_makeSelect($this->_radioPicInfo, 'COUNT(*) AS count', $whereArgs, array());
			$st = $db->prepare($sqlArgs['sql']);
			if(false === $st->execute($sqlArgs['data'])) {
				$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
				return $this->returnFormat('RADIO_D_DB_00003');
			}
			$count = $st->fetch(PDO::FETCH_ASSOC);
			return $this->returnFormat(1, array('count' => $count['count'], 'content' => $content));
		}
	}
		
	
	

	/**
	 * 删除推荐的热门电台信息
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function delHotRadio($whereArgs) {
		if(empty($whereArgs) || !is_array($whereArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		//$djArgs = $this->getDj($whereArgs);		// 获取此条记录的DJ信息
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeDelete($this->_radioHotInfo, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		/*
		// 获取RID操作MC
		$didArgs = is_numeric($whereArgs['did']) ? $djArgs['result'] : $djArgs['result']['content'];
		foreach($didArgs as $v) {
			$this->updateDjMC($v['rid']);// 更新MC
		}
		*/
		return $this->returnFormat(1);
	}
	
	/**
	 * 获得地区推荐电台
	 * @param array $whereArgs province_id,rolling_picture,publink,dj_info,right_picture,online
	 * @param string $postfixArgs 排序以及分页
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function getRadioProvince($whereArgs = array(), $postfixArgs = array()) {
		if(!is_array($whereArgs) && !is_array($postfixArgs)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb();
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->_radioProvinceInfo, '*', $whereArgs, $postfixArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$content = $st->fetchALL(PDO::FETCH_ASSOC);
		if(is_numeric($whereArgs['pic_id'])) {
			return $this->returnFormat(1, $content);
		} else {
			// 计算总数
			$sqlArgs = $this->_makeSelect($this->_radioProvinceInfo, 'COUNT(*) AS count', $whereArgs, array());
			$st = $db->prepare($sqlArgs['sql']);
			if(false === $st->execute($sqlArgs['data'])) {
				$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
				return $this->returnFormat('RADIO_D_DB_00003');
			}
			$count = $st->fetch(PDO::FETCH_ASSOC);
			return $this->returnFormat(1, array('count' => $count['count'], 'content' => $content));
		}
	}
		
			
	/**
	 * 添加地区首页的信息
	 * @param array $args province_id rolling_picture publink dj_info right_picture
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function addRadioProvince($args) {
		if(empty($args) || !is_array($args)) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeInsert($this->_radioProvinceInfo, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		$lastId = $db->lastInsertId();
		return $this->returnFormat(1, array('province_id' => $lastId));
	}
	
	/**
	 * 编辑设置地区首页的信息
	 * @param array $args province_id rolling_picture publink dj_info right_picture
	 * @param array $whereArgs 判断条件
	 * @return array array('errorno' => 1, 'result' => array())
	 */
	public function setRadioProvince($args, $whereArgs) {
		if(empty($args) || !is_array($args) || (!empty($whereArgs) && !is_array($whereArgs))) {
			return $this->returnFormat('RADIO_D_CK_00001');
		}
		$db = $this->_connectDb(1);
		if(false === $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeUpdate($this->_radioProvinceInfo, $args, $whereArgs);
		$st = $db->prepare($sqlArgs['sql']);
		if(false === $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}
		return $this->returnFormat(1,array('province_id' => $args['province_id']));
	}
	
	
	
	
	
}
?>