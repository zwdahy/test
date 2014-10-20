<?php

class Check {

	static function code2utf($num) {
	   if($num<128)return chr($num);
	   if($num<2048)return chr(($num>>6)+192).chr(($num&63)+128);
	   if($num<65536)return chr(($num>>12)+224).chr((($num>>6)&63)+128).chr(($num&63)+128);
	   if($num<2097152)return chr(($num>>18)+240).chr((($num>>12)&63)+128).chr((($num>>6)&63)+128) .chr(($num&63)+128);
	   return '';
	}

	static  public function getIp() {
		// Gets the default ip sent by the user
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			$step = 1;
			$direct_ip = $_SERVER['REMOTE_ADDR'];
		}
		
		// Gets the proxy ip sent by the user
		$proxy_ip     = '';
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$step = 2;
			$proxy_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
			$step = 3;
			$proxy_ip = $_SERVER['HTTP_X_FORWARDED'];
		} else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
			$step = 4;
			$proxy_ip = $_SERVER['HTTP_FORWARDED_FOR'];
		} else if (!empty($_SERVER['HTTP_FORWARDED'])) {
			$step = 5;
			$proxy_ip = $_SERVER['HTTP_FORWARDED'];
		} else if (!empty($_SERVER['HTTP_VIA'])) {
			$step = 6;
			$proxy_ip = $_SERVER['HTTP_VIA'];
		} else if (!empty($_SERVER['HTTP_X_COMING_FROM'])) {
			$step = 7;
			$proxy_ip = $_SERVER['HTTP_X_COMING_FROM'];
		} else if (!empty($_SERVER['HTTP_COMING_FROM'])) {
			$step = 8;
			$proxy_ip = $_SERVER['HTTP_COMING_FROM'];
		}
		
		// Returns the true IP if it has been found, else FALSE
		if (empty($proxy_ip)) {
			// True IP without proxy
			$ip = $direct_ip;
		} else {
			$is_ip = preg_match('|^([0-9]{1,3}\.){3,3}[0-9]{1,3}|', $proxy_ip, $regs);
			if ($is_ip && (count($regs) > 0)) {
				// True IP behind a proxy
				$ip = $regs[0];
			} else {
				// Can't define IP: there is a proxy but we don't have
				// information about the true IP
				$ip = $direct_ip;
			}
		}
		return $ip;
	}
	
	static public function ip2num($ip) {
		$temp_ip = explode(".", $ip);
		$ipnum = $temp_ip[0] * 256 * 256 * 256 + $temp_ip[1] * 256 * 256 + $temp_ip[2] * 256 + $temp_ip[3];
		return $ipnum;
	}
	
	/**
	 * IP访问限制检测
	 *
	 * @param int $conf
	 * @return boolean
	 * 符合要求:true/不符合:false
	 */
	public static function allow_visit($conf=0) {
		if($conf!=0) {
			$allow_ip_list = $GLOBALS['allow_ip_outside'];
		} else {
			$allow_ip_list = $GLOBALS['allow_ip'];
		}
		$ip = self::getIp();
		if ($ip == "") return true;
		while(list($k,$v) = each($allow_ip_list)) {
			if(ereg($v,$ip)) return true;			
		}
		return false;
	}

	/**
	 * 指定IP访问限制检测
	 * @author 刘焘<liutao3@staff.sina.com.cn>
	 * @param int $conf
	 * @param string $ipLink	// ip文件地址
	 * @return boolean
	 * 符合要求:true/不符合:false
	 */
	public static function allow_visit_ip($conf = 0, $ipLink) {
		include_once $ipLink;
		global $allow_ip, $allow_ip_outside;
		if($conf != 0) {
			$allow_ip_list = $allow_ip_outside;
		} else {
			$allow_ip_list = $allow_ip;
		}

		$ip = self::getIp();
		if($ip == '') return true;
		while(list($k, $v) = each($allow_ip_list)) {
			if(ereg($v, $ip)) return true;
		}
		return false;
	}

	/**
	 * 检查邮箱格式是否合法
	 *
	 * @param string $email
	 * @return boolean
	 * 合法:true/不合法:false
	 */
	public static function checkEmail($email) {
		if(strlen($email) < 1 || strlen($email) > 64) return false;
		//取出大小寫造成不能正確檢驗bug add by  張鵬飛
		$email = strtolower($email);
		return preg_match ('/^[0-9a-z_][-_\.0-9a-z-]{0,63}@([0-9a-z][0-9a-z-]*\.)+[a-z]{2,4}$/', $email );
		//邮箱部门的检测规则	'/^([a-z0-9]+[-|\.]?)+@([-_a-z0-9]{1,64}\.){1,7}[a-z0-9]{1,64}$/'
	}
	
	/**
	 * 检查手机格式是否合法
	 *
	 * @param integer $cellPhone
	 * @return boolean
	 * 合法:true/不合法:false
	 */
	public static function checkCellPhone($cellPhone) {
		return preg_match ( '/^1[0-9]{10}$/', $cellPhone );
	}
	
	/**
	 * 获取用户的token
	 *
	 * @param unknown_type $uid
	 */
	public static function getUserToken($uid){
		return md5('abct-45'.$uid.'c#9ba');
	}
	
	/**
	 * 检查mid的合法性
	 *
	 * @param bool
	 */
	public static function checkMid($mid){
		if($mid == ''){
			return false;
		}
		$mid_type = substr($mid,0,1);
		if($mid_type == 1){
			$month = intval(substr($mid,3,2));
			$day = intval(substr($mid,5,2));
			$id = substr($mid,7);
		}
		else{
			$month = intval(substr($mid,5,2));
			$day = intval(substr($mid,7,2));
			$id = substr($mid,9);
		}
		if($month<1 || $month>12){
			return false;
		}
		if($day<1 || $day>31){
			return false;
		}
		if(strlen($id)>10){
			return false;
		}
		return true;
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 将接收的微博客mid进行转换
	 * @param $mid 微博客mid
	 * @return array(array('mid'=>mid,'mid62'=>62位mid),)
	 */
	public static function mblogMidConvert($mid){
		$mblog= clsFactory::create ('libs/basic/tools', 'bBase62Parse' );
		$result = array();
		if(is_numeric($mid)){
			$result['mid'] = $mid;
			$result['mid62'] = $mblog->encode($mid);
		}else{
			$result['mid'] = $mblog->decode($mid);
			$result['mid62'] = $mid;
			}
		return $result;
	}
	/**
	 * @author 王占(wangzhan@staff.sina.com.cn)
	 * 将args中mid参数数组进行转换
	 * @param $mids array(mid=>array(mid))
	 * @return array()
	 */
	public function convertMids($mids){
		$midsConvert = array(); //经过转换后的微博客mid数组
		$result = array();
		foreach($mids as $val){
			if(!is_string($val) && !is_numeric($val)){
				return false;
			}
			$midsConvert[] = $this->mblogMidConvert($val);
		}
		foreach($midsConvert as $midConv){
			$result['midstr'][] = $midConv['mid'];
			$result['mid62str'][] = $midConv['mid62'];
		}
		return $result;
	}
	
	/**
	 * 检查referer，默认，只检查referer的HOST
	 *
	 * @param string $url
	 * @return true or false
	 */
	public function checkReferer($url='') {
		$referinfo = parse_url($_SERVER['HTTP_REFERER']);
		if($url==''){
			if(!preg_match('/^.+\.(sina|weibo)\.com+(\.cn)?$/', $referinfo['host'])) {
				return false;
			}
		} else {
			$urlinfo=parse_url($url);
			if(($referinfo['host']!=$urlinfo['host']) || ($referinfo['path']!=$urlinfo['path'])) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 检查uid是否合法
	 * 
	 * @author 刘勇刚<yonggang@staff.sina.com.cn>
	 * @date 2010-11-24
	 * 
	 * @param $str   用户uid
	 * @return true or false
	 */
	public static function uid($str) {
		return (is_numeric($str) && strlen($str) <=10 && strlen($str)>=5);
	}
	/**
	 * 检测字串是否是合法的date格式
	 * @param string $str date字串
	 * @param string $format 规定的格式
	 */
	public function isdate($str,$format="Y-m-d"){
		$strArr = explode("-",$str);
		if(empty($strArr)){
			return false;
		}
		foreach($strArr as $val){
			if(strlen($val)<2){
				$val="0".$val;
			}
			$newArr[]=$val;
		}
		$str =implode("-",$newArr);
		$unixTime=strtotime($str);
		$checkDate= date($format,$unixTime);
		if($checkDate==$str)
			return true;
		else
			return false;
	}


    /**
     *
     *检查时间段是否重叠, 时间点被忽略,  只有时间线才列入统计
     *@param Array $data   data中装着很多节目的信息, 每一个节目信息都有各自的begintime 和 endtime
     *@param string $begintime 09:30
     *@param string $endtime 09:30
     *@param int $max_overlap 默认为1  表示一段时间内只能存在一个节目
     *@Array $check_field data中的装有开始时间和结束时间的字段名称
     *return
     */
    public static function hasTimeConflict(Array $data, $program,$max_overlap=1, $check_field=array('begintime','endtime')){
        $conflicts = array();
        $timeline_programs = array();
        //初始化时间线,以分钟为颗粒度 start
        $timeline = array();
        for($hour=0; $hour<=23; $hour++){
            for($minute=0;$minute<=59;$minute++){
                $timeline[$hour][$minute] = 0;   
            }   
        }   
         $timeline;//复制一个timeline用来存放 这个点的节目
        //初始化时间线,以分钟为颗粒度 end
        if(!empty($program['program_id'])){//当前节目如果存在的话, 应该替换掉
            foreach($data as $k=> $v){
                if($v['program_id'] == $program['program_id']){
                    $data[$k] = $program;
                }
            }
        }else{
           $data[] = $program;
        }

        foreach($data as $k => $v){
            list($s_hour,$s_minute) =  explode(':',$v[$check_field[0]]);
            list($e_hour,$e_minute) =  explode(':',$v[$check_field[1]]);
            $s_hour = intval($s_hour);
            $s_minute = intval($s_minute);
            $e_hour = intval($e_hour);
            $e_minute = intval($e_minute);
            if($s_hour==$e_hour){
                for($minute=$s_minute;$minute<$e_minute;$minute++){
                    $timeline[$e_hour][$minute]++;
                    $timeline_programs[$e_hour][$minute][$k] = $k;  
                }
            }else{
                for($h = $s_hour;$h<=$e_hour;$h++){//开始看看设置的时间,都落在时间线的那个粒度里
                    if($h == $s_hour){
                        for($minute=$s_minute;$minute<=59;$minute++){
                            $timeline[$h][$minute]++;
                            $timeline_programs[$h][$minute][$k] = $k;  
                        }
                    }else if($h<$e_hour){
                        for($minute= 0;$minute<=59;$minute++){
                            $timeline[$h][$minute]++;
                            $timeline_programs[$h][$minute][$k] = $k;  
                        }
                    }else if($h==$e_hour && $e_minute != 0){
                        for($minute=0;$minute<$e_minute;$minute++){
                            $timeline[$h][$minute]++;
                            $timeline_programs[$h][$minute][$k] = $k;  
                        }
                    }
                    //$h should never be greater than $e_hour;
                }
            }
        }

        for($hour=0; $hour<=23; $hour++){
            for($minute=0;$minute<=59;$minute++){
                $current = $timeline[$hour][$minute]; 
                if($minute == 59){
                    if(!isset($timeline[$hour+1][0])) break;
                    $next =  $timeline[$hour+1][0]; 
                }else{
                    $next =  $timeline[$hour][$minute+1]; 
                }
                //after_next    minute==58  then hour+1, minute==59  then hour+1 and minute=1   if hour = 23

                if($minute == 58){
                    if(!isset($timeline[$hour+1][0])) break;
                    $after_next = $timeline[$hour+1][0];
                }else if($minute == 59){
                    if(!isset($timeline[$hour+1][1])) break;
                    $after_next = $timeline[$hour+1][1];
                }else {
                    $after_next = $timeline[$hour][$minute+2];
                }

                //if( isset($current) && isset($next) && isset($after_next)  && $current >1 && $next >1 && $after_next>1 ){
                //        $conflicts[$hour][$minute] = 1;
                //}

                if( isset($current) && $current >1){
                        $conflicts[$hour][$minute] = 1;
                }
            }   
        }   

        $conflict_data = array();
        foreach($conflicts as $h => $mdata){
            foreach($mdata as  $m => $aaaa){
                $conflict_data[md5(serialize($timeline_programs[$h][$m]))] = ($timeline_programs[$h][$m]);
            }
        }
        $tmp = array();
        foreach($conflict_data as $md5 => $v){
            foreach($v as $k){
                $tmp[$md5][] = $data[$k];
            }
        }
        return $tmp;
    }
    /**
     * 判断中英文混合字符串的长度, 两个英文字符按1个处理
     *@param str subject
     *@return int
     */
    public static function utf8_strlen($subject){
        $pattern = "/[".chr(228).chr(128).chr(128)."-".chr(233).chr(191).chr(191)."]/";
        preg_match_all($pattern, $subject, $matches);
        $subject = preg_replace($pattern, '', $subject );
        return mb_strlen($subject)/2 + count($matches[0])/3;
    }   
    public static function remove_xss($val) {
        // remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed  
        // this prevents some character re-spacing such as <java\0script>  
        // note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs  
        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);  

        // straight replacements, the user should never need these since they're normal characters  
        // this prevents like <IMG SRC=@avascript:alert('XSS')>  
        $search = 'abcdefghijklmnopqrstuvwxyz'; 
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
        $search .= '1234567890!@#$%^&*()'; 
        $search .= ',~`";:?+/={}[]-_|\'\\'; 
        for ($i = 0; $i < strlen($search); $i++) { 
            // ;? matches the ;, which is optional 
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 

            // @ @ search for the hex values 
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 
            // @ @ 0{0,7} matches '0' zero to seven times  
            $val = preg_replace('/({0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
        } 

        // now the only remaining whitespace attacks are \t, \n, and \r 
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base'); 
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
        $ra = array_merge($ra1, $ra2); 

        $found = true; // keep replacing as long as the previous round replaced something 
        while ($found == true) { 
            $val_before = $val; 
            for ($i = 0; $i < sizeof($ra); $i++) { 
                $pattern = '/'; 
                for ($j = 0; $j < strlen($ra[$i]); $j++) { 
                    if ($j > 0) { 
                        $pattern .= '(';  
                        $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
                        $pattern .= '|';  
                        $pattern .= '|({0,8}([9|10|13]);)'; 
                        $pattern .= ')*'; 
                    } 
                    $pattern .= $ra[$i][$j]; 
                } 
                $pattern .= '/i';  
                $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag  
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags  
                if ($val_before == $val) {  
                    // no replacements were made, so exit the loop  
                    $found = false;  
                }  
            }  
        }  
        return $val;  
    }

    public static function create_token(){
        setcookie('post_token', md5('yohohohohohohoh'));
    }
    public static function is_token_expired(){
        if(empty($_COOKIE['post_token'])){
            return true;
        }
        return false;
    }
    public static function delete_token(){
        setcookie("post_token","",time()-360000);
    }

}
?>
