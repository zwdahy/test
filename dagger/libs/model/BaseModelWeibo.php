<?php 
/*
 * How to use?
 *
 * $w = new BaseModelWeibo( 'APP Key' );
 * print_r($w->public_timeline());
 *
 * send image
 * $w->upload( 'image test' , file_get_contents('http://tp4.sinaimg.cn/1088413295/180/1253254424') );
 *
*/

class BaseModelWeibo
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

	function public_timeline()
	{
		return $this->call_method( 'statuses' , 'public_timeline' );
	}
	
	function friends_timeline()
	{
		return $this->call_method( 'statuses' , 'friends_timeline' );
	}

	function friendships_exists($user_a, $user_b)
	{
		return $this->call_method( 'friendships' , 'exists', '?user_a='.urlencode($user_a).'&user_b='.urlencode($user_b) );
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
	
	function user_show( $user_id ) 
	{
		return $this->call_method( 'users' , 'show' , '?user_id='.$user_id );
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
        //		curl_setopt( $this->curl , CURLOPT_USERPWD , $this->user['oname'] . ":" . $this->user['opass'] );
        $cookie = "SUE=" . urlencode($_COOKIE['SUE']) . "; SUP=". urlencode($_COOKIE['SUP']);
        curl_setopt( $this->curl, CURLOPT_COOKIE, $cookie);    

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



    //short url
    function short_url($short_url){
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
        curl_setopt( $this->curl , CURLOPT_URL , $url );
        $cookie = "SUE=" . urlencode($_COOKIE['SUE']) . "; SUP=". urlencode($_COOKIE['SUP']);
        curl_setopt( $this->curl, CURLOPT_COOKIE, $cookie);    

        $ret = curl_exec( $this->curl );
        // clean post data
        $this->postInit();
        return json_decode( $ret , true);
    }

    function __destruct ()
    {
        curl_close($this->curl);
    }

    //function 
	
}
?>
