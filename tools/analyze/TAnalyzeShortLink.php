<?php
class TAnalyzeShortLink{
	
	private $tinyurl = array('sinaurl.cn','t.cn','t.sina.com.cn','t.sina.cn');	 
	
	public function textToShortLink($str,$istarget=false){
		$target = $istarget ? ' target="_blank"' : '';
		$out = array();
		
		foreach ($this->tinyurl as $aUrl)
		{
			if(preg_match_all("/http:\/\/".$aUrl."([\/a-zA-Z0-9])*/i",$str,$out)){
				
				$shareStr = $out[0];
				$shareStr = array_unique($shareStr);
				foreach($shareStr as $key => $value){
					$r = "<a href='{$value}' {$target}>{$value}</a>";
					$str = str_replace($value,$r,$str);
				}
			}	
		}
		return $str;
	}
}
?>