<?php
/**
 * 
 * 电台新闻信息的data层
 * 
 * @package 
 * @author 张旭<zhangxu5@staff.sina.com.cn>
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
class dRadioNews extends dRadio{
	public $table_News = "radio_news";
	public $table_field = "`id`,`title`,`subtitle`,`pic_url`,`url`,`focus`,`sort`,`list`,`upuid`,`uptime`";
	CONST PAGESIZE=20;

	/**
	 * 获取首页新闻信息
	 */
	public function getNewsForIndex($fromdb = false){
		//从缓存中获取
		$mc_key = MC_KEY_RADIO_NEWS_INDEX;
		$news = $this->getCacheData($mc_key);
		if($news == false || $fromdb == true){
			$whereArgs = array('list'=>'0');
			$postfixArgs['field'] = 'sort';
			$postfixArgs['order'] =	'ASC';
			$result = $this->getNewsFromDB($whereArgs,$postfixArgs);
			$news = $result['result'];
			$mc_res = $this->setCacheData($mc_key, $news, MC_TIME_RADIO_NEWS_INDEX);
		}
		return $this->returnFormat(1,$news);
	}

	/**
	 * 获取分页新闻信息
	 */
	public function getNewsForPage($page,$fromdb = false){
		//从缓存中获取
		$mc_key = sprintf(MC_KEY_RADIO_NEWS_PAGE,$page);
		$news = $this->getCacheData($mc_key); 
		if($news == false || $fromdb == true){
			$whereArgs = array('list'=>'1');
			$postfixArgs['field'] = 'uptime';
			$postfixArgs['order'] =	'DESC';
			$postfixArgs['page'] = $page;
			$postfixArgs['pagesize'] = self::PAGESIZE;
			$result = $this->getNewsFromDB($whereArgs,$postfixArgs);
			$news = $result['result'];
			$mc_res = $this->setCacheData($mc_key, $news, MC_TIME_RADIO_NEWS_PAGE);
		}
		return $this->returnFormat(1,$news);
	}

	/**
	 * 获取分页新闻信息
	 */
	public function getNewsNum($fromdb = false){
		//从缓存中获取
		$mc_key = sprintf(MC_KEY_RADIO_NEWS_NUM);
		$news_count = $this->getCacheData($mc_key);  
		if($news_count == false || $fromdb == true){
			$sql_count = "SELECT COUNT(*) AS count FROM ".$this->table_News." WHERE `list`='1'";						
			$news_count = $this->_dbReadBySql($sql_count);	
			$mc_res = $this->setCacheData($mc_key, $news_count, MC_TIME_RADIO_NEWS_NUM);
		}
		return $this->returnFormat(1,$news_count);
	}

	/**
	 * 获取电台新闻信息
	 */
	public function getNewsFromDB($whereArgs,$postfixArgs){
		$db = $this->_connectDb();
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeSelect($this->table_News, $this->table_field, $whereArgs, $postfixArgs);
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
		return $this->returnFormat(1,$result);
	}
	
	/**
	 * 清除列表电台新闻信息缓存
	 * @param array $args
	 */ 
	public function delNewsListMemcache($page){
		$mc_key = sprintf(MC_KEY_RADIO_NEWS_PAGE,$page);
		$this->delCacheData($mc_key);  
	}
	/**
	 * 插入电台新闻信息
	 * @param array $args
	 */ 
	public function addRadioNews($args){
		$db = $this->_connectDb(1);
		if(false == $db) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		$sqlArgs = $this->_makeInsert($this->table_News, $args);
		$st = $db->prepare($sqlArgs['sql']);
		if(false == $st->execute($sqlArgs['data'])) {
			$this->writeRadioErrLog(array('执行SQL失败', 'SQL：' . $sqlArgs['sql'], '参数：' . implode('|', $sqlArgs['data']), '错误信息：' . implode('|', $st->errorInfo())), 'RADIO_ERR');
			return $this->returnFormat('RADIO_D_DB_00002');
		}	
		// 更新MC
		$news_count = $this->getNewsNum(true);
		$count = $news_count['result'][0][count];
		$pagesize = self::PAGESIZE;
		$pagecount = ceil($count/$pagesize);
		for($i=1;$i<=$pagecount;$i++){
			$this->delNewsListMemcache($i);
		}
		return $this->returnFormat(1);
	}
	
	/**
	 * 删除电台的新闻信息
	 */
	public function delRadioNews($data){	
		$field = $data['field'];
		$value = $data['value'];
		$sql .= "DELETE FROM ".$this->table_News." WHERE ".$field."=".$value;
		$db_res = $this->_dbWriteBySql($sql);
		if(false === $db_res) {
			return $this->returnFormat('RADIO_D_DB_00001');
		}
		// 更新MC
		$news_count = $this->getNewsNum(true);
		$count = $news_count['result'][0][count];
		$pagesize = self::PAGESIZE;
		$pagecount = ceil($count/$pagesize);
		for($i=1;$i<=$pagecount;$i++){
			$this->delNewsListMemcache($i);
		}
		return $this->returnFormat(1);
	}
	
}
