<?php
/**
 * mc 公共初始化和操作类
 *
 *
 * @package
 * @author 高峰 gaofeng3@staff.sina.com.cn
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */

class dPMc extends data {
	
	private $mcObj = array(); //MC实例对象
	
	private $alias; //MC资源别名
	
	public function __construct($alias=MC_PUBLIC_ALIAS){
		$this->objLog = clsFactory::create('framework/tools/log/', 'ftLogs', 'service');
		$this->objLog->switchs(1); //1 开    0 关闭
		$this->alias = $alias;		
	}
	
	public function setAlias($alias){
		$this->alias = $alias;
	}
	
	/**
	 * 连接缓存服务器
	 * @return connectMc
	 */
	protected function _connectMC(){
		if(!$this->mcObj[$this->alias]){
            $this->mcObj[$this->alias] = $this->connectMc($this->alias);
			if($this->mcObj[$this->alias] == false){
				$this->log(array("connect mc fail", "args：alias=".$this->alias), 'DPMC');
				return false;
			}
        }
        return $this->mcObj[$this->alias];
	}
	
	public function set($key, $value, $expire = 3600){
		$mc = $this->_connectMC();
		if($mc == false){
			return false;
		}
		return $mc->set($key, $value, $expire);
	}
	
	public function get($key){
		$mc = $this->_connectMC();
		if($mc == false){
			return false;
		}
		return $mc->get($key);
	}

	public function delete($key){
		$mc = $this->_connectMC();
		if($mc == false){
			return false;
		}
		return $mc->delete($key);
	}
	
	/**
	 * 日志
	 * @param array $errorMes array('MySQL', 'MC', 'MCQ')
	 */
	protected function log($errorMes, $filename = ""){
		$this->objLog->write('sapps', $errorMes, $filename);
	}

}
?>