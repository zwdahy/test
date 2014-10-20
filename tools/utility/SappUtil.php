<?php
/**
 * 各类公用工具方法
 */
class SappUtil {
	
	/**
	 * 写日志
	 * @param string	$project	项目标识 直播 : live 访谈 : talk 电台 : radio
	 * @param array 	$log 		日志数组,array('数据错误,名称不能为空', 'array('name'=>''),....);
	 * @param string	$filename	日志文件名
	 */
	public static function log($project, $log, $filename) {
		
		//序列化二维数组写入日志,框架不支持二维
		foreach($log as $k=>$v){
			$log[$k] = is_array($v) ? serialize($v) : $v;
		}
		
		//实例化,写日志
		$objLog = clsFactory::create ( 'framework/tools/log/', 'ftLogs', 'service' );
		$objLog->switchs ( 1 ); //1 开    0 关闭
		$objLog->write ( $project, $log, $filename );
	}
}
?>