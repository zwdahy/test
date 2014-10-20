<?php
//!defined('RADIO_ENV') && define('RADIO_ENV', 'dev');
!defined('RADIO_ENV') && define('RADIO_ENV', 'product');
define('RADIO_APP_SOURCE', 'srv.sapps.city');

define('RADIO_APP_USER','weidiantai@sina.cn');
define('RADIO_APP_PASS','weidiantai0000');

define('RADIO_OFFICIAL_UID','2183483187');			//微电台官方微博ID
define('USR_ACTION_LOGADDR','/data1/www/applogs/service.t.sina.com.cn/radio/%s');  //行为日志记录地址
define('USR_ACTION_LOGADDR_NEW','/data1/www/applogs/radio.weibo.com/%s');
define('RADIO_TITLE', '%s | 微电台');  //页面title
define('RADIO_ICON_PATH', GLOBAL_CSS_PATH_V3.'/images/fmradio/radio_pic.jpg'); //微博电台图标地址
define('RADIO_ICON_PID', '6055df12tw1do15aiuchfj');  //微博电台图标pid
define('RADIO_MBLOG_SOURCE', '微电台 - %s');   //发微薄来源
define('RADIO_ADMIN_UID',1797392503);        //搜索微博uid
define('RADIO_CUT_URL','http://fms.sinaimg.cn/');		//音频切片地址
define('RADIO_PIC_PATH','http://ww2.sinaimg.cn/square/'); //图片地址前缀
define('RADIO_APP_PIC_PATH','http://www.sinaimg.cn/dy/deco/2014/0711/images/download.jpg'); //微电台图片


//静态文件路径
if(RADIO_ENV == 'dev'){
	define('RADIO_JS_PATH_V2', 'http://js.com/js/lib/require/'); //js脚本
    define('RADIO_JS_PATH', 'http://js.com/js/'); //js脚本
    define('RADIO_CSS_PATH', 'http://js.com/asset/css/'); //css样式
	define('RADIO_IMG_PATH', "http://js.com/asset/images/"); //图片
}else{
	define('RADIO_JS_PATH_V2', 'http://news.sina.com.cn/js/792/2014-07-11/118/'); //js脚本
    define('RADIO_JS_PATH', 'http://news.sina.com.cn/js/792/2014-07-11/116/'); //js脚本
//    //define('RADIO_STATIC_PATH', "http://data.mix.sina.com.cn/js/radio/old_radio/static/"); //不压缩的文件
	define('RADIO_CSS_PATH', 'http://news.sina.com.cn/css/792/2014-07-11/119/'); //css样式
	//define('RADIO_IMG_PATH', "http://image2.sina.com.cn/music/radio/1.2.4/images/"); //图片
	define('RADIO_IMG_PATH', "http://www.sinaimg.cn/dy/deco/2014/0711/images/"); //图片
}

define('RADIO_STATIC_PATH', "http://image2.sina.com.cn/music/radio/1.2.4/static/"); //不压缩的文件

define('RADIO_SOURCE_APP_ID', 68);
define('RADIO_SOURCE_APP_KEY',1658340743);


define('RADIO_SHARE_CONTENT', '我正在微电台收听——%s的节目#{pname},'.RADIO_URL.'/%s/%s');  //分享内容
define('RADIO_SHARE_URL', 'http://' . SERVICE_DOMAIN . '/share/share.php?');  //分享跳转
define('RADIO_RADIO_LISTENERS_LIST', 'http://data.i.t.sina.com.cn/radio/getradiouidlist.php?rid=%s&pagesize=%s&page=%s');  //获取正在收听list
define('RADIO_RADIO_HOT_PROGRAM', 'http://api.data.sina.com.cn/servlet/defaultDispatcher?__action=general.vradio_pname_litsen_daily_weekly&date_begin=%s&date_end=%s&type=%s&__pageSize=%s&__currentPage=%s');  //获取热门节目单
define('RADIO_TOP10','http://api.data.sina.com.cn/servlet/defaultDispatcher?__action=general.vradio_rid_litsen_daily&day_key=%s&__pageSize=%s&__currentPage=%s');	//电台收听排行榜
define('RADIO_COLLECTION_TOP10','http://data.i.t.sina.com.cn/radio/getfavoritestop10.php');	//电台收藏排行榜
define('RADIO_INFLUENCE_RANK','http://i.data.weibo.com/top/ranks/influence_list?depart=0002&class=1692&type=%s&page=%s');//电台影响力排行榜
define('RADIO_CHECK_KEYWORD','http://i.admin.weibo.com/admin/keyword/keyword.json');	//验证输入内容是否合法
//更新短链接口地址
define('RADIO_SHORT_URL_UPDATE', 'http://i2.api.weibo.com/sinaurl/secure/update.json?url=%s');  

define('RADIO_PAGESIZE', 3); //页面显示的评论列表数
define('RADIO_PAGENUM', 6); //实际获取的评论列表数量

define('RADIO_INTERVAL', 30);  //行为日志打点时间

//feedlist页面信息
define('RADIO_FEEDLIST_PAGESIZE',40);	//feed每页显示条数
//define('RADIO_FEEDLIST_MAXPAGE',10);	//feed最大显示页数
//define('RADIO_FEEDLIST_MAXNUMS',400);	//feed最大显示条数
define('RADIO_FEEDLIST_MAXPAGE',5);	//feed最大显示页数
define('RADIO_FEEDLIST_MAXNUMS',200);	//feed最大显示条数
//dj feedlist页面信息
define('RADIO_DJ_FEEDLIST_PAGESIZE',40);	//feed每页显示条数
//define('RADIO_DJ_FEEDLIST_MAXPAGE',10);		//feed最大显示页数
//define('RADIO_DJ_FEEDLIST_MAXNUMS',400);	//feed最大显示条数
define('RADIO_DJ_FEEDLIST_MAXPAGE',5);		//feed最大显示页数
define('RADIO_DJ_FEEDLIST_MAXNUMS',200);	//feed最大显示条数
//@test
define('RADIO_COLLECTION_MAX',20);		//用户最大收藏电台数
define('RADIO_NEW_INTERVAL',48*3600);	//电台新标签显示时间范围（48小时）

define('RADIO_ENVIRONMENT',1);		//环境1为线上，其他调试环境请修改此变量为2.
//define('RADIO_ENVIRONMENT',2);		//环境1为线上，其他调试环境请修改此变量为2.
if(RADIO_ENVIRONMENT == 1){
	define('CACHE_RADIO_POSTFIX','_main');
}
else{
	define('CACHE_RADIO_POSTFIX','_widget');
}
//电台信息缓存key
define('MC_KEY_RADIO_LIST', CACHE_RADIO.'_radio_list'.CACHE_RADIO_POSTFIX);   //电台列表缓存	按省份分了 找机会可以干了 涉及较广 慢慢干
define('MC_KEY_RADIO_LIST_V2', CACHE_RADIO.'_radio_list_v2'.CACHE_RADIO_POSTFIX);   //电台列表缓存 
define('MC_KEY_RADIO_LIST_BY_PID', CACHE_RADIO.'_radio_list_by_pid_%s'.CACHE_RADIO_POSTFIX);   //电台列表(按地区)缓存
define('MC_KEY_RADIO_LIST_BY_CID', CACHE_RADIO.'_radio_list_by_cid_%s'.CACHE_RADIO_POSTFIX);   //电台列表(按分类)缓存
define('MC_KEY_RADIO_LIST_SORT', CACHE_RADIO.'_radio_list_by_cid_%s_pid_%s'.CACHE_RADIO_POSTFIX);   //电台列表(按分类和地区)缓存
define('MC_KEY_RADIO_BY_UID', CACHE_RADIO.'_radio_info_uid_%s'.CACHE_RADIO_POSTFIX); //单个电台详情缓存（按官方微博uid）
define('MC_KEY_RADIO_BY_RID', CACHE_RADIO.'_radio_info_rid_%s'.CACHE_RADIO_POSTFIX); //单个电台详情缓存
define('MC_KEY_RADIO_BY_DOMAIN_PROVINCE', CACHE_RADIO.'_radio_info_d_%s_p_%s'.CACHE_RADIO_POSTFIX); //单个电台详情缓存（按电台domain和地区拼音）
define('MC_KEY_RADIO_BY_DOMAIN_PROVINCE_ID', CACHE_RADIO.'_radio_info_domain_%s_pid_%s'.CACHE_RADIO_POSTFIX); //单个电台详情缓存（按电台domain和地区拼音）
define('MC_KEY_RADIO_ALL_ONLINE_LIST', CACHE_RADIO.'_radio_all_online_list'.CACHE_RADIO_POSTFIX);   //提供给搜索的接口电台列表缓存
//电台信息缓存时间
define('MC_TIME_RADIO_LIST', 7200);         //电台列表缓存时间
define('MC_TIME_RADIO_LIST_BY_PID', 7200);
define('MC_TIME_RADIO_LIST_BY_CID', 600);
define('MC_TIME_RADIO_LIST_SORT', 600);
define('MC_TIME_RADIO_BY_UID', 3600);		//单个电台详情缓存时间（按官方微博uid）
define('MC_TIME_RADIO_BY_RID', 3600);        //单个电台详情缓存时间（按电台id）
define('MC_TIME_RADIO_BY_DOMAIN_PROVINCE', 86400);        //单个电台详情缓存时间（按电台domain和地区拼音）
define('MC_TIME_RADIO_ALL_ONLINE_LIST', 600);         //提供给搜索接口-电台列表缓存时间
//电台黑名单缓存key
define('MC_KEY_RADIO_RANK_BLACKLIST', CACHE_RADIO.'_radio_rank_blacklist'.CACHE_RADIO_POSTFIX);   //电台排行榜黑名单列表
define('MC_KEY_RADIO_RANK_BLACKLIST_UID', CACHE_RADIO.'_radio_rank_blacklist_uid_%s'.CACHE_RADIO_POSTFIX);   //电台排行榜黑名单列表（按用户id）
//电台黑名单缓存时间
define('MC_TIME_RADIO_RANK_BLACKLIST', 86400);         //电台排行榜黑名单列表缓存时间
define('MC_TIME_RADIO_RANK_BLACKLIST_UID', 86400);         //电台排行榜黑名单列表缓存时间（按用户id）
//FEED缓存key
define('MC_KEY_RADIO_FEEDLIST_PAGE', CACHE_RADIO.'_radio_feedlist_%s_page_%s'.CACHE_RADIO_POSTFIX); //电台feedlist列表（分页）缓存
define('MC_KEY_RADIO_PROGRAM_FEEDLIST_PAGE', CACHE_RADIO.'_radio_program_feedlist_%s_page_%s'.CACHE_RADIO_POSTFIX); //节目feedlist列表（分页）缓存
//define('MC_KEY_RADIO_PROGRAM_TOPIC', CACHE_RADIO.'_radio_program_topic'.CACHE_RADIO_POSTFIX); //节目话题缓存
define('MC_KEY_RADIO_DJ_FEEDLIST_PAGE', CACHE_RADIO.'_radio_dj_feedlist_%s_page_%s'.CACHE_RADIO_POSTFIX); //电台dj feedlist列表（分页）缓存
//define('MC_KEY_RADIO_DJ_FEED', CACHE_RADIO.'_radio_dj_feed_%s_%s'.CACHE_RADIO_POSTFIX);	//电台在线dj的feed
define('MC_KEY_RADIO_DJ_FEED', CACHE_RADIO.'_radio_online_dj_feed_%s'.CACHE_RADIO_POSTFIX);	//电台在线dj的feed
//FEED缓存时间
define('MC_TIME_RADIO_FEEDLIST_PAGE', 86400);     //feedlist分页缓存时间（1天）
define('MC_TIME_RADIO_DJ_FEEDLIST_PAGE', 86400);     //dj feedlist分页缓存时间（1天）
//电台dj缓存key
define('MC_KEY_RADIO_DJ', CACHE_RADIO.'_radio_dj_%s'.CACHE_RADIO_POSTFIX);  //电台dj缓存（简版）
define('MC_KEY_RADIO_DJ_INFO', CACHE_RADIO.'_radio_dj_info_%s'.CACHE_RADIO_POSTFIX);  //电台dj_info缓存
//电台dj缓存时间
define('MC_TIME_RADIO_DJ', 600);           //电台推荐主持人缓存时间
define('MC_TIME_RADIO_DJ_INFO', 86400);           //电台推荐主持人缓存时间
//电台地区缓存key
define('MC_KEY_RADIO_AREA',CACHE_RADIO.'_radio_area'.CACHE_RADIO_POSTFIX);	//电台地区分类信息
//电台地区缓存时间
define('MC_TIME_RADIO_AREA',600);			//地区列表缓存时间
define('MC_KEY_RADIO_CLASSIFICATION',CACHE_RADIO.'_radio_classification'.CACHE_RADIO_POSTFIX);	//电台分类信息
//电台地区缓存时间
define('MC_TIME_RADIO_CLASSIFICATION',600);			//地区列表缓存时间
//用户信息缓存key
define('MC_KEY_RADIO_USERINFO', CACHE_RADIO . '_userinfo_%s'.CACHE_RADIO_POSTFIX); //微博客用户信息缓存key
define('MC_KEY_RADIO_USERINFO_NAME', CACHE_RADIO . '_userinfo_by_name_%s'.CACHE_RADIO_POSTFIX); //微博客用户信息缓存key（按用户名）
//用户信息缓存时间
define('MC_TIME_RADIO_USERINFO',600);      //微博用户信息缓存时间
define('MC_TIME_RADIO_USERINFO_NAME',600);      //微博用户信息缓存时间
//用户微博信息缓存key
define('MC_KEY_RADIO_MBLOG', CACHE_RADIO . '_%s'.CACHE_RADIO_POSTFIX); //微博内容缓存key
//用户微博缓存时间
define('MC_TIME_RADIO_MBLOG', 86400);        //微博信息缓存时间

define('MC_KEY_RADIO_NEWS_INDEX', CACHE_RADIO . '_radio_news_index'.CACHE_RADIO_POSTFIX); //首页新闻缓存key
//用户微博缓存时间
define('MC_TIME_RADIO_NEWS_INDEX', 86400);        //首页新闻缓存时间
define('MC_KEY_RADIO_NEWS_PAGE', CACHE_RADIO.'__radio_news_page_%s'.CACHE_RADIO_POSTFIX); //新闻列表页分页数据
define('MC_TIME_RADIO_NEWS_PAGE', 86400);        //首页新闻缓存时间
define('MC_KEY_RADIO_NEWS_NUM', CACHE_RADIO.'__radio_news_num'.CACHE_RADIO_POSTFIX); //列表页新闻总数
define('MC_TIME_RADIO_NEWS_NUM', 600);        //列表页新闻总数缓存时间


//排行榜缓存key
define('MC_KEY_RADIO_TOP10', CACHE_RADIO.'_radio_top10'.CACHE_RADIO_POSTFIX);   //电台收听排行榜列表缓存
define('MC_KEY_RADIO_PROGRAM_TOP10', CACHE_RADIO.'_radio_program_top10'.CACHE_RADIO_POSTFIX);   //电台节目收听排行榜列表缓存
define('MC_KEY_RADIO_LISTEN_RANK', CACHE_RADIO.'_radio_listen_rank'.CACHE_RADIO_POSTFIX);   //电台收听排行榜列表缓存
define('MC_KEY_RADIO_LISTEN_RANK_PID', CACHE_RADIO.'_radio_listen_rank_pid_%s_%s'.CACHE_RADIO_POSTFIX);   //电台收听排行榜列表缓存(按地区)
define('MC_KEY_RADIO_LISTEN_RANK_PID_V2', CACHE_RADIO.'_radio_listen_rank_pid_v2_%s'.CACHE_RADIO_POSTFIX);   //电台收听排行榜列表缓存(按地区)
define('MC_KEY_RADIO_HOT_RADIO',CACHE_RADIO.'_hot_radio'.CACHE_RADIO_POSTFIX);  //热门电台列表
define('MC_KEY_RADIO_COLLECTION_TOP10', CACHE_RADIO.'_radio_collection_top10'.CACHE_RADIO_POSTFIX);   //电台收藏排行榜列表缓存
define('MC_KEY_RADIO_LISTENERS', CACHE_RADIO.'_radio_listeners_%s'.CACHE_RADIO_POSTFIX);  //正在收听list
define('MC_KEY_RADIO_LISTENERS2', CACHE_RADIO.'_radio_listeners2_%s'.CACHE_RADIO_POSTFIX);  //正在收听list
define('MC_KEY_RADIO_HOT_PROGRAM_DAY_KEY',CACHE_RADIO.'_hprogram_day_key'.CACHE_RADIO_POSTFIX);  //当天热门节目 存放mc key数组
define('MC_KEY_RADIO_HOT_PROGRAM_DAY',CACHE_RADIO.'_hot_program_day_%s'.CACHE_RADIO_POSTFIX);  //当天热门节目 根据键值获取值
define('MC_KEY_RADIO_HOT_PROGRAM_DAY_KEY_V2',CACHE_RADIO.'_hprogram_day_key_v2'.CACHE_RADIO_POSTFIX);  //当天热门节目 存放mc key数组
define('MC_KEY_RADIO_HOT_PROGRAM_DAY_V2',CACHE_RADIO.'_hot_program_day_v2_%s'.CACHE_RADIO_POSTFIX);  //当天热门节目 根据键值获取值
//define('MC_KEY_RADIO_HOT_PROGRAM',CACHE_RADIO.'_hprogram'.CACHE_RADIO_POSTFIX);  //热门节目排行榜
define('MC_KEY_RADIO_HOT_PROGRAM_PID',CACHE_RADIO.'_hprogram_pid_%s'.CACHE_RADIO_POSTFIX);  //热门节目排行榜
define('MC_KEY_RADIO_INFLUENCE_RANK_DAY',CACHE_RADIO.'_influence_rank_day'.CACHE_RADIO_POSTFIX);  //影响力排行榜（日榜）
define('MC_KEY_RADIO_INFLUENCE_RANK_WEEK',CACHE_RADIO.'_influence_rank_week'.CACHE_RADIO_POSTFIX);  //影响力排行榜（周榜）
define('MC_KEY_RADIO_INFLUENCE_RANK_MONTH',CACHE_RADIO.'_influence_rank_month'.CACHE_RADIO_POSTFIX);  //影响力排行榜（月榜）
define('MC_KEY_RADIO_ACTIVE_USER_RANK', CACHE_RADIO.'_radio_active_user_rank'.CACHE_RADIO_POSTFIX);		//用户活跃榜缓存key
define('MC_KEY_RADIO_ACTIVE_DJ_RANK', CACHE_RADIO.'_radio_active_dj_rank'.CACHE_RADIO_POSTFIX);		//DJ活跃榜缓存key

//排行榜缓存时间
define('MC_TIME_RADIO_TOP10', 86400);     //电台收听排行榜缓存时间（1天）
define('MC_TIME_RADIO_LISTEN_RANK', 86400);     //电台收听排行榜缓存时间（1天）
define('MC_TIME_RADIO_LISTEN_RANK_PID', 86400);     //电台收听排行榜缓存时间（1天）
define('MC_TIME_RADIO_HOT_RADIO',86400);  //热门电台列表缓存时间
define('MC_TIME_RADIO_LISTENERS', 86400);    //电台推荐收听人缓存时间
define('MC_TIME_RADIO_COLLECTION_TOP10', 86400);     //电台收听排行榜缓存时间（1天）
define('MC_TIME_RADIO_HOT_PROGRAM_DAY',86400);  //当天热门节目缓存时间
//define('MC_TIME_RADIO_HOT_PROGRAM',86400);  //热门节目缓存时间
define('MC_TIME_RADIO_HOT_PROGRAM_PID',86400);  //热门节目缓存时间
define('MC_TIME_RADIO_INFLUENCE_RANK_DAY',86400);  ////影响力排行榜（日榜）缓存时间
define('MC_TIME_RADIO_INFLUENCE_RANK_WEEK',86400);  ////影响力排行榜（周榜）缓存时间
define('MC_TIME_RADIO_INFLUENCE_RANK_MONTH',86400);  ////影响力排行榜（月榜）缓存时间
define('MC_TIME_RADIO_ACTIVE_USER_RANK', 86400);   //微电台用户活跃榜缓存时间
define('MC_TIME_RADIO_ACTIVE_DJ_RANK', 86400);   //DJ活跃榜缓存时间

//搜索页缓存
define('MC_KEY_RADIO_SEARCH_TYPE_KEY_PAGE', CACHE_RADIO.'_radio_search_type_%s_key_%s_page_%s'.CACHE_RADIO_POSTFIX);		//用户收藏信息

//用户收藏电台缓存key
define('MC_KEY_RADIO_COLLECTION_RIDS', CACHE_RADIO.'_radio_collection_rids_%s'.CACHE_RADIO_POSTFIX);		//用户收藏信息
//用户收藏电台缓存时间
define('MC_TIME_RADIO_COLLECTION_RIDS',60);			//收藏电台id缓存时间
//公告缓存key
define('MC_KEY_RADIO_NOTICE', CACHE_RADIO.'_radio_notice_%s'.CACHE_RADIO_POSTFIX);		//电台公告信息
//公告缓存时间
define('MC_TIME_RADIO_NOTICE',600);			//电台公告缓存时间
//7天回看缓存key
define('MC_KEY_RADIO_SEEK', CACHE_RADIO.'_radio_seek_day_%s_rid_%s'.CACHE_RADIO_POSTFIX);		
//7天回看缓存时间
define('MC_TIME_RADIO_SEEK',86400);
//电台节目单缓存key
define('MC_KEY_RADIO_PROGRAM_V2',CACHE_RADIO.'_radio_program_v2_%s_%s'.CACHE_RADIO_POSTFIX);		//电台节目单
define('MC_KEY_RADIO_PROGRAM_V2_2',CACHE_RADIO.'_radio_program_v2_2_%s_%s'.CACHE_RADIO_POSTFIX);		//电台节目单2
//define('MC_KEY_RADIO_ALL_PROGRAM_V2',CACHE_RADIO.'_radio_all_program_v2'.CACHE_RADIO_POSTFIX);		//所有节目 话题使用
define('MC_KEY_RADIO_PROGRAM_TYPE',CACHE_RADIO.'_radio_program_type_%s'.CACHE_RADIO_POSTFIX);		//电台节目类型
define('MC_KEY_RADIO_HOT_PROGRAM_TYPE',CACHE_RADIO.'_radio_hot_program_type_%s'.CACHE_RADIO_POSTFIX);		//热门电台节目类型(该分类下必须对应节目)
define('MC_KEY_RADIO_PROGRAM',CACHE_RADIO.'_radio_program_%s_%s'.CACHE_RADIO_POSTFIX);		//电台节目单
define('MC_KEY_RADIO_PROGRAM_LIST',CACHE_RADIO.'_radio_program_list_%s'.CACHE_RADIO_POSTFIX);	//某电台全部节目单
define('MC_KEY_RADIO_PROGRAM_LIST_NEW',CACHE_RADIO.'_radio_program_new_list_%s'.CACHE_RADIO_POSTFIX);	//某电台全部节目单
define('MC_KEY_RADIO_PROGRAM_PID_DAY',CACHE_RADIO.'_program_pid_day_%s_%s'.CACHE_RADIO_POSTFIX);  //某地区下某天的全部电台节目单详细
define('MC_KEY_RADIO_PROGRAM_RID_NAME',CACHE_RADIO.'_program_rid_name_%s'.CACHE_RADIO_POSTFIX);  //某电台按节目名称统计的节目信息
define('MC_KEY_RADIO_PROGRAM_NAME_DAY',CACHE_RADIO.'_program_name_%s_day_%s'.CACHE_RADIO_POSTFIX);  //某电台按节目名称统计的节目信息
define('MC_KEY_RADIO_PROGRAM_PID_V2',CACHE_RADIO.'_program_pid_v2_%s'.CACHE_RADIO_POSTFIX);  //按节目id统计的节目信息
define('MC_KEY_RADIO_PROGRAM_RID_DJ',CACHE_RADIO.'_program_rid_dj_%s'.CACHE_RADIO_POSTFIX);  //某电台按节目dj统计的节目信息
define('MC_KEY_RADIO_PROGRAM_UID_DJ',CACHE_RADIO.'_program_uid_dj_%s'.CACHE_RADIO_POSTFIX);  //按节目dj统计的节目信息
define('MC_KEY_RADIO_PROGRAM_NUM_PID',CACHE_RADIO.'_program_num_pid_%s'.CACHE_RADIO_POSTFIX);  //按省份统计的节目数量信息
//电台节目单缓存时间
define('MC_TIME_RADIO_PROGRAM',86400);					//单个电台节目单缓存时间
define('MC_TIME_RADIO_PROGRAM_LIST',86400);				//全部电台节目单缓存时间
define('MC_TIME_RADIO_PROGRAM_PID_DAY',86400);			//某地区下某天的全部电台节目单详细缓存时间
define('MC_TIME_RADIO_PROGRAM_PID_NAME',86400);			//某电台按节目名称统计的节目信息缓存时间
define('MC_TIME_RADIO_PROGRAM_RID_DJ',86400);			//某电台按节目dj统计的节目信息缓存时间
define('MC_TIME_RADIO_PROGRAM_UID_DJ',86400);			//按节目dj统计的节目信息缓存时间
//电台名片缓存key
define('MC_KEY_RADIO_CARD',CACHE_RADIO.'_program_card_%s'.CACHE_RADIO_POSTFIX);  //电台名片
define('MC_KEY_RADIO_SIMPLE_NAME_CARD',CACHE_RADIO.'_simple_name_card_%s'.CACHE_RADIO_POSTFIX);  //电台名片
define('MC_TIME_RADIO_CARD',86400);			//电台名片
//电台dj名片缓存key
define('MC_KEY_RADIO_NAME_CARD',CACHE_RADIO.'_program_name_card_%s'.CACHE_RADIO_POSTFIX);  //电台dj名片
define('MC_TIME_RADIO_NAME_CARD',86400);			//电台dj名片
//其他缓存key
define('MC_KEY_RADIO_RECENT_WEBINFO_BY_CRONTAB', CACHE_RADIO.'_radio_recent_webinfo_by_crontab_%s'.CACHE_RADIO_POSTFIX);  //通过crontab获取最近的微博信息
//其他缓存时间
define('MC_TIME_RADIO_RECENT_WEBINFO_BY_CRONTAB', 86400);

define('ALLOW_VISIT_IP_DIR', PATH_ROOT . 'service/radio/tools/check/config/config.php');	// 允许访问接口的配置文件
//所有电台dj的uid缓存key
define('MC_KEY_RADIO_ALL_DJ_UIDS',CACHE_RADIO.'_radio_all_dj_uids'.CACHE_RADIO_POSTFIX);		//电台节目单
//所有电台dj的uid缓存时间
define('MC_TIME_RADIO_ALL_DJ_UIDS',600);
//所有电台信息的缓存
define('MC_KEY_RADIO_ALL_ONLINE_FOR_OPEN', CACHE_RADIO.'_radio_all_online_for_open'.CACHE_RADIO_POSTFIX);
define('MC_TIME_RADIO_ALL_ONLINE_FOR_OPEN', 86400);
//电台前台管理权限的缓存
define('MC_KEY_RADIO_POWER_FRONT', CACHE_RADIO.'_admin_power_front'.CACHE_RADIO_POSTFIX);
define('MC_TIME_RADIO_POWER_FRONT', 3600);
//电台无线推荐图的缓存
define('MC_KEY_RADIO_RECOMMEND_PIC', CACHE_RADIO.'_radio_recommend_pic'.CACHE_RADIO_POSTFIX);
define('MC_KEY_RADIO_RECOMMEND_PIC_ALL', CACHE_RADIO.'_radio_recommend_pic_all'.CACHE_RADIO_POSTFIX);//全部推荐图
define('MC_KEY_RADIO_RECOMMEND_PIC_NOW', CACHE_RADIO.'_radio_recommend_pic_now'.CACHE_RADIO_POSTFIX);//当前时段的推荐图
define('MC_TIME_RADIO_RECOMMEND_PIC', 3600);
define('MC_KEY_RADIO_RECOMMEND_PIC_TYPE', CACHE_RADIO.'_radio_recommend_pic_type_%s'.CACHE_RADIO_POSTFIX);
define('MC_TIME_RADIO_RECOMMEND_PIC_TYPE', 3600);
//首页左侧推荐图
define('MC_KEY_RADIO_LEFT_PIC', CACHE_RADIO.'_radio_left_pic'.CACHE_RADIO_POSTFIX);
define('MC_TIME_RADIO_LEFT_PIC', 86400);
//流状态缓存转码后的
define('MC_KEY_RADIO_CHECK_STREAM_AFTER', CACHE_RADIO.'_radio_stream_after'.CACHE_RADIO_POSTFIX);
//电台页面上某区块内容缓存
define('MC_KEY_RADIO_PAGE_BLOCK_INFO_BY_NAME', CACHE_RADIO.'_radio_page_block_%s_info_name_%s'.CACHE_RADIO_POSTFIX);
//电台页面全部内容缓存 
define('MC_KEY_RADIO_PAGE_ALL_INFO_BY_NAME', CACHE_RADIO.'_radio_page_all_info_name_%s'.CACHE_RADIO_POSTFIX);

//电台热门节目的分类(通过跑cron生成)
define('MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES', CACHE_RADIO.'_radio_all_hot_program_types'.CACHE_RADIO_POSTFIX);
//电台热门节目的分类(当前分类)
define('MC_KEY_RADIO_ALL_HOT_PROGRAM_TYPES_NOW', CACHE_RADIO.'_radio_all_hot_program_types_now'.CACHE_RADIO_POSTFIX);
//微博v6电台官方账号首页的card信息缓存
define('MC_KEY_RADIO_WEIBO_CARD_UID', CACHE_RADIO.'_radio_weibo_card_uid_%'.CACHE_RADIO_POSTFIX);

$_LANG = array(
	'RADIO_M_CK_00001' => '参数错误',

	'RADIO_M_CK_rid_UNINT' => '电台编号不是整型',
	'RADIO_M_CK_rid_UNEMPTY' => '电台编号不能为空',
	'RADIO_M_CK_domain_UNMIN' => '电台域名低于了最小值',
	'RADIO_M_CK_domain_UNMAX' => '电台域名超过了最大值',
	'RADIO_M_CK_domain_UNEMPTY' => '电台域名不能为空',
	'RADIO_M_CK_info_UNMIN' => '电台信息低于了最小值',
	'RADIO_M_CK_info_UNMAX' => '电台信息超过了最大值',
	'RADIO_M_CK_info_UNFUN' => '电台信息自定义方法错误',
	'RADIO_M_CK_info_UNEMPTY' => '电台信息不能为空',
	'RADIO_M_CK_tag_UNMIN' => '电台话题低于了最小值',
	'RADIO_M_CK_tag_UNMAX' => '电台话题超过了最大值',
	'RADIO_M_CK_tag_UNEMPTY' => '电台话题不能为空',
	'RADIO_M_CK_source_UNMIN' => '电台音频源标示值低于了最小值',
	'RADIO_M_CK_source_UNMAX' => '电台音频源标示值超过了最大值',
	'RADIO_M_CK_source_UNEMPTY' => '电台音频源标示不能为空',
	'RADIO_M_CK_upuid_UNMIN' => '更新用户低于了最小值',
	'RADIO_M_CK_upuid_UNMAX' => '更新用户超过了最大值',
	'RADIO_M_CK_upuid_UNEMPTY' => '更新用户不能为空',
	'RADIO_M_CK_did_UNINT' => '电台主持人编号不是整型',
	'RADIO_M_CK_did_UNEMPTY' => '电台主持人编号不能为空',
	'RADIO_M_CK_publink_UNMIN' => '电台主持人名人堂链接低于了最小值',
	'RADIO_M_CK_publink_UNMAX' => '电台主持人名人堂链接超过了最大值',
	'RADIO_M_CK_publink_UNEMPTY' => '电台主持人名人堂链接不能为空',
	'RADIO_M_CK_uids_UNMIN' => '电台主持人UID值低于了最小值',
	'RADIO_M_CK_uids_UNMAX' => '电台主持人UID值超过了最大值',
	'RADIO_M_CK_uids_UNEMPTY' => '电台主持人UID值不能为空',
	'RADIO_M_CK_tid_UNINT' => '电台分类编号不是整型',
	'RADIO_M_CK_tid_UNEMPTY' => '电台分类编号不能为空',
	'RADIO_M_CK_rtype_UNMIN' => '电台分类值低于了最小值',
	'RADIO_M_CK_rtype_UNMAX' => '电台分类值超过了最大值',
	'RADIO_M_CK_rtype_UNEMPTY' => '电台分类不能为空',
	'RADIO_M_CK_ftype_UNMIN' => '电台分类类别值低于了最小值',
	'RADIO_M_CK_ftype_UNMAX' => '电台分类类别值超过了最大值',
	'RADIO_M_CK_ftype_UNEMPTY' => '电台分类类别不能为空',
	'RADIO_M_CK_intro_UNMAX' => '简介文字不可超过200个汉字',
	'RADIO_D_CK_00001' => '验证数据参数错误',
	'RADIO_D_DB_00001' => '链接数据库失败',
	'RADIO_D_DB_00002' => '处理数据失败',
	'RADIO_D_DB_00003' => '计算数据失败'
);

//用户行为编码
define('RADIO_USER_LOGIN', '10000001');          //登陆
define('RADIO_USER_ADDMBLOG', '10000002');       //用户发微博
define('RADIO_USER_ADDCOMMENT', '10000003');       //用户发评论
define('RADIO_USER_ADDFOLLOW', '10000004');      //加关注
define('RADIO_USER_CHANGECHANNEL', '10000005');  //换台
define('RADIO_USER_SHARE', '10000006');          //落地分享
define('RADIO_USER_PLAY', '10000007');           //播放
define('RADIO_USER_PLAY_PROGRAM', '10000008');           //打点
define('RADIO_USER_COLLECT', '10000009');		//收藏电台
define('RADIO_USER_ADDMBLOG', '10000010');       //用户转发微博
define('RADIO_USER_ADDMBLOG', '10000011');       //用户评论微博
define('RADIO_USER_ADDMBLOG', '10000012');       //用户收藏微博

global $RADIO_ADMIN;		//节目单的超级管理员id
//$RADIO_ADMIN = Array('1576486794','1910494032','1652423191','2716653035','2663289435','1793584905','2052696803','2053539481','1740690051','1093802767','2022486271','1698084784');
global $RADIO_ACTION_SOURCE;		//微电台结果页来源参数
$RADIO_ACTION_SOURCE = Array(
						'radio_rollpic',	//轮播图来源
						'radio_live_program',	//电台首页正在直播区域的本地区节目来源
						'radio_hot_live',	//电台首页正在直播区域的当前热门节目来源
						'radio_hot_rank',	//电台首页上周热门节目排行榜来源
						'radio_live_dj',	//正在直播的dj来源
						'radioarea_hot',	//热门电台来源
						'radioarea_list',	//电台列表来源
						'radioarea_collect',//我收藏的电台来源
						'radioarea_listen', //微电台收听榜来源
						'radioarea_lastlisten', //上次收听榜来源
						'notice',//走马灯（公告区）
						'feed',//feed点击
						);

define('RADIO_STATISTIC_PIC_ID',1013125);	//微电台数据统计特殊处理，在pic_info中存放数据的ID 多少电台多少dj多少地区mysql mark
define('RADIO_STATISTIC_PIC_ID_NEW',128);	//微电台数据统计特殊处理，在pic_info中存放数据的ID 多少电台多少dj多少地区mysql mark  新版
define('MC_KEY_RADIO_STATIC_INFO', CACHE_RADIO.'_radio_static_info'.CACHE_RADIO_POSTFIX);	//微电台数据统计特殊处理，计算得出的结果 

define('RADIO_ACTIVE_RANK', 'http://api.data.sina.com.cn/servlet/defaultDispatcher?__action=general.vradio_activelist&day_key=%s&type=%s');  //微电台活跃榜的接口
//define('RADIO_KANDIAN_TRANSCODE_URL_CREATE', 'http://123.125.104.141/e/api/radio/createRadioEpg.php?rId=%s&rName=%s&rFm=%s&mms=%s');  //创建电台调用看到接口
//define('RADIO_KANDIAN_TRANSCODE_URL', 'http://123.125.104.141/e/api/radio/createRadioEpg.php?rId=%s&continue=%s');  //更新电台信息调用电台调用看点接口
define('RADIO_KANDIAN_TRANSCODE_URL_CREATE', 'http://10.13.1.225:8011/e/api/radio/createRadioEpg.php?rId=%s&rName=%s&rFm=%s&mms=%s');  //创建电台调用看到接口
define('RADIO_KANDIAN_TRANSCODE_URL', 'http://10.13.1.225:8011/e/api/radio/createRadioEpg.php?rId=%s&continue=%s');  //更新电台信息调用电台调用看点接口

//define('RADIO_TRANSCODE_URL', 'http://api.wtv.v.iask.com/operator/channel_add.php?channel_url=%s&start_time=%s&end_time=%s&callback_url=%s&key=%s&valide=%s&have_video=no');  //更新电台信息调用电台调用看点接口
//新版换流接口
define('RADIO_TRANSCODE_URL', 'http://api.wtv.v.iask.com/operator/channel_add.php?channel_url=%s&start_time=%s&end_time=%s&callback_url=%s&key=%s&valide=%s&have_video=no');
define('RADIO_TRANSCODE_URL_REDIS', 'http://10.13.0.51/operator/channel_add_redis.php?channel_url=%s&start_time=%s&end_time=%s&callback_url=%s&key=%s&valide=%s&stream_retry_secs=%s&have_video=no');
//所有黑名单用户信息
define('MC_KEY_RADIO_ALL_BLACK_LIST', CACHE_RADIO.'_radio_all_black_list'.CACHE_RADIO_POSTFIX);
define('MC_TIME_RADIO_ALL_BLACK_LIST', 600);

define('COMMENT_SWITCH',1);//评论开关 1 打开    2关闭,禁止评论    
define('FORWARD_SWITCH',1);//转发开关 1 打开    2关闭,禁止转发    

define('RADIO_STARTTIME_PIC_ID',1013126);	//微电台配置文件start参数特殊处理，在pic_info中存放数据的ID
