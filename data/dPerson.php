<?php
/**
 * 用户数据层
 *
 * @date 2010-08-30
 * @package
 * @author 刘勇刚<yonggang@staff.sina.com.cn>
 * @copyright (c) 2010, 新浪网 MiniBlog All rights reserved.
 */
include_once SERVER_ROOT."config/radioconf.php";
include_once SERVER_ROOT."tools/check/Check.php";
require_once(SERVER_ROOT.'dagger/libs/extern.php');
class dPerson extends data{
	
	/**
	 * 根据uid取得用户的信息
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							’uid'   => $uid // 当前登录uid
							'fuid'    =>  对方用户UID
							'detail'  =>  是否获取详细资料  
							            默认为0，不获取；1为获取详细资料
							'size'    =>  头像尺寸,30/50/120
							)
	  * @return array(	
		   			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 							用户详细资料
		 							)
	 			)
	 */
	public function getUserInfo($args) {
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$api = clsFactory::create('libs/api', 'InternalAPI');
		return $api->getUserInfoDetail($args);
	}
	
	/**
	 * 添加关注    
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuid'  => //增加关注的用户UID，多个UID之间用逗号间隔
							)
	  * @return true or false;
	 */
	public function addAttention($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
                BaseModelCommon::debug($args,'args');

                $weiboClient = new BaseModelWeiboClient(RADIO_SOURCE_APP_KEY);

                $result = $weiboClient->follow_by_id($args['fuid'][0]);
                //$result = $api->addAttention($args);
                BaseModelCommon::debug($result,'result');
		//return $api->addAttention($args);
		return $result;
	}
	
	// 废弃方法，没有调用。
	public function newAddAttention($getdata,$postdata){
        $url = "http://i.t.sina.com.cn/attention/addattention.php?uid={$getdata['uid']}&cip={$getdata['cip']}&appid={$getdata['appid']}&location={$getdata['location']}&refer_sort={$getdata['refer_sort']}&refer_flag={$getdata['refer_flag']}";
        $result = $this->requestPost(1, $url, $postdata);
        return json_decode($result,true);
	}
	/** 废弃方法
	 * 取得用户的关注关系(批量)
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuids'  => //要取得关系的用户uid,批量用逗号分隔
							)
	  * @return array(
	  * 			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 							'uid' => array{"relation"=>1} //1为以关注，0为未关注，4为黑名单
		 							)
	 			)
	 */
	public function getRelation($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getRelation($args);
	}


	
	
	/**
	 * 获取用户关注关系  此方法ms没效果
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * @param $args参数数组包含如下参数项
	 * 
	 * uid:用户uid(POST方式，必选)
     * fuids:被检察用户uid(POST方式，必选，支持批量，以逗号隔开) 
	 * @return array(2) {["one2many"]=>array(1) {[1821046267]=> bool(true)}
     *                   ["many2one"]=>array(1) {[1821046267]=>bool(true)}}
	 */
	public function newGetUserRelation($args){
		if(empty($args) || !is_array($args)){
			return false;
		}
		//$api = clsFactory::create('libs/api', 'InternalAPI');
		//$args['cip'] = Check::getIp();
		//$args['appid'] = RADIO_SOURCE_APP_ID;

                $weiboClient = new BaseModelWeiboClient(RADIO_SOURCE_APP_KEY);
                //$result = $weiboClient->is_followed_by_id($args['fuids'],$args['uid']);
                $result = $weiboClient->exists_batch_internal($args['fuids'],$args['uid']);
                
                $output = array();
                if(count($result)>0){
                    foreach($result as $key=>$item){
                        $output['one2many'][$item['id']] = TRUE;  
                    }
                        $output['many2one'] = FALSE;
                }
                else{
                    $output['one2many'] = FALSE;
                    $output['many2one'] = FALSE;
                }
               // BaseModelCommon::debug($output);
                return $output;

                
		//return $api->checkAttRelation($args);
	}
	
	/**
	 * 检查用户是否绑定手机
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
							'uid'   => //当前登录用户uid
							'fuid'  => //要查询的用户uid
							)
	  * @return array(
	  * 			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 							'code' => 返回码M06001 待绑定,含手机号，M06002 成功,含手机号，M06000 手机未绑定
		 							'data' => array(
		 											 'mobile'   =>  手机号，
		 											 'isnotice'   =>  是否提醒
		 											)
		 							)
	 			)
	 */
	public function checkUidBind($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->checkUidBind($args);
	}
	
	/**
	 * getIconvers
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-09-15
	 * 
	 * @param 	$args = array(
							'uid'   => //当前用户uid
							'fuids'  => //要查询的用户的UID，支持批量
							)
	  * @return array(
	  * 			'errno'  => 错误编码,
					'errmsg' => 错误信息,
					'result' => array(
		 								［uid］=> 头像版本
		 							)
	 			)
	 */
	public function getIconvers($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getIconvers($args);
	}	
	
	/**
	 * 批量获取用户信息，调用开放平台的接口
	 * 
	 */
	public function getUsersByShowBatch($uids,$trim_status=1,$has_extend=0,$simplify=0,$is_encode=0){
		$url = "http://api.weibo.com/2/users/show_batch.json?source=".RADIO_SOURCE_APP_KEY."&uids={$uids}&trim_status={$trim_status}&has_extend={$has_extend}&simplify={$simplify}&is_encode={$is_encode}";
        $result = $this->requestGet($url);
        return json_decode($result,true);
	}
	
	/**
	 * 取得用户的关注关系(批量) 此处注意 当需要判断的用户过多时返回值会不一样！！！
	 * @author wenda<wenda@staff.sina.com.cn>
	 * @date   2014/4/26
	 * 
	 * @param $uid 某一用户a
	 * @param $uids 判断a是否关注的那波人 array
	 *
	 * @return array(
	 * 				
	 *
	 *
	 *
	 *				）				
	 */
	public function getRelation2($uid,$uids) {
		if(empty($uid)||empty($uids)||!is_array($uids)){
			return false;
		}
		//考虑到人过多情况进行分批次调用接口每次判断500人
		$length=500;
		$count=count($uids);
		if($count<=$length){
			$str=implode(",",$uids);
			$url = "http://i2.api.weibo.com/2/friendships/exists_batch_internal.json?source=".RADIO_SOURCE_APP_KEY."&uid={$uid}&uids={$str}";
			$result = json_decode($this->requestGet($url),true);
        }else{
			$tmp=array();
			for($i=0,$k=1;$i<$count;$i++,$k++){
				$tmp[]=$uids[$i];
				if($k>=$count){
					$str=implode(',',$tmp);
					$url = "http://i2.api.weibo.com/2/friendships/exists_batch_internal.json?source=".RADIO_SOURCE_APP_KEY."&uid={$uid}&uids={$str}";
					$res= array_values(json_decode($this->requestGet($url),true));
					$result[]=$res;
					unset($res);
					$k=1;
					$tmp=array();
				}
			}
		}
		if(!empty($result['error_code'])){
			return FALSE;
		}else{
			return array(
				'error_code'	=>1,
				'result'		=>$result
				);
		}
	}
	
}
?>
