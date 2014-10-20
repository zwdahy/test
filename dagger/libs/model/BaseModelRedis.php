<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * @abstract        Redis操作基类
 * @author          shuoshi <shuoshi@staff.sina.com.cn>
 * @time            2011/12/27 15:23
 * @version         Id: 1.0
 */

class BaseModelRedis{

    /**
     * @var 服务器定位符
     */
    private $link;

    /**
     * @var 资源描述符数组
     */
    private static $instances = array();

    /**
     * 构造函数
     * @param string $host redis服务器地址
     * @param string $port redis服务器端口
     */
    public function __construct($host, $port){
        $this->link = "{$host}:{$port}";
        self::connect($this->link);
    }

    /**
     * 连接redis服务器
     * @param string $link redis服务器标识
     */
    private static function connect($link){
        if(!(self::$instances[$link] instanceof Redis) || (self::$instances[$link]->ping() !== '+PONG')){
            self::$instances[$link] = new Redis();
            list($host, $port) = explode(':', $link);
            BaseModelCommon::debug("{$host}:{$port}", 'connect to redis');
            self::$instances[$link]->connect($host, $port);
            if(!self::$instances[$link]){
                throw new Exception('Redis fail to connect');
            }
        }
    }

    /**
     * 魔法__call
     * @param string $func Redis函数名
     * @param array $args Redis函数所带参数列表
     */
    public function __call($func, $args){
        try{
            self::connect($this->link);
            return call_user_func_array(array(self::$instances[$this->link], $func), $args);
        }catch(Exception $e){
            BaseModelMessage::showError($e->getMessage());
        }
    }
}
