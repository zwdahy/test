<?php
/**
 * 
 * 电台公告信息的data层
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
class dRadioNotice extends dRadio{
	public $table_field = 'sort,notice_content,notice_start_time,notice_end_time,week_day,upuid,uptime';
	
	
	/**
	 * 获取电台公告信息
	 */
	public function getNoticeFromDB(){
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->_radioNotice, $this->table_field, array(), array());
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
	 * 插入电台公告信息
	 * @param array $args
	 */
	public function addRadioNotice($args){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		
		$sqlArgs = $this->_makeInsert($this->_radioNotice, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}		
		// 更新MC
		$this->updateNoticeMc();
		return $this->returnFormat(1);
	}
	
	/**
	 * 获取电台公告信息
	 */
	public function getRadioNotice($flag = false){
		$key = MC_KEY_RADIO_NOTICE;
		$notice = $this->getCacheData($key);
		$common_notice_key =md5("dradionotice.getradionotice");
		if($notice == false || $flag == true){
			$notice = $this->getNoticeFromDB();
			if ($notice && ($notice['errorno']!='RADIO_D_DB_00001')){
				$this->setCacheData($common_notice_key, $notice,86400);//种下一天缓存
			}
			$this->setCacheData($key,$notice,MC_TIME_RADIO_NOTICE);
		}
		if ($notice && ($notice['errorno']=='RADIO_D_DB_00001')){
			$old_notice = $notice;
			$notice = $this->getCacheData($common_notice_key);
			if (!$notice){
				$notice = $old_notice;
			}
		}
		return $notice;
	}				
	
	/**
	 * 更新公告信息的缓存
	 */
	public function updateNoticeMc(){
		$key = MC_KEY_RADIO_NOTICE;
		$notice = $this->getNoticeFromDB();
		return $this->setCacheData($key,$notice,MC_TIME_RADIO_NOTICE);
	}

	/**
	 * 删除电台的公告信息
	 */
	public function delRadioNotice(){		
		$sql .= "DELETE FROM ".$this->_radioNotice;
		$db_res = $this->_dbWriteBySql($sql);
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$key = MC_KEY_RADIO_NOTICE;
		$this->delCacheData($key);
		return $this->returnFormat(1);
	}
	
}
