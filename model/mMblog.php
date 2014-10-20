<?php
/**
 * mblog model
 *
 * @date 2010-08-30
 * @package
 * @author 刘勇刚<yonggang@staff.sina.com.cn>
 * @copyright (c) 2009, 新浪网 MiniBlog All rights reserved.
 */
class mMblog extends model{

	/**
	 * 发表微博客
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
			                 'uid'   => $uid // 当前登录uid
			                 'content'  =>  微博内容
			                 'appid'   => appid
			                 ''pid     => 上传的图片
							)
	  * @return array(	
		   			'flag'  => true or false;
					'result' => 如果flag为true,返回array('mblogid'  = // 微博id);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function addMblog($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog','service');
		$args['ip'] = $this->getIp();
		$addInfo = $dMblog->addMblog($args);
		$result = array();
		if($addInfo){
			$result = array(
							'flag'   => true,
							'result' => $addInfo
						);
		}
		else{
			$result = array(
							'flag'  => false,
							'errorno' => $GLOBALS ['ERROR_NO'],
							'errorcode' => $GLOBALS ['SUB_ERROR_NO'],
							'errmsg'   => ''	
						);			
		}
		return $result;
	}

	/**
	 * 根据mid批量获取微博信息
	 * @date   2010-08-30
	 * 
	 * @param 	$args = array(
								'uid'   => $uid // 当前登录uid
								'mid'    =>  微博id,批量用逗号隔开
							)
	  * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('微博id' => //微博内容);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function getMblogContent($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$mblogInfo = $dMblog->getMblogContent($args);
		
		$result = array();
		if(is_array($mblogInfo)){
			$result = array(
							'flag'   => true,
							'result' => $mblogInfo['result']
						);
		}
		else{
			$result = array(
							'flag'  => false,
							'errorno' => $mblogInfo['errno'],
							'errmsg'  => $mblogInfo['msg']
						);			
		}
		return $result;
	}
	
	/**
	 * 删除微博客
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-27
	 * 
	 * @param 	$args = array(
							'uid'   => $uid // 当前登录uid
							'mblogid' =>  微博客ID
							)
	  * @return array(	
		   			'flag'  => true or false;
					'errno' => 如果为FALSE，返回errno
	 			)
	 */
	public function delMblog($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$delInfo = $dMblog->delMblog($args);
		$result = array();
		if($delInfo){
			$result = array(
							'flag'   => true
						);
		}
		else{
			$result = array(
							'flag'  => false,
							'errorno' => '-1'	
						);			
		}
		return $result;
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
								'appid'   => 来源ID 可选，默认为6(来自WAP)
							)
	  * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('mblogid' => //微博id);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function repostMblog($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$repostInfo = $dMblog->repostMblog($args);
		$result = array();
		if($repostInfo){
			$result = array(
							'flag'   => true,
							'result' => $repostInfo
						);
		}
		else{
			$result = array(
							'flag'  => false,
							'errorno' => $GLOBALS ['ERROR_NO'],
							'errorcode' => $GLOBALS ['SUB_ERROR_NO'],
							'errmsg'   => ''	
						);			
		}
		return $result;
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
							)
	  * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('favid' => //收藏ID(为-1时说明此话题已收藏));
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function addFavMblog($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$repostInfo = $dMblog->addFavMblog($args);		
		return $this->returnStyle($repostInfo);
	}

	public function addFavMblog2($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$repostInfo = $dMblog->addFavMblog2($args);		
		return $this->returnStyle($repostInfo);
	}
	
	
	/**
	 * 格式化微博客
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-09-01
	 * 
	 * @param 	$args = array(
							'微博id'   => 微博信息
							)
	  * @return array(	
		   			'微博id'   => 微博信息
	 			)
	 */
	public function formatMblog($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$mblogInfo = $dMblog->formatMblog($args);
		return $mblogInfo;
	}
	
	/**
	 * 格式化微博feedlist
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @param array $mblogList 微博列表
	 * @return array
	 */
	public function formatMbloglist($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$mblogInfo = $dMblog->formatMblogList($args);
		return $mblogInfo;
	}
	
	/**
	 * 
	 * 格式化微博客(静态页面使用，不同在于时间的处理，没有今天或者几秒，应格式化为具体的日期)
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 */
	public function formatStaticHtmlMblog($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$mblogInfo = $dMblog->formatStaticHtmlMblog($args);
		return $mblogInfo;
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
							'appid'   => 来源ID 可选，默认为6(来自WAP)
							)
	  * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('cmtid' => 评论id);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function addComment($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$addInfo = $dMblog->addComment($args);
		return $this->returnStyle($addInfo);
	}
	/**
	 * 发表评论(返回错误码)
	 * @author 王超<wangchao@staff.sina.com.cn>
	 * @date   2010-11-04
	 *
	 * i.t.sina.com.cn/wap/addcomment.php
	 * @param 	$args = array(
							'uid'   => $uid // 当前登录uid
							'srcuid'    =>  //资源所属者UID(微博客发表人ID)
							'srcid'  =>  资源ID(微博客ID)
							'content'   => 评论/回复内容
							'appid'   => 来源ID 可选，默认为6(来自WAP)
							)
	  * @return array(	
		   			'flag'  => true or false,
					'cmid' =>  评论id,
					'errCode' => 错误码,
					'errMsg' => 错误信息,
	 			)
	 */
	public function addCommentReturnCode($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$addInfo = $dMblog->addCommentReturnCode($args);
		if($addInfo != false && isset($addInfo['cmtid'])) {
			return $return = array(
				'flag' => true,
				'cmid' => $addInfo['cmtid']
			);
		} else {
			return $return = array(
						'flag'    => false,
						'errCode' => $GLOBALS ['SUB_ERROR_NO'],
						'errmsg'  => '', //暂时无法获取
						);
		}
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
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('srcid' => 评论数,);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function getCommentsNum($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$commentsInfo = $dMblog->getCommentsNum($args);
		return $this->returnStyle($commentsInfo);
	}
	
	/**
	 * 批量获取评论数和转发数
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date   2010-08-31
	 * 
	 * @param 	$args = array(
							'mids'   => //微博mid
							)
	  * @return array(
	  * 			'flag'  => true or false,
					'result' => 如果flag为true,返回array([rtnum]=>转发数,[cmtnum]=>评论数);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	public function getMblogCounts($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$countsInfo = $dMblog->getMblogCounts($args);
		return $this->returnStyle($countsInfo);
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
							)
	  * @return array(	
		   			'flag'  => true or false,
		   			'errno' => 如果flag为false，错误码
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
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['sort'] = 'desc';
		$commentInfo = $dMblog->getArticleComment($args);
		return $this->returnStyle($commentInfo);
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
				)
	  * @return array(	
		   			'flag'  => true or false,
	 				'errno' => 如果flag为false，错误码
	 			)
	 */
	public function delComment($args) {
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$delInfo = $dMblog->delComment($args);
		return $this->returnStyle($delInfo);
	}
	
	
	/**
	 * 批量发表私信
	 * @date 2010-09-09
	 * @param  $args = array(
	 						'fromuid'   => $uid  //当前登录uid
	 						'touids'  => //对方uid,批量逗号分隔
	 						'content'  => //内容
	 						 )
	 * $return array(
	 				'errno'  => 错误编码,
					'errmsg' => 错误信息,
	 				)
	 */
	public function sendMessageMulti($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$messageInfo = $dMblog->sendMessageMulti($args);
		return $this->returnStyle($messageInfo);
	}
	/**
	 * 发通知接口
	 *
	 * @param unknown_type $args
	 */
	public function sendNotice($system_args,$method_args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$result = $dMblog->sendNotice($system_args,$method_args);
		return $this->returnStyle($result);
	}
	
	/**
	 * 关键词搜索
	 * @date 2010-08-30
	 * @param  $args = array(
	 						'uid'   => //当前登录用户uid
							'tag'  => //搜索关键词
	 						 )
	 * $return array(
	 				'flag'  => true or false,
	 				'errno' => 如果flag为false，错误码
	 				)
	 */
	public function searchMblog($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['pagesize'] = 50;
		$searchInfo = $dMblog->searchMblog($args);
		return $this->returnStyle($searchInfo);
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
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$searchInfo = $dMblog->long2short($args);
		return $this->returnStyle($searchInfo);
	}
	
	/**
	 * 从lists读取微博
	 * @author 翟健雯6937<jianwen@staff.sina.com.cn>
	 * @date   2010-09-25
	 * @param  $args = array(
	 						'listId'   => //list的id
							'uid'  => /创建人的id
							'page' => 分页数
							'source' => OPENAPI key值
							'since_id' => 返回带有比指定list ID大的ID
							'max_id'   => 返回带有一个小于（就是比较老的）或等于指定list ID的ID 的结果。
							'maxcount' => 每次返回的最大记录数。 默认100
	 						 )
     * @return array  微博数组
	 */
	public function getMblogFromList($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		return $dMblog->getMblogFromList($args);
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
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$midInfo = $dMblog->id2mid($args);
		return $this->returnStyle($midInfo);
	}


	/**
	 * 将要返回前台的数组格式化
	 * 
	 * @param array $array
	 * @return array(	
		   			'flag'  => true or false,
					'result' => 如果flag为true,返回array('uid' => 用户uid);
					'errno' => 如果flag为false,返回错误码
	 			)
	 */
	private function returnStyle($array){
		$result = array();
		if($array){
			$result = array(
							'flag'   => true,
							'result' => $array
							);
		}
		else{
			$result = array(
							'flag'   => false,
							'errorno' => '-1'
							);
		}
		return $result;
	}
	
	/**
	 * 返回ip地址
	 * 
	 * @param null
	 * @return string $ip 
	 */
	private function getIp(){
		clsFactory::create(CLASS_PATH.'tools/check', 'Check','service');
		return Check::getIp();
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
	 * 截取字符串
	 * @param $text  需要截取的字符串
	 * @param $num   截取的长度
	 * @param $suffix  截取后的要加的后缀
	 * @return $text   截取完后的字符串d
	 */
	public function substringText($text, $num, $suffix = ''){
		if(mb_strlen(htmlspecialchars_decode($text), 'UTF-8') > $num) {
			$text = htmlspecialchars(mb_substr(htmlspecialchars_decode($text), 0, $num, 'UTF-8'));
			if($suffix){
				$text .= $suffix;
			}
		}
		return $text;
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
	public function newAddMblog($args, $from = MBLOG_APP_MBLOG){
		if(empty($args) || !is_array($args)){
			return false;
		}
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args['ip'] = $this->getIp();
		$args['appid'] = $from; //来源,默认为新浪微博
		$blog = $dMblog->newAddMblog($args); //发表微博
		if($blog === false){
			return false;
		}
		return $blog;
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 批量获取用户信息
	 * @param uids uid数组
	 * @param size 头像大小
	 * @return array
	 */
	public function newGetUserList($uids, $size = 50){
		if(empty($uids) || !is_array($uids)){
			return false;
		}
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$args = array(
			'uids' => $uids,
			'size' => $size,
		);
		return $dMblog->newGetUserInfos($args);
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
	public function newGetUserRelation($uid, $fuids){
		if(empty($uid)){
			return false;
		}
		if(!isset($fuids) || empty($fuids) || !is_array($fuids)){
			return false;
		}
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$paras = array(
			'uid' => $uid,
			'fuids' => $fuids,
		);
		
		return $dMblog->newGetUserRelation($paras);
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
		if(empty($args) || !is_array($args)){
			return false;
		}
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		return $dMblog->formatText($args);
	}
	
	/**
	 * @author 张倚弛(yichi@staff.sina.com.cn)
	 * 格式化假写微薄数据
	 * @param $args = array(
	 *            'mblogid' => //新微薄mblogid
	 *            'content' => //新微薄content
	 *            'pid'     =>
	 *            'usrinfo' =>
	 *        )
	 */
	public function fakeFormatText($args){
		if(!is_array($args) || empty($args['mblogid']) || empty($args['content']) || empty($args['usrinfo'])){
			return false;
		}
		
		$mid = $this->midDecode($args['mblogid']);

		$strContent = htmlspecialchars($args['content']);

		//将微博内容的url地址转成短地址
		$strContent = $this->newLongToShort($strContent);
		
		//将微博内容格式化 begin
		$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
		$atUser = $objAt->getAtUsername($strContent);
		$format = array(
			'text' => $strContent,
			'time' => time(),
			'at_array' => $atUser
		);

		$formatResult = $this->newFormatText($format);
		
		//将微博内容格式化 end
		
		//取图片的地址 begin
		$pic_url = '';
		if($args['pid']){
			$pic_num = (hexdec(substr($args['pid'], -2)) % 16) + 1;
			//$pic_url = sprintf(PHOTO_URL, $pic_num, 'thumbnail', $this->para['pid']);
			include_once SERVER_ROOT.'tools/image/ImageUrl.php';
			$imageurl = new ImageUrl();
			$pic_url = $imageurl -> get_image_url(array($args['pid']), 'thumbnail');
			$pic_url =  $pic_url[$args['pid']];
		}
		//取图片的地址 end
		$cmf = clsFactory::create('tools', 'commonFunc');				
		$contentArr = array(
			'uid'		=> $args['usrinfo']['uid'],
			'rt_uid'	=> $args['usrinfo']['uid'],
			'mid'		=> $mid,
			'mblogid'   => $args['mblogid'],
			'icon'  => $args['usrinfo']['profile_image_url'],
			'user_link' => $cmf->getUidUrl(array('uid'=>$args['usrinfo']['uid'],'domainType'=>DOMAIN_TYPE)),
			'nick' 		=> $args['usrinfo']['name'],
			'user_type'		=> $args['usrinfo']['user_type'],
			'content'	=> array(
								'text'	=> $formatResult['text'],
								'pid'	=> $args['pid'],
								'pic'   => $pic_url,
								'encode_text' => rawurlencode($formatResult['text'])
								),
			'user' => $args['usrinfo'],
			'mblog_link'=> $cmf->getUidUrl(array('uid'=>$args['usrinfo']['uid'],'type'=>'mblog','mid'=>$mid,'domainType'=>DOMAIN_TYPE)),
			'source_link'=>$args['source'],
			'ctime'		=> time(),
			'created_at'=> '10秒前',
			'rt_mblogid'=> $mid
		);
		return $contentArr;
	}
	
	/**
	 * @author 张倚弛(yichi@staff.sina.com.cn)
	 * 格式化假写转发
	 * @param array $args = array(
	 * 					'mid'=>  原微薄ID
	 * 					'forwardid  =>  转发微薄ID
	 * 					'uid'    =>
	 *                  'reason' =>
	 *                  'usrinfo'=>
	 * 				)
	 */
	public function fakeFormatForward($args){
		//获取原微博内容
		if(!is_array($args)){
			return false;
		}
		$dmblog = clsFactory::create(CLASS_PATH . 'data', 'dMblog', 'service');
		$args1 = array(
			'mid' => $args['mid'],
			'uid' => $args['uid']
		);
		$rootBlog = $this->getMblogContent($args1);
		if($rootBlog['flag']) {
			$rootBlogArr = $rootBlog['result']['0'];			
			$mid = $dmblog->midDecode($rootBlogArr['mblogid']);
			$forwardmid = $dmblog->midDecode($args['forwardid']);
			$rootBlog = array();
			if($rootBlogArr['rt']) {
				$rootBlog['uid'] = $rootBlogArr['rt']['rootuid'];
				$rootBlog['mblogid'] = $rootBlogArr['rt']['rootid'];
			} else {
				$rootBlog['uid'] = $rootBlogArr['uid'];
				$rootBlog['mblogid'] = $rootBlogArr['mblogid'];
			}
			$rootBlog['mid'] = $dmblog->midDecode($rootBlog['mblogid']);
			$rootBlog['content']['pic'] = $rootBlogArr['content']['pic'];
			$rootBlog['content']['old_text'] = $rootBlogArr['content']['text'];

			//获取转发数与评论数
			$rtnumAndCmnum = $this->getMblogCounts(array('mids'=>$rootBlog['mblogid']));
			$rootBlog['rtnum'] = $rtnumAndCmnum['result'][$rootBlog['mid']]['rtnum'];
			$rootBlog['cmtnum'] = $rtnumAndCmnum['result'][$rootBlog['mid']]['cmtnum'];

			//获取用户信息
			$rootBlog['user'] = array();
			$mPerson = clsFactory::create(CLASS_PATH . 'model', 'mPerson', 'service');
			$args2 = array('uid'=>$args['uid'], 'detail'=>1, 'fuid'=>$rootBlog['uid'],'size'=>'50');
			$user = $mPerson->getUserInfo($args2);
			$userArr = $user['result'];
			$rootBlog['user']  = array(
							'uid' 						=> $userArr['uid'],
							'screen_name' 				=> $userArr['nick'],
							'name' 						=> $userArr['nick'],
							'profile_image_url' 		=> $userArr['portrait'],
							'domain' 					=> $userArr['domain'],
							'verified' 					=> $userArr['vip'],
						);
			//图片信息
			$pic_url = '';
			if($rootBlog['content']['pic']){
				$pidKey = array_keys($rootBlog['content']['pic']);
				$pid = $pidKey[0];
				$pic_url =  $rootBlog['content']['pic'][$pid];
			}
			$rootBlog['thumbnail_pic'] = $pic_url;
			
			$rootBlog['content']['text'] = $rootBlog['content']['old_text'];

			//将微博内容格式化 begin
			$objAt = clsFactory::create(CLASS_PATH.'tools/analyze', 'TAnalyzeAt', 'service');//分析@符号的类
			$atUser = $objAt->getAtUsername($rootBlog['content']['text'] );
			$format = array(
				'text' => $rootBlog['content']['text'],
				'time' => time(),
				'at_array' => $atUser
			);
			$formatRootResult = $dmblog->formatText($format);

			$strReason = htmlspecialchars($args['reason']);
			//将微博内容的url地址转成短地址
			$strReason = $this->newLongToShort($strReason);
		/*	preg_match_all("!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\>\%\>\/\?\:\@\&\=(\&amp\;)\#]+!is", $strReason, $out);
			if($out[0]){
				foreach ($out[0] as $value){
	        			$short_url = $mMblog->long2short(array('url' => $value));
	        			if($short_url['flag']){
	        				if(substr($short_url['result']['url'], 0, 7) != 'http://')
	        				{
	        					$short_url = 'http://sinaurl.cn/'.$short_url['result']['url'];
	        					$this->para['reason'] = str_replace($value, $short_url, $strReason);
	        				}		        				
	        			}
	        			
	        	}
			}*/
			//将微博内容的url地址转成短地址  end
			$atUser = $objAt->getAtUsername($strReason);
			$format = array(
				'text' => $strReason,
				'time' => time(),
				'at_array' => $atUser
			);
			$formatResult = $dmblog->formatText($format);
			//将微博内容格式化  end
			$contentArr = array(
				'created_at'  => '10秒前',
				'mblogid'     => $args['forwardid'],
				'uid'		  => $args['uid'],
				'time'		  => time(),
				'rtnum'		  => 0,
				'cmtnum'	  => 0,
				'icon'        => $args['usrinfo']['portrait'],
				'nick' 		  => $args['usrinfo']['nick'],
				'vip'		  => $args['usrinfo']['vip'],
				'source_link' => $args['source'],
				'mid'		  => $forwardmid,
				'content'	  => array(
					'text'    	  => $formatRootResult['text'],
					'pic'         => $rootBlog['content']['pic'][$pid],
			        'pid'         => $pid,
					'encode_text' => rawurlencode($formatRootResult['text'])
				),
				'user'		  => $args['usrinfo'],
				'rt'=>array(
					'rootuid'  => $rootBlog['uid'],
					'rootid'   => $rootBlog['mblogid'],
					'fromuid'  => '',
					'rturl'    => '',
					'old_rtreason' => strip_tags($formatResult['text']),
					'encode_rtreason' => strip_tags($formatResult['text']),
					'rtreason' => htmlspecialchars_decode($formatResult['text']),
					'text' => htmlspecialchars_decode($formatResult['text']),
					'rootrtnum'=> $rootBlog['rtnum'],
					'rootcmtnum'  => $rootBlog['cmtnum'],
					'rootuser' => array(
						'uid'      => $rootBlog['user']['uid'],
						'nick'     => $rootBlog['user']['screen_name'],
						'portrait' => $rootBlog['user']['profile_image_url'],
						'vip'      => $rootBlog['user']['verified'],
					
					)
				)
			);
			return $contentArr;
		}else{
			return false;
		}
	}
	
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 将文本中的长url转换成短url(如果文本中的url为短链接，则不需要转换，只需要urlencode)
	 * @param $text
	 * @return string
	 */
	public function newLongToShort($text){
		//将微博内容的url地址转成短地址  begin
		$content = '';
		$match = preg_match_all("!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\>\%\>\/\?\:\@\&\=(\&amp\;)\#]+!is", $text, $out);
		$sMatch = "/http:\/\/sinaurl.cn|t.cn\/[a-zA-Z0-9]{1,}/i";
		$urls = $out[0];
		$r = array();
		//$url = array('t.sina.com','t.sina.com.cn','www.sina.com','weibo.com');
		$url = array('t.sina.com.cn','t.sina.cn','www.weibo.com','weibo.com');
		if($match == true){
			if((is_array($urls) && count($urls) != 0) || $urls != false){//var_dump($urls);
				foreach ($urls as $value){
					$shortR = preg_match_all($sMatch, $value, $sOut); //匹配链接中是否有短链接
					$sUrls = $sOut[0];
					if($shortR == true){
						if((is_array($sUrls) && count($sUrls) != 0) || $sUrls != false){
							foreach ($sUrls as $val){
								$r[] = $val; //如果url中有短链接，则只需要将urlencode，不需要调用long2short
							}
						}
					}else{//没有短链接，则调用long2short，将长url转换成短链接
						$urlArr = parse_url($value);
						if(in_array($urlArr['host'], $url)){
							$r[] = $value;
						}else{
							$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
							$short_url = $dMblog->long2short(array('url' => $value));
				        	if((is_array($short_url) && count($short_url) != 0) || $short_url != false){
				        		if(substr($short_url['url'], 0, 7) != 'http://')
				        		{
				        			$short_url = SHORTURL_DOMAIN .$short_url['url'];
									$r[] = $short_url;
				        		}		        				
				        	}
						}
					}
	        	}//var_dump($urls, $r,$text);
	        	$content = str_replace($urls, $r, $text);
			}
		}else{
			$content = $text;
		}
		//将微博内容的url地址转成短地址  end
		return $content;
	}
	
	/**
	 * 
	 * 解析视频url
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @param string $url 视频输入地址
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
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$aResult = $dMblog->analyseVideoUrl($url);
		return $this->returnStyle($aResult);
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
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$aResult = $dMblog->delUserList($listid, $uid, $source, $userpwd);
		return $this->returnStyle($aResult);
	}
	/**
	 * 关键词搜索
	 * @param  $args = array(
	 						'uid'   => //当前登录用户uid
							'tag'  => //搜索关键词
	 						 )
	 * $return array(
	 				'flag'  => true or false,
	 				'errno' => 如果flag为false，错误码
	 				)
	 */
	public function newsearchMblog($args){
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		$searchInfo = $dMblog->newsearchMblog($args);
		return $this->returnStyle($searchInfo);
	}

	function stripTag($str, $linkWord, $istarget=false) {
		$target = $istarget ? ' target="_blank"' : '';
		if(mb_strwidth($str) > 15) {
			$str = mb_substr($str, 0, 15, 'UTF-8');
		}
		$url = sprintf(SEARCH_MBLOG_URL, urlencode(urlencode(htmlspecialchars_decode($str))));
		$str = '<a href="'.$url.'"'.$target.'>'.$linkWord.'</a>';
		return $str;
	}

	//仅格式化微博内容
	public function formatOnlyText($text){
//		die($text);
		$dMblog = clsFactory::create(CLASS_PATH.'data', 'dMblog', 'service');
		return $dMblog->formatOnlyText($text);
	}


}
?>