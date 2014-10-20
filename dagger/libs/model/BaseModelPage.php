<?php
/**
 * @Copyright (c) 2011, 新浪网运营部-网络应用开发部
 * All rights reserved.
 * 翻页逻辑基类
 * @author          wangxin <wangxin3@staff.sina.com.cn>
 * @time            2011/3/2 11:48
 * @version         Id: 0.9
*/

class BaseModelPage
{
    private $prePage;//上一页
    private $nextPage;//下一页
    private $firstPage = 1; //第一页
    private $lastPage;//最后一页
    private $pageStr;//翻页导航
    private $totalNum = 0;//总个数
    private $pageSize = 10;//每页显示几个
    private $link = '';//无参数链接
    private $paramStr = '';//页面参数
    private $totalPage =  0;//总页数
    private $page = 0;//当前页数
    private $limit = '';//SQL——limit
    private $style = 0;//翻页样式
    private $params = array();//参数数组

    public function __construct($totalNum, $pageSize, $params = array())
    {
        if (empty($pageSize)) {
            return false;
        }
        empty($params) && $params = $_GET;
        //基本数据计算
        $this->totalNum = intval($totalNum);
        $this->pageSize = intval($pageSize);
        $this->params = $params;
        $this->page = max($this->params['page'], 1);
		$this->totalPage = ceil($this->totalNum/$this->pageSize);
        $this->page = min($this->page, $this->totalPage);
        $this->prePage = max($this->page - 1, 1);//上一页
        $this->nextPage = min($this->page + 1, $this->totalPage);//下一页
        $this->lastPage = $this->totalPage;//最后一页
        //limit计算
		$this->page || $this->page = 1;
        $this->limit  = " LIMIT " . ($this->page -1) * $this->pageSize . ', ' . $this->pageSize;//用于 MySQL 分页生成语句
        //url参数计算
        $this->link = '';
        unset($this->params['page']);
        unset($this->params['dpc']);
        unset($this->params['_NOT_ICONV']);
        $this->paramStr = Router::createUrl('', '', $this->params);
        if(strpos($this->paramStr, '?') !== false){
            $this->paramStr .= '&';
        }else{
            $this->paramStr .= '?';
        }
    }

	/**
	 * 获取limit语句
	 */
	public function getLimit()
	{
		return $this->limit;
	}

    /**
     * 设置基本链接
     */
    public function setLink($link)
    {

        $this->link = $link;
    }

    /*
     * 设置style
     */
    public function setStyle($style)
    {
    	$this->style = $style;
    }

	/**
    /* 构造翻页字符串
    /*
    */
    public function getPageStr()
    {
        $tpl = new BaseView();
        $assign = array(
            'totalPage'=>$this->totalPage, 
            'pageSize'=>$this->pageSize,
            'prePage'=>$this->prePage,
            'nextPage'=>$this->nextPage,
            'firstPage'=>$this->firstPage,
            'lastPage'=>$this->lastPage,
            'totalNum'=>$this->totalNum, 
            'page'=>$this->page,
            'link'=>$this->link,
            'paramStr'=>$this->paramStr
        );
        switch($_REQUEST['format']){
            case 'xml':
            case 'json':
                return $assign;
                break;
            default:
                foreach($assign as $key=>$val){
                    $tpl->assign($key, $val);
                }
                $style = intval($this->style);
                return $tpl->fetch('page/style_' . $style . '.html');
        }
    }
    
    /**
    /* 构造翻页跳转input
    /*
    */
    public function getPageJump()
    {
        return "";
    }
}//end class
?>
