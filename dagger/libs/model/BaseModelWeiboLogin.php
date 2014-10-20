<?php 

/*
 * How to use?
 *
 * $w = new BaseModelWeiboLogin( 'APP Key' );
 * $w->setUser( 'username' , 'password' );
 * print_r($w->public_timeline());
 *
 * send image
 * $w->upload( 'image test' , file_get_contents('http://tp4.sinaimg.cn/1088413295/180/1253254424') );
 *
*/


class BaseModelWeiboLogin
{
	function __construct( $akey , $skey = '' ) 
	{
		$this->akey = $akey;
		$this->skey = $skey;
		$this->base = 'http://api.t.sina.com.cn/';
		$this->curl = curl_init();
		curl_setopt( $this->curl , CURLOPT_RETURNTRANSFER, true); 
		
		$this->postInit();
		
	}
	
	function postInit()
	{
		$this->postdata = array('source=' . $this->akey);
	
	}

	function setUser( $name , $pass ) 
	{
            $this->user['oname'] = $name;
            $this->user['opass'] = $pass;

            //模拟登录获取cookie
            $cookie = '';
            $mc = new BaseModelMemcache;
            $cookie = $mc->get("sso_login_cookie_" . $name);
            if (empty($cookie)) {
                $ch = curl_init();
                $post_data_string = "service=sso&encoding=utf-8&gateway=1&savestate=30&useticket=0&username=" . rawurlencode($name) . "&password=" . rawurlencode($pass);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_URL, "http://login.sina.com.cn/sso/login.php");
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HEADER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data_string);
                $result = curl_exec($ch);
                preg_match_all('/Set-Cookie: (.*?=.*?;)./m', $result, $regs);
                foreach ($regs[1] as $v) {
                    $cookie .= $v;
                }
                curl_close($ch);
                $mc->set("sso_login_cookie_" . $name, $cookie, 3600);
            }
            curl_setopt($this->curl , CURLOPT_COOKIE , $cookie);
            //curl_setopt( $this->curl , CURLOPT_USERPWD , "$name:$pass" );
	}

	function public_timeline()
	{
		return $this->call_method( 'statuses' , 'public_timeline' );
	}
	
	function friends_timeline( $count = 20, $page = 1, $since_id = 0, $max_id = 0, $base_app = 0, $feature = 0 )
	{
        $others = '?count=' . $count . '&page=' . $page . '&since_id=' .$since_id . '&max_id=' .$max_id. '&feature=' . $feature . '&base_app=' . $base_app;
		return $this->call_method( 'statuses' , 'friends_timeline' , $others);
	}

	function friendships_exists($user_a, $user_b)
	{
		return $this->call_method( 'friendships' , 'exists', '?user_a='.urlencode($user_a).'&user_b='.urlencode($user_b) );
	}
	
	function user_id_timeline( $user_id , $since_id=0, $max_id=0, $count=20 , $page=1 , $feature=0 , $base_app=0 ) 
	{
        $others = '&count=' . $count . '&page=' . $page . '&feature=' . $feature . '&base_app=' . $base_app;
        if( $since_id > 0 ) { $others .= '&since_id='. $since_id; }
        if( $max_id > 0 ) { $others .= '&max_id=' . $max_id ; }
		return $this->call_method( 'statuses' , 'user_timeline' , '?user_id=' .  $user_id . $others );
	}
    
	function user_timeline( $name ) 
	{
		return $this->call_method( 'statuses' , 'user_timeline' , '?screen_name=' . urlencode( $name ) );
	}
	
	function mentions( $count = 10 , $page = 1 ) 
	{
		return $this->call_method( 'statuses' , 'mentions' , '?count=' . $count . '&page=' , $page  );
	}
	
	function comments_timeline( $count = 10 , $page = 1 )
	{
		return $this->call_method( 'statuses' , 'comments_timeline' , '?count=' . $count . '&page=' , $page  );
	}
	
	function comments_by_me( $count = 10 , $page = 1 )
	{
		return $this->call_method( 'statuses' , 'comments_by_me' , '?count=' . $count . '&page=' , $page  );
	}
	
	function comments( $tid , $count = 10 , $page = 1 )
	{
		return $this->call_method( 'statuses' , 'comments' , '?id=' . $tid . '&count=' . $count . '&page=' , $page  );
	}
	
	function counts( $tids )
	{
		return $this->call_method( 'statuses' , 'counts' , '?tids=' . $tids   );
	}
	
	function show( $tid )
	{
		return $this->call_method( 'statuses' , 'show/' . $tid  );
	}
	
		function destroy( $tid )
	{
	
		//curl_setopt( $this->curl , CURLOPT_CUSTOMREQUEST, "DELETE"); 		
		return $this->call_method( 'statuses' , 'destroy/' . $tid  );
	}
	
	
	function repost( $tid , $status )
	{
		$this->postdata[] = 'id=' . $tid;
		$this->postdata[] = 'status=' . urlencode($status);
		return $this->call_method( 'statuses' , 'repost'  );
	}
	
	
	function update( $status )
	{
		$this->postdata[] = 'status=' . urlencode($status);
		return $this->call_method( 'statuses' , 'update'  );
	}
	
	function upload( $status , $file )
	{
		
		$boundary = uniqid('------------------');
		$MPboundary = '--'.$boundary;
		$endMPboundary = $MPboundary. '--';
		
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody .= 'Content-Disposition: form-data; name="pic"; filename="wiki.jpg"'. "\r\n";
		$multipartbody .= 'Content-Type: image/jpg'. "\r\n\r\n";
		$multipartbody .= $file. "\r\n";

		$k = "source";
		// 杩欓噷鏀规垚 appkey
		$v = $this->akey;
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody.='content-disposition: form-data; name="'.$k."\"\r\n\r\n";
		$multipartbody.=$v."\r\n";
		
		$k = "status";
		$v = $status;
		$multipartbody .= $MPboundary . "\r\n";
		$multipartbody.='content-disposition: form-data; name="'.$k."\"\r\n\r\n";
		$multipartbody.=$v."\r\n";
		$multipartbody .= "\r\n". $endMPboundary;
		
		curl_setopt( $this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt( $this->curl , CURLOPT_POST, 1 );
		curl_setopt( $this->curl , CURLOPT_POSTFIELDS , $multipartbody );
		$url = 'http://api.t.sina.com.cn/statuses/upload.json' ;
		curl_setopt( $this->curl , CURLOPT_USERPWD , $this->user['oname'] . ":" . $this->user['opass'] );
		
		$header_array = array("Content-Type: multipart/form-data; boundary=$boundary" , "Expect: ");

        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $header_array ); 
        curl_setopt($this->curl, CURLOPT_URL, $url );
		curl_setopt($this->curl, CURLOPT_HEADER , true );
		curl_setopt($this->curl, CURLINFO_HEADER_OUT , true );
		
		$info = curl_exec( $this->curl );
		
		//print_r( curl_getinfo( $this->curl ) );
		
		return json_decode( $info , true);
		// =================================================
		
		
		
		
		//return $this->call_method( 'statuses' , 'upload'  );
	}
	
	function send_comment( $tid , $comment , $cid = '' )
	{
		$this->postdata[] = 'id=' . $tid;
		$this->postdata[] = 'comment=' . urlencode($comment);
		if( intval($cid) > 0 ) $this->postdata[] = 'cid=' . $cid;
		return $this->call_method( 'statuses' , 'comment'  );
	}
	
	function reply( $tid , $reply , $cid  )
	{
		$this->postdata[] = 'id=' . $tid;
		$this->postdata[] = 'comment=' . urlencode($comment);
		if( intval($cid) > 0 ) $this->postdata[] = 'cid=' . $cid;
		return $this->call_method( 'statuses' , 'comment'  );
	}
	
	function remove_comment( $cid )
	{
		return $this->call_method( 'statuses' , 'comment_destroy/'.$cid  );
	}
	
	// add favorites supports
	
	function get_favorites( $page = false ) 
    { 
        return $this->call_method( '' , 'favorites' , '?page=' . $page  );
    } 

    function add_to_favorites( $sid ) 
    { 
        $this->postdata[] = 'id=' . $sid;
        return $this->call_method( 'favorites' , 'create'   );
    } 

    function remove_from_favorites( $sid ) 
    { 
        $this->postdata[] = 'id=' . $sid;
        return $this->call_method( 'favorites' , 'destroy'   ); 
    } 
    
    // add account supports
    function verify_credentials() 
    { 
        return $this->call_method( 'account' , 'verify_credentials' );
    } 
    
    function search($q, $count = 200, $page = 1)
    {
        return $this->call_method( '' , 'search', '?q=' .urlencode($q) .'&rpp=' . $count . '&page=' .$page .'&source='. $this->akey );
    }


    //short url
    function short_url_batch_info($short_url){
        return $this->call_method( 'short_url' , 'batch_info' , '?url_short=' . $short_url );
    }


    //get id by mid
    function queryid($mid, $isBase62 = 1, $type = 1){
        return $this->call_method( '' , 'queryid' , '?mid=' . $mid .'&isBase62=' .$isBase62 .'&type=' . $type );
    }

    //get mid by id
    function querymid($id, $type = 1){
        return $this->call_method( '' , 'querymid' , '?id=' . $id .'&type=' . $type );
    }

	
	function call_method( $method , $action , $args = '' ) 
	{
		
		curl_setopt( $this->curl , CURLOPT_POSTFIELDS , join( '&' , $this->postdata ) );
		
		$url = $this->base . $method . '/' . $action . '.json' . $args ;
        BaseModelCommon::debug($url, 'api_url');
        BaseModelCommon::debug($this->postdata, 'api_post_data');

        curl_setopt($this->curl , CURLOPT_URL , $url );
		
		$ret = curl_exec( $this->curl );
		// clean post data
		$this->postInit();
		
		
		$data = json_decode( $ret , true);
        BaseModelCommon::debug($data, 'api_result');
        return $data;
		
	}
	
	function __destruct ()
	{
		curl_close($this->curl);
	}
	
	

	
	
	
	
	
	//function 
	
	
	

	
	
}
 ?>
