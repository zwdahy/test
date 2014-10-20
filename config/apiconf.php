<?php
//I.API根路径
define('API_ROOT_PATH',"http://i2.api.weibo.com/2");
//I.API用户信息接口
define('API_USER_PATH',API_ROOT_PATH.'/users/');
define('API_DOMAIN_SHOW',API_USER_PATH.'domain_show.json?source='.RADIO_SOURCE_APP_KEY.'&domain=%s');
define('API_SHOW_BATCH_BY_UID',API_USER_PATH.'show_batch.json?source='.RADIO_SOURCE_APP_KEY.'&uids=%s&has_extend=1');
define('API_SHOW_BATCH_BY_NAME',API_USER_PATH.'show_batch.json?source='.RADIO_SOURCE_APP_KEY.'&screen_name=%s&has_extend=1');

//I.API微博信息接口
define('API_STATUSES_PATH',API_ROOT_PATH.'/statuses/');
define('API_STATUSES_REPOST',API_STATUSES_PATH.'repost.json');
define('API_STATUSES_ADD',API_STATUSES_PATH.'update.json');
define('API_STATUSES_ADD_PIC',API_STATUSES_PATH.'upload_url_text.json');
define('API_STATUSES_QUERYID',API_STATUSES_PATH.'queryid.json?source='.RADIO_SOURCE_APP_KEY.'&mid=%s&type=%s&is_batch=1');
define('API_STATUSES_SHOW_BATCH',API_STATUSES_PATH.'show_batch.json?source='.RADIO_SOURCE_APP_KEY.'&ids=%s&is_encoded=0');
define('API_STATUSES_TIMELINE_BATCH',API_STATUSES_PATH.'timeline_batch.json?source='.RADIO_SOURCE_APP_KEY.'&count=%s&page=%s&uids=%s');

//收藏微博
define('API_FAVORITES_PATH',API_ROOT_PATH.'/favorites/');
define('API_FAVORITES_CREATE',API_FAVORITES_PATH.'create.json');

//I.API评论接口
define('API_COMMENTS_PATH',API_ROOT_PATH.'/comments/');
define('API_COMMENTS_CREATE',API_COMMENTS_PATH.'create.json');
define('API_COMMENTS_REPLY',API_COMMENTS_PATH.'reply.json');
define('API_COMMENTS_DESTROY',API_COMMENTS_PATH.'destroy.json');
define('API_COMMENTS_SHOW',API_COMMENTS_PATH.'show.json?source='.RADIO_SOURCE_APP_KEY.'&id=%s&page=1');

//I.API搜索接口
define('API_SEARCH_PATH',API_ROOT_PATH.'/search/');
define('API_SEARCH_STATUSES',API_SEARCH_PATH.'statuses.json?');

//I.API账号信息接口
define('API_ACCOUNT_PATH',API_ROOT_PATH.'/account/');
define('API_MOBILE_BATCH',API_ACCOUNT_PATH.'mobile_batch.json?source='.RADIO_SOURCE_APP_KEY.'&uids=%s');
define('API_WATERMARK',API_ACCOUNT_PATH.'watermark.json?source='.RADIO_SOURCE_APP_KEY);

//I.API发布动态接口
define('API_ACTIVITY_PATH',API_ROOT_PATH.'/activities/apps/update.json');

//I.API发布长链转短链接口
define('API_SHORT_PATH',API_ROOT_PATH.'/short_url/');
define('API_SHORT_CREATE',API_SHORT_PATH.'shorten.json?source='.RADIO_SOURCE_APP_KEY.'&url_long=%s');