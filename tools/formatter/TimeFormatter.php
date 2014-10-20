<?php
if($GLOBALS['LANGUAGE'] == 'zh-cn') {
define("TIME_FORMAT_MINITE", "%s分钟前");
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY','今天');
define('TIME_FORMAT_CAPTION_YESTODAY','昨天');
define('TIME_FORMAT_CAPTION_YEAR','年');
define('TIME_FORMAT_CAPTION_MONTH','月');
define('TIME_FORMAT_CAPTION_DAY','日');
define('TIME_FORMAT_CAPTION_HOUR','点');
define('TIME_FORMAT_CAPTION_MINITE','分');
define('TIME_FORMAT_CAPTION_SECOND','秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");
define('TIME_FORMAT_WHOLE',  "%s年%s月%s日 %s");
} elseif($GLOBALS['LANGUAGE'] == 'zh-tw') {
define("TIME_FORMAT_MINITE", "%s分鐘前");
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY','今天');
define('TIME_FORMAT_CAPTION_YESTODAY','昨天');
define('TIME_FORMAT_CAPTION_YEAR','年');
define('TIME_FORMAT_CAPTION_MONTH','月');
define('TIME_FORMAT_CAPTION_DAY','日');
define('TIME_FORMAT_CAPTION_HOUR','點');
define('TIME_FORMAT_CAPTION_MINITE','分');
define('TIME_FORMAT_CAPTION_SECOND','秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");
define('TIME_FORMAT_WHOLE',  "%s年%s月%s日  %s");
} else {
define("TIME_FORMAT_MINITE", "%s分钟前");
define("TIME_FORMAT_TODAY", "今天 %s");
define("TIME_FORMAT_YESTODAY", "昨天 %s");
define("TIME_FORMAT_HISTORY", "%s-%s-%s");
define("TIME_FORMAT_HISTORY_VISITOR", "%s月%s日");
define('TIME_FORMAT_CAPTION_TODAY','今天');
define('TIME_FORMAT_CAPTION_YESTODAY','昨天');
define('TIME_FORMAT_CAPTION_YEAR','年');
define('TIME_FORMAT_CAPTION_MONTH','月');
define('TIME_FORMAT_CAPTION_DAY','日');
define('TIME_FORMAT_CAPTION_HOUR','点');
define('TIME_FORMAT_CAPTION_MINITE','分');
define('TIME_FORMAT_CAPTION_SECOND','秒');
define('TIME_FORMAT_EVENT_NOYEAR', "%s月%s日 周%s %s");
define('TIME_FORMAT_EVENT_WITHYEAR', "%s年%s月%s日 周%s %s");
define('TIME_FORMAT_WHOLE',  "%s年%s月%s日  %s");
}

class TimeFormatter {
	public static function timeFormat($time) {
		$now = time();
		if (strpos($time,'-')!==false) {
			$time = strtotime($time);
		}
		if(($dur = $now - $time) < 3600) {
			$minutes = ceil($dur / 60);
			if ($minutes<=0){
				$minutes = 1;
			}
			$time = sprintf(TIME_FORMAT_MINITE, $minutes);
		}else
		if(date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		}else{
			if(date("Y") == date("Y",$time)){
				$time = sprintf(TIME_FORMAT_HISTORY_VISITOR,date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}else{
				$time = sprintf(TIME_FORMAT_HISTORY, date("Y",$time),date("n",$time),date("j",$time)) . " " . date("H:i",$time);
			}
		}
		return $time;
	}
	
	/**
	 * @author 张倚弛6328<yichi@staff.sina.com.cn>
	 * @param unknown_type $time
	 */
	public static function staticHtmlTimeFormat($time){
		$now = time();
		if (strpos($time,'-')!==false) {
			$time = strtotime($time);
		}
		$time = sprintf(TIME_FORMAT_WHOLE, date("Y",$time),date("n",$time), date("j",$time), date("H:i",$time));
	
		return $time;
	}

	public static function timeFormatGroup($time) {
		$now = time();
		if (strpos($time,'-')!==false) {
			$time = strtotime($time);
		}
		if(($dur = $now - $time) < 3600) {
			$minutes = ceil($dur / 60);
			if ($minutes<=0){
				$minutes = 1;
			}
			$time = sprintf(TIME_FORMAT_MINITE, $minutes);
		}else
		if(date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		}else{
			$time = sprintf('%s-%s %s:%s', date("m",$time),date("d",$time),date("H",$time),date("i",$time));
		}
		return $time;
	}
	public static function timeFormatVisitor($time) {
		$now = time();
		if(($dur = $now - $time) < 3600) {
			$minutes = ceil($dur / 60);
			if ($minutes<=0){
				$minutes = 1;
			}
			$time = sprintf(TIME_FORMAT_MINITE, $minutes);
		}else
		if(date("Ymd", $now) == date("Ymd", $time)) {
			$time = sprintf(TIME_FORMAT_TODAY, date("H:i", $time));
		}else{
			$time = sprintf(TIME_FORMAT_HISTORY_VISITOR, date("n",$time),date("j",$time));
		}
		return $time;
	}

	public static function timeFormatArr($time) {
		$retime = array();
		$now = time();
		if (strpos($time,'-')!==false) {
			$time = strtotime($time);
		}
		if(($dur = $now - $time) < 3600) {
			$minutes = ceil($dur / 60);
			if ($minutes<=0){
				$minutes = 1;
			}
			$retime['date'] = TIME_FORMAT_CAPTION_TODAY;
			$retime['time'] = sprintf(TIME_FORMAT_MINITE, $minutes);
		}else
		if(date("Ymd", $now) == date("Ymd", $time)) {
			$retime['date'] = TIME_FORMAT_CAPTION_TODAY;
			$retime['time'] = date("H:i", $time);
		}else{
			$retime['date'] = date("n", $time).TIME_FORMAT_CAPTION_MONTH.date("j", $time).TIME_FORMAT_CAPTION_DAY;
			$retime['time'] = date("H:i", $time);
		}
		return $retime;
	}
	
	public static function eventTimeFormat($start, $end) {
		$week = array(1=>"一",2=>"二",3=>"三",4=>"四",5=>"五",6=>"六",7=>"日");
		$sweek = date("N", $start);
		$eweek = date("N", $end);
		if(date("Ymd", $start) == date("Ymd", $end)) {//同一天
			$time = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start) . " - " . date("H:i", $end));
		}else 
		if(date("Y", $start) == date("Y", $end)) {//同一年不同天
			$stime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start));
			$etime = sprintf(TIME_FORMAT_EVENT_NOYEAR, date("n", $end), date("j", $end), $week[$eweek], date("H:i", $end));
			$time = $stime . " - " . $etime;
		}else {//不是同一年
			$stime = sprintf(TIME_FORMAT_EVENT_WITHYEAR, date("Y", $start), date("n", $start), date("j", $start), $week[$sweek], date("H:i", $start));
			$etime = sprintf(TIME_FORMAT_EVENT_WITHYEAR, date("Y", $end), date("n", $end), date("j", $end), $week[$eweek], date("H:i", $end));
			$time = $stime . " - " . $etime;
		}
		return $time;
	}
}

?>