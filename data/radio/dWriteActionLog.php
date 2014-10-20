<?php
/**
 * 
 * 电台的行为日志data层
 * 
 * @package 
 * @author 张倚弛6328<yichi@staff.sina.com.cn>
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
class dWriteActionLog extends dRadio {
	/**
	 * 
	 * 用户行为日志
	 * @param array $args 
	 * $args = array(
	 *		'typeid'   => $this->para['typeid'],
	 *		'radioid'  => $this->para['radioid'],
	 *		'from'     => $this->para['from'],
	 *		'mblogid'  => $this->para['mblogid'],
	 *		'optime'   => $strTime, 
	 *		'serverip' => $strServiceIp,
	 *		'clientip' => $strRemoteIp,
	 *		'opuid'    => $strUid
	 *	);
	 */
	public function writeUserActionLog($args){
		//'/data1/www/applogs/service.t.sina.com.cn/radio/%s'
		$strDir = sprintf(USR_ACTION_LOGADDR, 'user_action_log');
		//添加日期目录方便动态平台的同步和删除 by chengliang1@staff.sina.com.cn 2011.5.3
		$strDir .= "/".date("Y.m.d");
		//end
		$result1 = $this->UserActionLog($args, 'radio_useraction_alog.log', $strDir);
		/*
		//经检查,第二个用户日志目录不存在，自从2011年8月就无效，故废弃
		$Dir = sprintf(USR_ACTION_LOGADDR_NEW,date("Y.m.d"));
		$result2 = $this->UserActionLog($args,'radio.weibo.com_user_action.log',$Dir);
		return $result2;
		*/
		return $result1;
	}
	
	/**
	 * 
	 * 用户行为日志
	 * @param array $args 日志所需参数
	 * @param string $strFileName 日志文件名
	 * @param string $dir 日志存放路径
	 */
	private function UserActionLog($args, $strFileName, $dir){
		if (!is_dir($dir))
			mkDirs($dir);	//创建日志文件存放目录
		if (empty($args)) return $this->returnFormat('RADIO_00001');//内容为空返回false
		$strArgs = implode("\t", $args)."\r\n";
		$handel = fopen($dir . '/' . $strFileName, 'a+');
		if(!$handel){
			return $this->returnFormat('-9','打开日志文件失败！');
		}		
		$puts = fputs($handel,$strArgs);
		if($puts == false){
			return $this->returnFormat('-9','写入日志文件失败！');
		}
		fclose($handel);	
		return $this->returnFormat(1);
	}
	
	
}
?>