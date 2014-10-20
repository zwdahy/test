<?php
/**
 * mblog data
 *
 * @date 2010-08-30
 * @package
 * @author 刘勇刚<yonggang@staff.sina.com.cn>
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */
include_once SERVER_ROOT."config/radioconf.php";
require_once(SERVER_ROOT.'dagger/libs/extern.php');
class dMblog extends data{
	/**
	 * mc链接资源号定义 
	 */
	private $mc_res = CACHE_BASE;
	/**
	 * mc链接资源号设置
	 * @author gaofeng3
	 */
	public function setMcRes($res){
		$this->mc_res = $res;
	}

	/**
	 * 批量获取微博内容
	 * @date   2010-08-30
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'mid'  =>  微博id,批量用逗号隔开
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息
	 'count' => 结果数
	 'result' => array(
	 '微博id' => 微博内容,
	 ),
	 )
	 */
	public function getMblogContent($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getMblogContent($args);
	}

	/**
	 * 发表微博客
	 * @date   2010-08-30
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'content'  =>  微博内容
	 'appid'   => appid
	 ''pid     => 上传的图片
	 'ip'    =>  //ip地址
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息
	 'result' => array(
	 'mblogid' => 微博客id,
	 ),
	 )
	 */
	public function addMblog($args){
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$api->setTimeout(10);
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->newAddMblog($args);
	}

	/**
	 * 根据openapi批量获取评论和转发数
	 * @param array $ids
	 * @return array
	 * @author gaofeng3
	 */
	public function getRtAndCommentsCounts($ids){
		if(!is_array($ids)){
			return false;
		}
		$api = clsFactory::create('libs/api','RestAPI');
		$api->setAppKey(OPENAPI_APP_KEY);
		$args = array();
		$args['ids'] = $ids;
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->statuses_counts($args);
	}
	/**
	 * 格式化微博feed
	 * @param array $mblogList 微博列表
	 * @return array
	 */
	public function formatMblog($mblogList){
		//var_dump($mblogList);
		if(!is_array($mblogList)){
			return false;
		}

		if(empty($mblogList)){
			return $mblogList;
		}		
		
		foreach($mblogList as $k=>$v) {
			if(empty($v) || !isset($v['content'])){
				unset($mblogList[$k]);
				continue;
			}

			$args = array(
				'text'     => isset($v['content']["text"])?$v['content']["text"]:'',
				'at_array' => isset($v['content']['atUsers'])?$v['content']['atUsers']:array(),
				'time'     => isset($v['time'])?$v['time']:''
			);
			$textInfo = $this->formatText($args);
			
			$mblogList[$k]['content'] = array('text' => isset($textInfo['text'])?$textInfo['text']:'',
												'pic' => isset($v['content']['pic'])?$v['content']['pic']:array(),
												'atUsers' => isset($v['content']['atUsers'])?$v['content']['atUsers']:array(),
												'old_text' => isset($v['content']['text']) ? $v['content']['text']:'');													
			$mblogList[$k]['source_link'] = $this->getSourceLink($v);
			$mblogList[$k]['created_at'] = $textInfo['created_at'];
			if($v['rt'] && is_array($v['rt'])){
				$args['text'] = $v['rt']['rtreason'];
				$rtInfo = $this->formatText($args);
				$mblogList[$k]['rt']['old_rtreason'] = $v['rt']['rtreason'];
				$mblogList[$k]['rt']['rtreason'] = $rtInfo['text'];
			}
		}
		
		return $mblogList;
	}

	/**
	 * 格式化微博内容
	 * @date 2010-08-31
	 *
	 * @param $args = array(
	 * 						'text' = //微博內容,
	 * 						'time' = //发表时间,
	 * 						'at_array' => array('用户昵称' => //用户昵称);
	 *  					)
	 *  @return array(
	 *  				'text' => //格式化后的微博内容
	 *  				'created_time' => 格式化后的发表时间	
	 *  )
	 */
	public function formatText($args){
		$objBase62 = clsFactory::create ('libs/basic/tools', 'bBase62Parse' );
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$objTag = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeEmotion','service');//分析tag的类
		$objKeyWord = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeKeyWord','service');//分析##符号的类
		$objShortLink = clsFactory::create(CLASS_PATH.'tools/analyze/','TAnalyzeShortLink','service'); //解析短连接
		$objTimeFormat =  clsFactory::create(CLASS_PATH.'tools/formatter','TimeFormatter','service'); //创建日期工具对象

		$result = array();
		//解析时间
		$created_at = date('Y-m-d H:i', $args['time']);
		$result["created_at"] = $objTimeFormat->timeFormat($created_at);
		//解析表情
		$text = $args['text'];
		$text = $objTag->textToIcon($text);
		//解析sinaurl标签
		$text = $this->newAnalyseSinaUrl($text);
		//给短URL加超连接
		$text = $objShortLink->textToShortLink($text,true);
		//解析关键字
		$text = $objKeyWord->renderTag($text, true);

		//解析对应@连接地址
		if(!$args['at_array']){
			$args['at_array'] = $objAt->getAtUsername($text);
		}
			
		//解析对应@连接地址
		$objAt->atTOlink($text, $args['at_array'], true);

		$result['text'] = $text;
		return $result;
	}

	//仅格式化文本
	public function formatOnlyText($text){
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$objTag = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeEmotion','service');//分析tag的类
		$objKeyWord = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeKeyWord','service');//分析##符号的类
		$objShortLink = clsFactory::create(CLASS_PATH.'tools/analyze/','TAnalyzeShortLink','service'); //解析短连接
		$result = array();
		//解析时间
		$created_at = date('Y-m-d H:i', $args['time']);
		//解析表情
//		$text = $args['text'];
		$text = $objTag->textToIcon($text);
//		print_r($text);
//		exit;
		//解析sinaurl标签
		$text = $this->newAnalyseSinaUrl($text);
		//给短URL加超连接
		$text = $objShortLink->textToShortLink($text,true);
		//解析关键字
		$text = $objKeyWord->renderTag($text, true);

		//解析对应@连接地址
		if(!$args['at_array']){
			$args['at_array'] = $objAt->getAtUsername($text);
		}
			
		//解析对应@连接地址
		$objAt->atTOlink($text, $args['at_array'], true);

		$result= $text;
		return $result;
	}


	/**
	 * 获取缓存链接
	 * @author gaofeng3
	 */
	private function _connectMc() {
		$mc = $this->connectMc($this->mc_res);
		return $mc;
	}

	/**
	 * 删除微博客
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'mblogid' =>  微博客ID
	 'ip'  =>  客户端IP
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息
	 )
	 */
	public function delMblog($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->delMblog($args);
	}

	/**
	 * 转发微博客
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'mblogid'    =>  //微博客ID
	 'mbloguid'  =>  微博客所属UID
	 'reason'   => 转发理由
	 'aid'   => 活动id
	 'ip'    =>  //ip地址
	 'appid'   => 来源ID 可选，默认为6(来自WAP)
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 'mblogid' => //微博id
	 )
	 )
	 */
	public function repostMblog($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		$api->setTimeout(10);
		return $api->newtransmit($args);
	}

	/**
	 * 收藏微博客
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * i.t.sina.com.cn/wap/addfavmblog.php
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'mblogid'    =>  微博客ID
	 'ip'  =>  客户端IP
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 'favid' => //收藏ID(为-1时说明此话题已收藏),
	 )
	 )

        i.t接口下线, 由zihao1改造
	 */
	public function addFavMblog($args) {
                /*
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->addFavMblog($args);
                */
                $weiboClient = new BaseModelWeiboClient(RADIO_SOURCE_APP_KEY);
                $id = $weiboClient->queryid($args['mblogid'],1,0,0,1);

                return $weiboClient->add_to_favorites($id['id']);
	}


	public function addFavMblog2($args) {
		$url = API_FAVORITES_CREATE;
		$data = "source=".RADIO_SOURCE_APP_KEY."&id={$args['mblogid']}";
		$result = json_decode($this->requestPostWithCookie($url,$data,10));
        return $result;
	}

	

	/**
	 * 发表评论
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * i.t.sina.com.cn/wap/addcomment.php
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'srcuid'    =>  //资源所属者UID(微博客发表人ID)
	 'srcid'  =>  资源ID(微博客ID)
	 'content'   => 评论/回复内容
	 'isreply'    =>  0为评论微博客，1为回复评论
	 'restitle'  =>  微博内容
	 'rcmtid'   => 回复的评论ID 只有isreply为1是才需要此参数
	 'cmtuid'    => 回复的评论的作者UID 只有isreply为1是才需要此参数
	 'ip'    =>  //ip地址
	 'appid'   => 来源ID 可选，默认为6(来自WAP)
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 'cmtid' => 评论id
	 )
	 )
	 */
	public function addComment($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$api->setTimeout(10);
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->addComment($args);
	}
	/**
	 * 发表评论（返回错误码）
	 * @author 王超<wangchao@staff.sina.com.cn>
	 * @date   2010-11-04
	 *
	 * i.t.sina.com.cn/wap/addcomment.php
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'srcuid'    =>  //资源所属者UID(微博客发表人ID)
	 'srcid'  =>  资源ID(微博客ID)
	 'content'   => 评论/回复内容
	 'isreply'    =>  0为评论微博客，1为回复评论
	 'restitle'  =>  微博内容
	 'rcmtid'   => 回复的评论ID 只有isreply为1是才需要此参数
	 'cmtuid'    => 回复的评论的作者UID 只有isreply为1是才需要此参数
	 'ip'    =>  //ip地址
	 'appid'   => 来源ID 可选，默认为6(来自WAP)
	 )
	 * @return array(
	 'errCode'  => 错误编码,
	 'errMsg' => 错误信息,
	 'result' => array(
	 'cmtid' => 评论id
	 )
	 )
	 */
	public function addCommentReturnCode($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$api->setTimeout(10);
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->newaddComment($args);
	}

	/**
	 * 根据mid取得某条微博的评论
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'srcuid'    =>  //微博客发表人ID
	 'srcid'  =>  资源ID(微博客ID)
	 'page'   =>  页码，可选，默认为1
	 'pagesize'=> 页码大小，可选，默认为10
	 'sort'   =>  排序方式，必选，desc为按评论时间从新到旧，asc为按评论时间从旧到新
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 [0] => array(
	 [cmtid] => 评论ID,
	 [uid] => 评论发表人UID,
	 [content] => 评论内容,
	 [time] => 评论发表时间(时间戳),
	 [cmtsource] => 评论发布渠道(参见备注),
	 [srcid] => 资源ID,
	 [srcuid] => 资源所属者uid,     
	 [srctype] => 资源类型(0为微博客，1为评论),
	 [atUsers] => array(nick1, nick2, nick3), //内容中被识别出来的@用户昵称
	 )
	 )
	 )
	 */
	public function getArticleComment($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getArticleComment($args);
	}
	/**
	 * 取得微博评论数(批量)
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * i.t.sina.com.cn/wap/getcommentsnum.php
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'srcuid'    =>  //微博客发表人ID(批量时用半角逗号分隔，srcid与此一一对应分隔)
	 'srcid'  =>  资源ID(微博客ID)
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 'srcid' => 评论数,
	 )
	 )
	 */
	public function getCommentsNum($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getCommentsNum($args);
	}

	/**
	 * 批量获取评论数和转发数
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-31
	 *
	 * @param 	$args = array(
	 'mids'   => //微博mid,多个mid逗号分隔
	 'ip'  => //ip地址
	 )
	 * @return array(
	 * 			'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 [mid] => array(
	 [rtnum]=>转发数,
	 [cmtnum]=>评论数
	 ),
	 )
	 )
	 */
	public function getMblogCounts($args) {
		//通过MIDS生成KEY
		$key = "MC_MBLOG_CNT_".strtoupper(md5($args['mids']));
		$mc = clsFactory::create ( CLASS_PATH . 'data', 'dMc', 'service' );
		$result = $mc -> get($key);
		if(empty($result)){
			$api = clsFactory::create('libs/api', 'InternalAPI');
			$args['cip'] = Check::getIp();
			$args['appid'] = RADIO_SOURCE_APP_ID;
			$result = $api->getMblogCounts($args);
			if(!empty($result)){
				$mc -> set($key,$result,INTERFACE_SAFE_TIME);
			}
		}
		return $result;
	}

	/**
	 * 删除评论
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-24
	 *
	 * @param 	$args = array(
	 'uid'   => $uid // 当前登录uid
	 'cmtid' =>  评论ID
	 'cmtuid' => 评论发表人uid
	 'srcid'  =>  微博id
	 'ip'     =>  客户端ip地址
	 )
	 * @return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 )
	 */
	public function delComment($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->delComment($args);
	}



	/**
	 * 批量发表私信
	 * @date 2010-09-09
	 * @param  $args = array(
	 'fromuid'   => $uid  //当前登录uid
	 'touids'  => //对方uid,批量逗号分隔
	 'content'  => //内容
	 )
	 * $return boolean
	 */
	public function sendMessageMulti($args){
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->sendMessageMulti($args);
	}
	/**
	 * 发送通知
	 * $system_args 这个是基础服务需要的参数
	 * $method_args 这个是方法需要的参数
	 */
	public function sendNotice($system_args,$method_args){
		$obj = clsFactory::create ( 'libs/basic/model', 'bmNotice', 'service' );
		$obj->setPara('uid',$system_args['uid']);   //当前登录用户uid
		$obj->setPara('appid',$system_args['appid']);  //appid
		$obj->setPara('cip',$system_args['cip']);   //请求接口的ip
		$obj->setPara('appkey',$system_args['appkey']);//appkey
		$obj->setPara('userpwd',$system_args['userpwd']);
		$title = $method_args['title'];
		$content = $method_args['content'];
		$uids = $method_args['uids'];
		return $obj->send($title, $content, $uids);
	}
	/**
	 * 将长url地址变成短url地址
	 * @date 2010-09-25
	 * @param  $args = array(
	 'url'   => 长url
	 )
	 * $return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array('url' => '')
	 )
	 */
	public function long2short($args){
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->long2short($args);
	}

	/**
	 * 关键词搜索
	 *
	 * @date   2010-08-30
	 *
	 * @param 	$args = array(
	 'uid'   => //当前登录用户uid
	 'tag'  => //搜索关键词
	 )
	 * @return array(
	 * 			'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 [0] => 微博通用数据结构
	 )
	 )
	 */
	public function searchMblog($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->searchMblog($args);
	}

	/**
	 * 将62进制字符串转成10进制mid
	 * @param $baseMid   base62进制的mid
	 * @return $mid    10进制mid
	 */
	public function midDecode($baseMid){
		$base= clsFactory::create ('libs/basic/tools', 'bBase62Parse' );
		$mid = $base -> decode($baseMid);
		return $mid;
	}

	/**
	 * 将10进制mid转成62进制字符串
	 * @param $mid  10进制的mid
	 * @return $baseMid   62进制mid
	 */
	public function midEncode($mid){
		$base= clsFactory::create ('libs/basic/tools', 'bBase62Parse' );
		$baseMid = $base -> encode($mid);
		return $baseMid;
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 发表微博
	 * @param $args参数数组包含如下参数项
	 *
	 * uid:当前用户UID(POST方式，必选)
	 * content:微博客内容(POST方式，必选)
	 * pid:图片pid(POST方式，可选)
	 * piccontent:图片文件内容(base64编码)(POST方式，可选)
	 * picurl:图片文件(POST FILE方式，可选)
	 * @return boolean
	 */
	public function newAddMblog($args){
		if(empty($args) || !is_array($args)){
			return false;
		}
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->newAddMblog($args);
	}
	/**
	 * 批量获取用户信息
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * @param $args array(uids=>uid数组,size=>头像大小)
	 * @return array
	 */
	public function newGetUserInfos($args){
		if(empty($args) || !is_array($args)){
			return false;
		}
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->getUserInfos($args);
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 获取用户关注关系
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
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->checkAttRelation($args);
	}
	/**
	 * 解析<sina:link标签
	 * @param $text
	 * @return unknown_type
	 */
	public function newAnalyseSinaUrl($text){
		$out = array();
		$r = array();
		$s = array();
		$content = '';
		$patterns = preg_match_all("/\<\s*sina\s*\:\s*([a-zA-Z0-9]+)\s+([^\>]*)\/?\>/i", $text, $out);
		$fun=$out[1];
		$ora=$out[0];
		foreach($fun as $key => $value){
			if($value=='link'){

				if (!preg_match("/<sina\:link.*?src=\"http:\/\/(t.sina.com.cn|weibo.com)([\/a-zA-Z0-9])*/i",$ora[$key]))
				$urlsPrefix = SHORTURL_DOMAIN;

				$p2 = preg_match_all("/([a-zA-Z0-9_]+)\s*\=\s*[\'\"]([^\'\"]*)[\'\"]/", $ora[$key], $out2);
				$urls = $urlsPrefix . $out2[2][0];
				$text = str_replace($ora[$key], $urls, $text);
			}
		}
		return $text;
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 格式化微博内容
	 *
	 * @param $args = array(
	 * 			'text' = //微博內容,
	 * 			'time' = //发表时间(可以是时间戳也可以是date格式),
	 * 			'at_array' => array('用户昵称' => //用户昵称);@数组,如果没有传入这个字段,则自动解析微博内容中出现的@为数组
	 *  	  )
	 * @return array(
	 *			'text' => //格式化后的微博内容
	 *			'created_at' => 格式化后的发表时间	
	 *         )
	 */
	public function newFormatText($args){
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$objTag = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeEmotion','service');//分析tag的类
		$objKeyWord = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeKeyWord','service');//分析##符号的类
		$objShortLink = clsFactory::create(CLASS_PATH.'tools/analyze/','TAnalyzeShortLink','service'); //解析短连接
		$objTimeFormat =  clsFactory::create(CLASS_PATH.'tools/formatter','TimeFormatter','service'); //创建日期工具对象

		$result = array();
		$result["created_at"] = $objTimeFormat->timeFormat($args['time']);


		//解析表情
		$text = $args['text'];
		$text = $this->newAnalyseSinaUrl($text);
		$text = $objTag->textToIcon($text);
		//解析关键字
		$text = $objKeyWord->renderTag($text, true);
		if(isset($args['at_array']) && !empty($args['at_array'])){
			$objAt->atTOlink($text, $args['at_array'], true);
		}else{
			$atArray = $objAt->getAtUsername($text); //解析@成数组
			//解析对应@连接地址
			$objAt->atTOlink($text, $atArray, true);
		}
		//给短URL加超连接
		$text = $objShortLink->textToShortLink($text,true);
		$result['text'] = $text;

		return $result;
	}
	/**
	 * 话题搜索
	 *
	 * @author 翟健雯<jianwen@staff.sina.com.cn> 
	 * @param $args = array{ 'keyword' => //搜索的关键字
	 * 						 'filter_rt' => filter_rt : 0  全部;  4  只搜转发; 5  只搜原创
	 * 						}
	 *
	 *
	 */
	public function searchMblogWireless($args=array()){
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->searchMblogWireless($args);
	}

	/**
	 *
	 * 删除用户list
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @param int $listid lists id
	 * @param int $uid 用户id
	 * @param int $source appkey
	 * @param string $userpwd 用户名和密码(uid:password)
	 * @return json
	 */
	public function delUserList($listid, $uid, $source, $userpwd){
		$restAPI = clsFactory::create('libs/api', 'RestAPI');
		return $restAPI->delUserList($listid, $uid, $source, $userpwd);
	}

	/**
	 *
	 * 解析视频url
	 * @param string $url 视频输入地址
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * return array(7) {
	 *	["code"]=> int(1)
	 *	["shorturl"]=> string(6) "hikaBy"
	 *	["url"]=> string(54) "http://i7.imgs.letv.com/player/swfPlayer.swf?id=865942"  播放地址
	 *	["title"]=> string(19) " "   视频名称
	 *	["pic"]=> string(116) "http://img1.c3.letv.com/mms/thumb/2010/08/17/78fc0f5a4f41c60231390812f1a52444/78fc0f5a4f41c60231390812f1a52444_3.jpg"  视频显示图像 
	 *	["url_type"]=> string(19) "SHORTURL_TYPE_VIDEO"
	 *	["from"]=> int(4)
	 *	}
	 */
	public function analyseVideoUrl($url){
		$objAnalyze  = clsFactory::create('tools/analyze/', 'ftAnalyze', 'service');
		return $objAnalyze->link($url);
	}

	/**
	 * 格式化微博feedlist
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @param array $mblogList 微博列表
	 * @return array
	 */
	public function formatMblogList($mblogList){
		if(!is_array($mblogList)){
			return false;
		}

		if(empty($mblogList)){
			return $mblogList;
		}

		//格式化处理
		foreach($mblogList as $k=>&$v) {
			if(empty($v)){
				unset($mblogList[$k]);
				continue;
			}

			if($v['time'] === null){
				$strTime = strtotime($v['created_at']);
			}else{
				$strTime = $v['time'];
			}

			if($v['content'] === null){
				$strText = $v['text'];
			}else{
				if(!isset($v['content']['text'])){
					$strText = '';
				}else{
					$strText = $v['content']['text'];	
				}
			}
			$strText = htmlspecialchars($strText);

			$v['orig_text'] = $strText;
			$args = array(
						'text'     => $strText,
						'at_array' => isset($v['content']['atUsers'])?$v['content']['atUsers']:array(),
						'time'     => $strTime
			);
			$textInfo = $this->formatText($args);
			if(empty($v['content'])){
				$v["text"]     = isset($textInfo['text'])?$textInfo['text']:'';
				$v["old_text"] = isset($v["text"])?$v["text"]:'';
				$v['time']     = $strTime;
			}else{
				if(is_array($v['content'])){
					$v['content']["text"] = isset($textInfo['text'])?$textInfo['text']:'';
					$v['content']["old_text"] = $v['content']["text"];
				}
			}

			//var_dump($v);
			$regx= '/<a (href.*)>(.*)<\/a>/';
			if(preg_match_all($regx, $v['source'], $math, PREG_SET_ORDER )!=false){
				$math=$math[0];
				$apptitle = $math[2];
				if(mb_strlen(htmlspecialchars_decode($math[2]), 'UTF-8') > 13) {
					$apptitle = htmlspecialchars(mb_substr(htmlspecialchars_decode($math[2]), 0, 13, 'UTF-8')) . "...";
				}
				$v['source'] = '<a ' . $math[1] .  'target=\'blank\' title=' . $math[2] . '>' . $apptitle . '</a>';

			}

			$v["created_at"] = $textInfo['created_at'];
			if($v['retweeted_status'] && is_array($v['retweeted_status'])){
				$args = array(
					'text'     => htmlspecialchars($v['retweeted_status']['text']),
					'time'     => $v['retweeted_status']['created_at']
				);
				$rtInfo = $this->formatText($args);
				$v['retweeted_status']['created_at'] = $rtInfo['created_at'];
				$v['retweeted_status']['text'] = $rtInfo['text'];
			}
		}
		return $mblogList;
	}

	/**
	 *
	 * 格式化微博客(静态页面使用，不同在于时间的处理，没有今天或者几秒，应格式化为具体的日期)
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 */
	public function formatStaticHtmlMblog($mblogList){
		if(!is_array($mblogList)){
			return false;
		}

		if(empty($mblogList)){
			return $mblogList;
		}

		//格式化处理
		foreach($mblogList as $k=>&$v) {
			if(empty($v)){
				unset($mblogList[$k]);
				continue;
			}

			if($v['time'] === null){
				$strTime = strtotime($v['created_at']);
			}else{
				$strTime = $v['time'];
			}

			if($v['content'] === null){
				$strText = $v['text'];
			}else{
				$strText = $v['content']["text"];
			}
			$strText = htmlspecialchars($strText);
			$v['orig_text'] = $strText;
			$args = array(
						'text'     => $strText,
						'at_array' => $v['content']['atUsers'],
						'time'     => $strTime
			);
			$textInfo = $this->formatStaticHtmlText($args);

			if($v['content'] === null){
				$v["text"]     = $textInfo['text'];
				$v["old_text"] = $v["text"];
				$v['time']     = $strTime;
			}else{
				$v['content']["text"] = $textInfo['text'];
				$v['content']["old_text"] = $v['content']["text"];
			}
			$regx= '/<a (href.*)>(.*)<\/a>/';
			if(preg_match_all($regx, $v['source'], $math, PREG_SET_ORDER )!=false){
				$math=$math[0];
				$apptitle = $math[2];
				if(mb_strlen(htmlspecialchars_decode($math[2]), 'UTF-8') > 13) {
					$apptitle = htmlspecialchars(mb_substr(htmlspecialchars_decode($math[2]), 0, 13, 'UTF-8')) . "...";
				}
				$v['source'] = '<a ' . $math[1] .  'target=\'blank\' title=' . $math[2] . '>' . $apptitle . '</a>';

			}

			$v["created_at"] = $textInfo['created_at'];
			if($v['retweeted_status'] && is_array($v['retweeted_status'])){
				$args = array(
					'text'     => htmlspecialchars($v['retweeted_status']['text']),
					'time'     => $v['retweeted_status']['created_at']
				);
				$rtInfo = $this->formatStaticHtmlText($args);
				$v['retweeted_status']['created_at'] = $rtInfo['created_at'];
				$v['retweeted_status']['text'] = $rtInfo['text'];
			}
			if($v['rt'] && is_array($v['rt'])){
				$args['text'] = $v['rt']['rtreason'];
				$rtInfo = $this->formatStaticHtmlText($args);
				$v['rt']['old_rtreason'] = $v['rt']['rtreason'];
				$v['rt']['rtreason'] = $rtInfo['text'];
			}
		}
		return $mblogList;
	}

	/**
	 * 格式化微博客(静态页面使用，不同在于时间的处理，没有今天或者几秒，应格式化为具体的日期)
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @date 2010-08-31
	 *
	 * @param $args = array(
	 * 						'text' = //微博內容,
	 * 						'time' = //发表时间,
	 * 						'at_array' => array('用户昵称' => //用户昵称);
	 *  					)
	 *  @return array(
	 *  				'text' => //格式化后的微博内容
	 *  				'created_time' => 格式化后的发表时间	
	 *  )
	 */
	public function formatStaticHtmlText($args){
		$objBase62 = clsFactory::create ('libs/basic/tools', 'bBase62Parse' );//解析字符串
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$objTag = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeEmotion','service');//分析tag的类
		$objKeyWord = clsFactory::create(CLASS_PATH.'tools/analyze','TAnalyzeKeyWord','service');//分析##符号的类
		$objShortLink = clsFactory::create(CLASS_PATH.'tools/analyze/','TAnalyzeShortLink','service'); //解析短连接
		$objTimeFormat =  clsFactory::create(CLASS_PATH.'tools/formatter','TimeFormatter','service'); //创建日期工具对象

		$result = array();
		//解析时间
		$created_at = date('Y-m-d H:i', $args['time']);
		$result["created_at"] = $objTimeFormat->staticHtmlTimeFormat($created_at);

		//解析表情
		$text = $args['text'];
		$text = $objTag->textToIcon($text);
		//解析sinaurl标签
		$text = $this->newAnalyseSinaUrl($text);
		//给短URL加超连接
		$text = $objShortLink->textToShortLink($text,true);
		//解析关键字
		$text = $objKeyWord->renderTag($text, true);
		//解析对应@连接地址
		if(!$args['at_array']){
			$args['at_array'] = $objAt->getAtUsername($text);
		}
		$objAt->atTOlink($text, $args['at_array'], true);

		$result['text'] = $text;

		return $result;
	}

	/**
	 * 从lists读取微博
	 * @author 翟健雯6937<jianwen@staff.sina.com.cn>
	 * @date   2010-09-25
	 *
	 * @param  $args = array(
	 'listId'   => //list的id
	 'uid'  => /创建人的id
	 'page' => 分页数
	 'source' => OPENAPI key值
	 'since_id' => 返回带有比指定list ID大的ID
	 'max_id'   => 返回带有一个小于（就是比较老的）或等于指定list ID的ID 的结果。
	 'maxcount' => 每次返回的最大记录数。 默认100
	 'userpwd' => "用户邮箱:用户密码"
	 'feature' => 微博类型，0：全部，1：原创，2：图片，3：视频，4：音乐
	 )
	 * @return array
	 */
	public function getMblogFromList($args){
		$restAPI = clsFactory::create('libs/api', 'RestAPI');
		return $restAPI->newgetMblogFromList($args['listId'], $args['uid'], $args['page'], $args['source'],$args['since_id'], $args['max_id'], $args['maxcount'], $args['userpwd'],$args['feature']);
	}

	/**
	 * 通过id获取mid
	 * @date 2010-09-25
	 * @param  $args = array(
	 'id' => //必选参数，微博/评论/私信 id 
	 'type' => 必选参数，id的类型：1=微博，2=评论；3=私信 
	 'is_batch' => 可选参数，是否使用批量模式，值为1或者0，为1时使用批量模式，mid参数可以提交由半角逗号分隔的多个值
	 )
	 * $return array(
	 'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 'mid' => '//base62进制的微博id'
	 )
	 )
	 */

	public function id2mid($args){
		$restAPI = clsFactory::create('libs/api', 'RestAPI');
		return $restAPI->id2mid($args);
	}

	/**
	 * 关键词搜索(临时的)
	 *
	 * @date   2010-08-30
	 *
	 * @param 	$args = array(
	 'uid'   => //当前登录用户uid
	 'tag'  => //搜索关键词
	 )
	 * @return array(
	 * 			'errno'  => 错误编码,
	 'errmsg' => 错误信息,
	 'result' => array(
	 [0] => 微博通用数据结构
	 )
	 )
	 */
	public function newsearchMblog($args) {
		$api = clsFactory::create('libs/api', 'InternalAPI');
		$args['cip'] = Check::getIp();
		$args['appid'] = RADIO_SOURCE_APP_ID;
		return $api->newSearchMblog($args);
	}

	/**
	 * 检查关键字  -- 废弃方法
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	public function checkkeyword($content,$uid){
		$ip = tCheck::getIp();
		$url = "http://i.t.sina.com.cn/check/checkkeyword.php?content={$content}&cip={$ip}&appid=51&uid={$uid}";
		$user_result = json_decode ( $this->requestGet ( $url ), true );
		return $user_result;
	}

	/**
	 * 微博来源格式化处理
	 * @param unknown_type $blog 微博内容
	 * @author gaofeng3
	 */
	private function getSourceLink(&$blog){
		$regx= '/<a (href.*)>(.*)<\/a>/';
		$source_link = '';
		//来源连接处理
		if(!empty($blog['appinfo']) && isset($blog['appinfo']['sourcetitle'])){
			if(!empty($blog['extinfo']) && isset($blog['extinfo']['title']) && isset($blog['extinfo']['url'])){
				$source_link = '<a href="'.$blog['extinfo']['url'].'"'.' title="'.$blog['appinfo']['name'].' - '.$blog['extinfo']['name'].'" target="_blank">'.$blog['appinfo']['sourcetitle'].' - '.$blog['extinfo']['title'].'</a>';
			}else{
				$source_link = '<a href="'.$blog['appinfo']['sourceurl'].'"'.' title="'.$blog['appinfo']['name'].'" target="_blank">'.$blog['appinfo']['sourcetitle'].'</a>';
			}
		}else if(preg_match_all($regx, $blog['source_link'], $math, PREG_SET_ORDER )!=false){
			//连接来源老接口兼容处理
			$math=$math[0];
			$apptitle = $math[2];
			if(mb_strlen(htmlspecialchars_decode($math[2]), 'UTF-8') > 13) {
				$apptitle = htmlspecialchars(mb_substr(htmlspecialchars_decode($math[2]), 0, 13, 'UTF-8')) . "...";
			}
			$source_link = '<a ' . $math[1] . ' title=' . $math[2] . '>' . $apptitle . '</a>';
		}else{
			$source_link = $blog['source_link'];
		}
		return $source_link;
	}
	

	
	/**
	 * 新搜索接口
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	public function searchMblogByrpc($args){
		if(is_array($args) && !empty($args)){
			$param = "";
			foreach ($args as $key => $val){
				if(empty($param)){
					$param .= "{$key}={$val}";
				}
				else{
					$param .= "&{$key}={$val}";
				}
			}
			$url = "http://weibointra.match.sina.com.cn/openService/rpcMblog.php?{$param}";
			$user_result = json_decode ( $this->requestGet ( $url,10 ), true );
			return $user_result;
		}
		else{
			return array('errno'=>-4,'result'=>'参数错误');
		}
	}
	
	/**
	 * 根据appid获取信息
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	public function getAppInfo($appids){
		$url = "http://i.open.t.sina.com.cn/rpcapi";
		$serviceName = 'application';
		$apiFunc = 'getAllAppInfoForWeibo';
		$args = array($appids);
		$data = array('name'=>$serviceName, 'func'=>$apiFunc, 'args'=>serialize($args));
		$result = $this->requestPost(1, $url, $data);
		$result = @unserialize( trim($result) );
		return $result;
	}
	
	
	/**
	 * 2011-9-8最新搜索接口
	 * Enter description here ...
	 * @param unknown_type $content
	 */
	public function searchNewMblogByrpc($args){
		if(is_array($args) && !empty($args)){
			$param = "";
			foreach ($args as $key => $val){
				if(empty($param)){
					$param .= "{$key}={$val}";
				}
				else{
					$param .= "&{$key}={$val}";
				}
			}
			$url = "http://weibointra.match.sina.com.cn/search/statuses.php?{$param}";
			$user_result = json_decode ( $this->requestGet ( $url,10 ), true );
			return $user_result;
		}
		else{
			return array('errno'=>-4,'result'=>'参数错误');
		}
	}
	
	
}
?>
