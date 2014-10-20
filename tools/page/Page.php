<?php
class Page
{
	var $totalnum;
	var $pageRecNum;
	var $pagenum;
	var $url;
	var $pageDate;
	/*
	 * $pageRecNum  每页条目数
	 * $pagenum 当前页码数
	 * $url 页码前面的url
	 * $totalnum 总条目数量
	 * $page 输出数组
	 */
	function page($pageRecNum, $pagenum, $url, $totalnum, & $page, $ajax_url)
	{
		$this->pageRecNum = $pageRecNum;
		//echo 'page'.$pagenum;
		$this->pagenum = $pagenum;
		if(empty($ajax_url)){
			$this->url = $url;
			if(substr($this->url,0,1) == '?')
			{
				$this->url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].$this->url;
			}
		}
		else{
			$this->url = 'http://'.$_SERVER['HTTP_HOST'].'/'.$ajax_url;
		}
		if($totalnum==='')
		{
			$totalnum = 0;
		}
		$this->totalnum = $totalnum;
		$this->getPageData();
		$page = $this->get_page_data();
	}

	function getPageData()
	{
		if ($this->totalnum === '')
		{
			echo '请输入总数';
			return false;
		}
		//echo $this->totalnum;
		$page_count = 1;
		if ($this->totalnum)
		{
			if ($this->totalnum < $this->pageRecNum)
			{
				$page_count = 1;
			}
			else
				if ($this->totalnum % $this->pageRecNum)
				{
					$page_count = (int) ($this->totalnum / $this->pageRecNum) + 1;
				}
				else
				{
					$page_count = $this->totalnum / $this->pageRecNum;
				}
		}
		if ($this->pagenum <= 1)
		{
			$this->pagenum = 1;
			$this->pageDate['firstpage'] = $_SERVER['REQUEST_URI'].'#';
			$this->pageDate['previouspage'] = $_SERVER['REQUEST_URI'].'#';
		}
		else
		{
			$this->pageDate['firstpage'] = $this->url . '1';
			$this->pageDate['previouspage'] = $this->url . ($this->pagenum - 1);
		}
		if (($this->pagenum >= $page_count) || ($page_count == 0))
		{
			$this->pagenum = $page_count;
			$this->pageDate['nextpage'] = $_SERVER['REQUEST_URI'].'#';
			$this->pageDate['lastpage'] = $_SERVER['REQUEST_URI'].'#';
		}
		else
		{
			$this->pageDate['nextpage'] = $this->url . ($this->pagenum + 1);
			$this->pageDate['lastpage'] = $this->url . $page_count;
		}
		$this->pageDate['totalpage'] = $page_count;
		$this->pageDate['pagenum'] = $this->pagenum;

		$this->pageDate['from'] = ($this->pagenum - 1) * $this->pageRecNum + 1;
		if ($this->totalnum == 0)
		{
			$this->pageDate['from'] = 0;
		}
		if ($this->pagenum * $this->pageRecNum > $this->totalnum)
		{
			$this->pageDate['to'] = $this->totalnum;
		}
		else
		{
			$this->pageDate['to'] = ($this->pagenum) * $this->pageRecNum;
		}
		$this->pageDate['totalnum'] = $this->totalnum;
		$this->pageDate['pageRecNum'] = $this->pageRecNum;
		$this->pageDate['pageurl'] = $this->url;

	}
	function get_page_data()
	{
		return $this->pageDate;
	}
	function set_totalnum($totalnum)
	{
		$this->totalnum = $totalnum;
		//echo 'totalnum'.$this->totalnum;
	}

	function getpagelist($listnum, &$pagelist)
	{
		$pagelist = array ();
		$a = intval(($this->pagenum - 1) / $listnum);
		if (($a * $listnum +1) > $this->pageDate['totalpage'])
		{
			return;
		}
		if (($a+1) * $listnum > $this->pageDate['totalpage'])
		{
			$end = $this->pageDate['totalpage'];
		}
		else
		{
			$end = ($a+1) * $listnum;
		}
		
		for ($i = $a * $listnum +1; $i <= $end; $i++)
		{
			$pagelist[$i]['num'] = $i;
			$pagelist[$i]['url'] = $this->url . $i;
		}
	}

	/*
	listnum  显示页码数, 默认展示11页
	*/
	function getpagelist_v3(&$pagelist, $listnum=7, $omimark="...")
	{
		$pagelist = array ();
		$begin = $last = array();
		
		$rim_num = floor($listnum/2)+1;	

		if(($this->pagenum>$rim_num && $this->pageDate['totalpage'] > $listnum) && ($this->pageDate['totalpage']-$this->pagenum>$rim_num))						// 两头的...都存在时
		{
			$begin[] = array("num"=>1, "url"=>$this->url . "1");
			$begin[] = array("num"=>$omimark, "url"=>"");
			$last[] = array("num"=>$omimark, "url"=>"");
			$last[] = array("num"=>$this->pageDate['totalpage'], "url"=>$this->url . $this->pageDate['totalpage']);

			$firstpage = $this->pagenum - $rim_num + 2;
			$endpage = $this->pagenum + $rim_num -2;
		}
		elseif($this->pagenum>$rim_num && $this->pageDate['totalpage'] > $listnum)	// 只有开头的...时
		{
			$begin[] = array("num"=>1, "url"=>$this->url . "1");
			$begin[] = array("num"=>$omimark, "url"=>"");
			
			$firstpage = $this->pageDate['totalpage']-$listnum+2;
			$endpage = $this->pageDate['totalpage'];
		}
		elseif($this->pageDate['totalpage']-$this->pagenum>$rim_num && $this->pageDate['totalpage'] > $listnum)	// 只有结尾的...时
		{
			$last[] = array("num"=>$omimark, "url"=>"");
			$last[] = array("num"=>$this->pageDate['totalpage'], "url"=>$this->url . $this->pageDate['totalpage']);

			$firstpage = 1;
			$endpage = $listnum-1;
		}
		else	// 没有...时
		{
			$firstpage = 1;
			$endpage = $this->pageDate['totalpage'];
		}

		for ($i = $firstpage; $i <= $endpage; $i++)
		{
			$pagelist[$i]['num'] = $i;
			$pagelist[$i]['url'] = $this->url . $i;
		}

		$pagelist = array_merge($begin, $pagelist, $last);
	}
}
?>