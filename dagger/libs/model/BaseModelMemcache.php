<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * MC基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
*/

class BaseModelMemcache {

    /**
     * 主动刷新时间key后缀
     * @var string
     */
    const CACHE_TIME_CTL = '_@t';

    /**
     * 锁key后缀
     * @var string
     */
    const CACHE_LOCK_CTL = '_@l';

    
    /**
     * MC连接池
     * @var array
     */
    static private $memcache = array();

    /**
     * 当前MC链接
     * @var resource
     */
    private $mc;

    /**
     * 构造函数
     * @params string $mcName MC名称
     * @params array $mcConfig eg:array('servers'=>'192.168.1.1:7600 192.168.1.2:7700');
     */
    public function __construct($mcName = '', $servers = '') {
        empty($mcName) && $mcName = MC_DEFAULT;
        $servers = empty($servers) ? constant(strtoupper("MC_{$mcName}_SERVERS")) : $servers;
        $mcKey = md5($servers);
        if (self::$memcache[$mcKey] instanceof Memcache) {
            $this->mc = self::$memcache[$mcKey];
        } else {
            self::$memcache[$mcKey] = new Memcache();
            $serverArr = explode (' ', $servers);
            foreach ($serverArr as $v) {
                list($server, $port) = explode(':', $v);
                self::$memcache[$mcKey]->addServer($server, $port);
            }
            $this->mc = self::$memcache[$mcKey];
            defined("DAGGER_DEBUG") && BaseModelCommon::debug($servers, 'mc_connect');
        }
        if(!$this->checkConnection()){
            BaseModelLog::sendLog(50, "memcache服务器: {$servers} 无法响应");
        }
    }
    
    /**
     * 设置缓存
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function set($key, $value, $time=0) {
        $key = MC_KEY_PREFIX . $key;
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($value, "mc_set({$key}),ttl({$time})");
        $this->mc->set($key.self::CACHE_TIME_CTL, 1, 0, $time);
        $ret = $this->mc->set($key, $value, 0, $time + 86400);
        $this->releaseLock($key);
        return $ret;
    }

    /**
     * 增加缓存
     * @param string $key 缓存键
     * @param mixed $value 缓存值
     * @param int $time 缓存时间
     * @retrun bool
     */
    public function add($key, $value, $time=0) {
        $key = MC_KEY_PREFIX . $key;
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($value, "mc_add({$key}),ttl({$time})");
        if(!$this->mc->add($key.self::CACHE_TIME_CTL, 1, 0, $time)) {
            return false;
        }
        $ret = $this->mc->set($key, $value, 0, $time + 86400);
        $this->releaseLock($key);
        return $ret;
    }

    /**
     * 自增
     * @param string $key 缓存键
     * @param int $incre 自增值
     * @return float
     */
    public function increment($key, $incre=1){
        $key = MC_KEY_PREFIX . $key;
        $t = $this->mc->increment($key, $incre);
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($t, "mc_increment({$key})");
        return $t;
    }

    /**
     * 自减
     * @param string $key 缓存键
     * @param int $incre 自减值
     * @return float
     */
    public function decrement($key, $incre=1){
        $key = MC_KEY_PREFIX . $key;
        $t = $this->mc->decrement($key, $incre);
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($t, "mc_decrement({$key})");
        return $t;
    }

    /**
     * 获取缓存
     * @param string $key 缓存键
     * @param int $lockTime  缓存锁失效时间
     * @return mixed
     */
    public function get($key, $lockTime=3) {
        if(!$this->checkConnection()){
            return false;
        }
        $key = MC_KEY_PREFIX . $key;
        $outdated = $this->mc->get($key.self::CACHE_TIME_CTL);
        $data = $this->mc->get($key);
        if(($data === false) || ($outdated === false) || ($_GET['_flush_cache'] == 1)){
            if($this->getLock($key, $lockTime)){
                defined("DAGGER_DEBUG") && BaseModelCommon::debug(false, "mc_get_not_lock({$key})");
                return false;
            }
            $attempt = 0;
            do{
                $data = $this->mc->get($key);
                usleep(100000);
                if(++$attempt >= 4){
                    break;
                }
            }while($data === false);
        }
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($data, "mc_get({$key})");
        return $data;
    }

    /**
     * 删除缓存
     * @param string $key 缓存键
     * @return bool 
     */
    public function delete($key) {
        $key = MC_KEY_PREFIX . $key;
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($key, 'mc_delete');
        //查看要被delete的信息是否正确
        $data = $this->mc->get($key);
        return $this->mc->delete($key);
    }

    /**
     * 删除全部集群缓存
     * @param string $key 缓存键
     * @return bool
     */
    public static function deleteAll($key) {
        $key = MC_KEY_PREFIX . $key;
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($key, 'mc_delete_all');
        $searchMcArr = explode(' ', SEARCH_MC_ARR);
        foreach($searchMcArr as $searchMc) {
            $mc = new BaseModelMemcache($searchMc);
            $mc->delete($key);
        }
    }

    /**
     * 对资源加锁
     * @param string $key 缓存锁键
     * @param int $lockTime 缓存锁失效时间
     */
    public function getLock($key, $lockTime=3){
        defined("DAGGER_DEBUG") && BaseModelCommon::debug($lockTime, 'mc_lock_time');
        return $this->mc->add($key.self::CACHE_LOCK_CTL, 1, false, $lockTime);
    }

    /**
     * 释放资源锁
     * @param string $key 缓存锁键
     */
    public function releaseLock($key){
        $this->mc->delete($key.self::CACHE_LOCK_CTL);
    }

    /**
     * 检测memcache是否正常运行
     */
    private function checkConnection(){
        if($this->mc->getVersion() !== false){
            return true;
        }
        return false;
    }
}
