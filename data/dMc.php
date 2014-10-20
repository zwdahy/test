<?php
/**
 * mc 初始化和操作类
 *
 *
 * @package
 * @author 牛乐 <niule@staff.sina.com.cn>
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */

class dMc extends data {
	
	const LIVE_MC = 'srv.sapps.main';
	
	public function __construct()
	{
		$this->mcObj = $this->connectMc(self::LIVE_MC);		
	}
	
	public function set($key, $value, $expire = 3600) {
		return $this->mcObj->set ( $key, $value,$expire );
	}
	
	public function get($key) {
		return $this->mcObj->get ( $key);
	}
	
	public function replace($key, $value, $expire = 3600) {
		return $this->mcObj->replace ( $key, $value, $expire );
	}
	
	public function delete($key) {
		return $this->mcObj->delete ( $key );
	}
	
	public function setMulti($items,$expire) {
		return $this->mcObj->setMulti ( $items, $expire);
	}
	
	public function getMulti($items) {
		return $this->mcObj->getMulti ( $items);
	}
	
	public function increment( $key, $offset=1 ){
		return $this->mcObj->increment ( $key, $offset );
	}
	
	public function decrement( $key, $offset=1 ){
		return $this->mcObj->decrement ( $key, $offset );
	}
	
	/**
	 * 数据分块缓存,将$data数组按照$size分块,保存到多个缓存,分片索引保存到缓存主key
	 * @param	array 	$data			缓存数据
	 * @param	string	$key			缓存主key
	 * @param	int		$size			分块数据大小
	 * @param	int		$time			缓存时间
	 * @param	bool	$preserve_key	分块数据,数组索引(false 不保存,索引自动转换为数字, true保存) 详见array_chunk参数3
	 * @return true or false
	 */
	public function setChunk(array $data = array(), $key = '', $size = 1000, $time = 600, $preserve_key = true){
		//验参
		if(count($data) == 0 || $key == '') return false;
		
		//数据分块
		$chunk = array_chunk($data,$size,$preserve_key);
		
		$chunk_key = array();
		foreach ($chunk as $k=>$v) {
			//分块缓存
			$chunk_key[$k] = $key."_chunk_".$k;
			if ($this->set($chunk_key[$k],$v) != true) {
				return false;
			}
		}
		
		//缓存分块索引
		return $this->set($key,$chunk_key);
	}
	
	/**
	 * 获取分块缓存 ,将原先分块的缓存合并为原始数组
	 * @param 	string	$key	缓存主键
	 * @return	array	$data	分块合并后的缓存
	 */
	public function getChunk($key = ''){
		if($key == '') return false;
		$chunk_key = $this->get($key);
		$data = array();
		foreach ($chunk_key as $k=>$v) {
			$data = array_merge ($data,$this->get($v));
		}
		return $data;
	}
		
	/**
	 * 根据key值,获取缓存数据
	 */
	public function getCache($key){
		//数据未过期,返回旧数据
		$cache = $this->get($key);
		if($cache['exp_time'] >= time())
			return $cache['mc_data'];
		
		//锁定查询,返回旧数据
		if( $this->is_lock($key) === true)
			return $cache['mc_data'];
			
		return false;
	}
	
	/**
	 * 根据key值设置缓存,默认所有缓存时间均为一天
	 * @param $key				缓存key值
	 * @param $data				数据
	 * @param $exp_time			过期时间
	 * @return bool
	 */
	public function setCache($key, $data = array(), $exp_time = 120){
		$cache = array(
			'mc_data'  => $data,
			'exp_time' => time() + $exp_time,
		);
		
		//检查是否有缓存数据,没有则set,有则replace
		if($this->get($key) === false)
			return $this->set($key, $cache, 86400);
		else	
			return $this->replace($key, $cache, 86400);
	}
	
	/**
	 * 设置锁定,操作进行中
	 * @param $key	缓存key值
	 * @return bool 
	 */
	public function lock($key){
		return $this->setCache($key . MC_KEY_PREFIX_LOCK, true);
	}
	
	/**
	 * 设置解锁,操作结束
	 * @param $key	缓存key值
	 * @return bool
	 */
	public function unlock($key){
		return $this->setCache($key . MC_KEY_PREFIX_LOCK, false);
	}
	

	/**
	 * 根据缓存key查询是否锁定
	 * @param $key	缓存key值
	 * @return bool
 	 */
	public function is_lock($key){
		$lock = $this->get($key . MC_KEY_PREFIX_LOCK);
		if($lock['mc_data'] === true)
			return true;
		return false;
	}
	
}

?>