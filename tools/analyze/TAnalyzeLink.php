<?php
/**
 * 分析链接的类
 * 
 * @copyright   (c) 2009, 新浪网SPACE  All rights reserved.
 * @author  朱建鑫
 * @version 1.0 - 2009-07-27
 * @package tools
 */


class TAnalyzeLink
{
    
	var $urlinfo = array();
    
    /**
     * 替换文本中的链接为短URL
     *
     * @param unknown_type $content
     * @param unknown_type $logType
     * @return unknown
     */
    public function getData($conArr,&$shortUrlArr=array(),&$videoArr=array(), &$musicArr=array()) {
        $content = $conArr['content'];
        $action_from = $conArr['from']; //来自评论，转发，还是微博，……
        
        // add by 张鹏飞  页面中提取嵌入的flash视频(pengfei4@staff.sina.com.cn)
        $content = preg_replace("/<embed.*?(http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\[\]\`\<\>\#\%\<\>\/\?\:\@\&\=]+).*?<\/embed>/is","\$1",htmlspecialchars_decode($content));
        // add end
        
        //--获取链接,并替换成短URL
        $out = array();
        
        //preg_match_all("/http:\/\/[^ ]+ /i", $content, $out);
        //用正则进行进行查找
       // $content = htmlspecialchars_decode($content);
        //preg_match_all("!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\<\>\%\<\>\/\?\:\@\&\=(\&amp\;)]+!is", $content, $out);
        preg_match_all("!http\:\/\/[a-zA-Z0-9\$\-\_\.\+\!\*\'\,\{\}\\\^\~\]\`\>\%\>\/\?\:\@\&\=(\&amp\;)\#]+!is", $content, $out);
        $content = htmlspecialchars($content);
        if (count($out[0]) > 0)
        {
            $islink = 1;
            $out[0] = array_unique($out[0]);
            $objMshortUrl = clsFactory::create(CLASS_PATH.'model::mShortUrl','service');
            $arrShortUrl = array();
            foreach ($out[0] as $linkItem)
            {
                $urlinfo = null;
                //--如果是短URL的就不再转了
                $urlArray = parse_url($linkItem);
                $shortUrlArray = parse_url(SHORTURL_DOMAIN);
                if (strtolower(trim($urlArray['host'])) == $shortUrlArray['host'])  //判断域名就是短URL
                {
                    //--取得短URL的ID
                    $shortUrlId = trim($urlArray['path'], "/");
                    if($shortUrlId == ''){
                        return false;
                    }
                    $strinShortUrl = $shortUrlId;

                    //若是短网址需要反解出来
                    $long_url = $objMshortUrl->short2long($shortUrlId);
                    //根据长网址算出网址信息
                    $long_url = $long_url['url'];
                    $urlinfo = $this->getUrlInfo($long_url);
                }
                else    //否则要转成短URL
                {
                    //根据长网址算出网址信息
                    $urlinfo = $this->getUrlInfo($linkItem);
                    $url_type = SHORTURL_TYPE_WEB;
                    if(false !== $urlinfo && $urlinfo['url_type']){
                        $url_type = $urlinfo['url_type'];
                    }
                    if($url_type == SHORTURL_TYPE_VIDEO){
                        //若是视频
                        $url_ext['url'] = $urlinfo['url'];
                        $url_ext['title'] = $urlinfo['title'];
                        $url_ext['pic'] = $urlinfo['pic'];
                    }
                    else if($url_type == SHORTURL_TYPE_MP3){
                        //若是MP3
                        $url_ext['url'] = $urlinfo['url'];
                        $url_ext['title'] = $urlinfo['title'];
                        $url_ext['author'] = $urlinfo['artist'];
                    }else if($url_type == SHORTURL_TYPE_EVENT){
                        //若是活动
                        $url_ext['url'] = $urlinfo['url'];
                        $url_ext['eid'] = $urlinfo['eid'];
                    }
                    $url_from = $urlinfo['type'] ? $urlinfo['type'] : 0;
                    if($url_type == SHORTURL_TYPE_EVENT) {
                    	$strinShortUrl = $objMshortUrl->shortUrl($linkItem, $url_type, $url_ext,$url_from, 1);
                    }else {
                    	$strinShortUrl = $objMshortUrl->shortUrl($linkItem, $url_type, $url_ext,$url_from);
                    }
                    $long_url = $linkItem;
                }
                if ($strinShortUrl === false)       //如果短URL转换失败,则不使用短URL返回
                {
                    return $content;
                }
                
                $extinfo = $objMshortUrl->short2long($strinShortUrl);
                $strinShortUrl = htmlspecialchars($strinShortUrl);
                if(!empty($urlinfo)){
					$this->urlinfo[] = $urlinfo;	//url信息，便于数据统计分析
				}
                
                if($urlinfo && $urlinfo['url_type'] == SHORTURL_TYPE_VIDEO && !empty($extinfo['ext'])){
                    if($action_from == MBLOG_ACTION_COMMENT || $action_from == MBLOG_ACTION_FORWARD){
                        //评论或转发
                        $strShortUrlHtml = "<sina:link src=\"{$strinShortUrl}\" type=\"{$urlinfo['url_type']}\"/>";
                    }
                    else{
                        $strShortUrlHtml = "<sina:link src=\"{$strinShortUrl}\" name=\"{$strinShortUrl}\" type=\"{$urlinfo['url_type']}\"/>";
                        $urlinfo['vname'] = $strinShortUrl;
                        $urlinfo['ourl'] = $long_url;
                        unset($urlinfo['url_type']);
                        $videoArr[] = $strinShortUrl;
                    }
                }
                else if($urlinfo && $urlinfo['url_type'] == SHORTURL_TYPE_MP3) {
                    $strShortUrlHtml = "<sina:link src=\"{$strinShortUrl}\" name=\"{$strinShortUrl}\" type=\"{$urlinfo['url_type']}\"/>";
                    $urlinfo['vname'] = $strinShortUrl;
                    $urlinfo['ourl'] = $long_url;
                    unset($urlinfo['url_type']);
                    unset($urlinfo['from']);
                    $musicArr[] = $strinShortUrl;
                }
                else{
                    $this->weburl[] = $extinfo;
                    $strShortUrlHtml = "<sina:link src=\"{$strinShortUrl}\"/>";
                }
                $shortUrlArr[] = $strinShortUrl;
                
                $linkItem = htmlspecialchars($linkItem);
                $content = str_replace($linkItem, $strShortUrlHtml, $content);
            }
            return $content;
        }
        return false;
    }
    //获取url相关信息
    public function getUrlInfo($url){
        if($url == ''){
            return false;   
        }
        //判断是否视频信息
        if(false !== ($info = $this->isVideoUrl($url))){
            $api = clsFactory::create(CLASS_PATH.'data::dMultiMediaApi');
			$urlinfo = false;
			$urlinfo = $api->getVideoInfo($info);
			if($urlinfo === false) {
				switch($info['from']){
				case VIDEO_TYPE_SINA :
					$urlinfo = $api->getSinaVideoInfo($info['url']);
					break;
				case VIDEO_TYPE_YOUKU :
					$urlinfo = $api->getYoukuVideoInfo($info['url']);
					break;
				case VIDEO_TYPE_TUDOU :
					$urlinfo = $api->getTuDouVideoInfo($info['url']);
					break;
				case VIDEO_TYPE_TIANXIAN :
					$urlinfo = $api->getTianXianVideoInfo($info['url']);
					break;
				case VIDEO_TYPE_XIYOU :
                    $urlinfo = $api->getXiYouVideoInfo($info['url']);
                    break;
				}
			}

			if($urlinfo && $urlinfo['url'] != '') {
                $urlinfo['url_type'] = SHORTURL_TYPE_VIDEO;
                $urlinfo['from'] = $info['from'];
                return $urlinfo;
            }
        }
        //判断是否为音频(MP3信息) 
        if(false !==($urlinfo = $this->isMp3Url($url))) {
           $urlinfo['url_type'] = SHORTURL_TYPE_MP3;
           return $urlinfo;
        }
    	if(false !==($urlinfo = $this->isEventUrl($url))) {
           $urlinfo['url_type'] = SHORTURL_TYPE_EVENT;
           return $urlinfo;
        }
        return false;
    }
    //判断url是否为视频url
    public function isVideoUrl($url){
        if((false !== strpos($url,"video.sina.com.cn") || false !== strpos($url,"video.2010.sina.com.cn")) && strlen($url)>39){
            //可能是新浪视频
            $arr['from'] = VIDEO_TYPE_SINA;
        }
        
        else if(false !== strpos($url,"style.sina.com.cn/style/news/v") || false !== strpos($url,"sports.sina.com.cn/uclvideo"))
        {
            $arr['from'] = VIDEO_TYPE_SINA;         
        }
        
        else if(false !== strpos($url,"youku.com")){
            //youku
            if(preg_match("/v.youku.com\/(v_show)|(v_playlist)\/.*\.html/i",$url,$out)){
                $arr['from'] = VIDEO_TYPE_YOUKU;
                //$url = $out[1];
                
            }
            else if(preg_match("/player.youku.com\/player.php\/sid\/(.*?)\/v\.swf/i",$url,$out)){
                $arr['from'] = VIDEO_TYPE_YOUKU;
                $url = $out[1];
            }           
        }
        else if(false !== strpos($url,"tudou.com/programs/view/")) {
            $arr['from'] = VIDEO_TYPE_TUDOU;
        }
        else if(false !== strpos($url,"tudou.com/playlist/playindex.do")) {
            $arr['from'] = VIDEO_TYPE_TUDOU;
        }
        else if(false !== strpos($url,"letv.com/ptv/vplay")) {
            if(preg_match("/letv.com\/ptv\/vplay\/([0-9]+)/i",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_LESHI;
            }
        }
        else if(false !== strpos($url,"openv.com/") || false !== strpos($url,"vsearch.cctv.com/"))
        {
            if(preg_match("/(.*?openv.com\/.*?_[0-9]+_[0-9]+.*?.html$)/i",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_TIANXIAN;
                $url = $out[1];
            }
            else if(preg_match("/(.*?vsearch.cctv.com\/.*?_[0-9]+_[0-9]+.*?.html$)/i",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_TIANXIAN;
                $url = $out[1]; 
            }
        }
        else if(false !== strpos($url,"56.com/"))
        {
            if(preg_match("/(.*?56.com\/u[0-9]+\/.*?.html$)/i",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_56COM;
                $url = $out[1]; 
            }
        }
        
        else if(false !== strpos($url,"ku6.com/"))
        {
            if(preg_match("/ku6.com\/.*?\/([A-Za-z0-9_-]+).html$/",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_KU6;
                $url = $out[1];
            }
        }
        
        else if(false !== strpos($url, "xiyou.cntv.cn/video/") )
        {
            if(preg_match("/xiyou.cntv.cn\/video\/([A-Za-z0-9-]+)$/",$url,$out))
            {
                $arr['from'] = VIDEO_TYPE_XIYOU;
                $url = $out[1];
            }
        }
		
        else if(false !== strpos($url, "joy.cn/") )
        {
			$arr['from'] = VIDEO_TYPE_JIDONG;
        }

		else if(false !== strpos($url, "bugu.cntv.cn/"))
		{
			$arr['from'] = VIDEO_TYPE_BUGU;
		}
		else if(false !== strpos($url, "qiyi.com/") )
		{
			$arr['from'] = VIDEO_TYPE_QIYI;
		}
		else if(false !== strpos($url,"yinyuetai.com"))
		{
			$arr['from'] = VIDEO_TYPE_YINYUETAI;
		}
		else if(false !== strpos($url, "video.xunlei.com/") )
		{
			$arr['from'] = VIDEO_TYPE_XUNLEI;
		}
	    else if(false !== strpos($url, "6.cn/watch/") )
		{
			$arr['from'] = VIDEO_TYPE_LIUCN;
		}
    	else if(false !== strpos($url, "v1.cn") )
		{
			$arr['from'] = VIDEO_TYPE_VODONE;
		}
    	else if(false !== strpos($url, "ikan.pptv.com/p/") )
		{
			$arr['from'] = VIDEO_TYPE_PPTV;
		}
        else {
            //网页url
            return false;
        }

        if($arr['from'] && $url != ''){
            $arr['url'] = $url;
            return $arr;
        }
        return false;
    }

    /**
     * isMp3Url 
     * 
     * 判断是否为Mp3 Url
     * @param mixed $url 
     * @access public
     * @return void
     */
    public function isMp3Url($url)
    {
        $mMusic = clsFactory::create(CLASS_PATH.'model::mMusic');
        $result = $mMusic->isSinaMusicUrl($url);
        $urlInfo = false;
        if(false !== $result) {
            $urlInfo['url'] = $url;
            $urlInfo['from'] = MUSIC_TYPE_SINA;
            $urlInfo['title'] = $result['name'];
            $urlInfo['artist'] = $result['singer'];
        }
        else 
        {
            $res = $mMusic->isValid($url);
            if($res === true) {
                $urlInfo = $mMusic->getId3Info($url);
                $urlInfo['from'] = MUSIC_TYPE_OTHER;
                $urlInfo['url'] = $url;
            }
        }
        return $urlInfo;
    }
    
    /**
     * 
     * @param $url
     */
    public function isEventUrl($url) {
    	if(preg_match("/t.sina.com.cn\/event\/([0-9]+)$/",$url,$out)) {
    		$urlInfo['url'] = $url;
    		$urlInfo['eid'] = $out[1];
    	}else {
    		$urlInfo = false;
    	}
    	return $urlInfo;
    }    
}
?>
