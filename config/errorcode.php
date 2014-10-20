<?php
//--------------open api error code----------------------
define('OPENAPI_304', '没有数据返回');
define('OPENAPI_400', '请求数据不合法，或者超过请求频率限制');
define('OPENAPI_401', '没有进行身份验证');
define('OPENAPI_402', '没有开通微博');
define('OPENAPI_403', '没有权限访问对应的资源');
define('OPENAPI_404', '请求的资源不存在');
define('OPENAPI_500', '服务器内部错误');
define('OPENAPI_502', '微博接口API关闭或正在升级');
define('OPENAPI_503', '服务端资源不可用');

//--------------Live data error code---------------------
define('LIVE_00001', 'sql查询失败，请检查sql语句');
define('LIVE_00002', 'sql执行失败，请检查sql语句');
define('LIVE_00003', '数据库单一访谈内容为空');
define('LIVE_00004', '数据库连接失败');
//define('LIVE_00005', '数据库问题列表为空');
//define('LIVE_00006', '数据库回答列表为空');
//define('LIVE_00007', '数据库单一访谈黑名单内容为空');
//define('LIVE_00008', '数据库单一访谈黑名单内容为空');
//define('LIVE_00009', '没有数据');

define('LIVE_00010', '线上没有找到用户信息');
define('LIVE_00011', '线上没有找到微博信息');

define('LIVE_00012', '获取单一访谈数据失败');
define('LIVE_00013', '参数类型不正确');
//define('LIVE_00014', '没有提问信息');
define('LIVE_00016', '参数中缺少必填项mid或mid为空');
define('LIVE_00017', '参数中缺少必填项live_id');
define('LIVE_00018', '参数中缺少必填项ctime');
define('LIVE_00019', '已经添加关键词，无须重复添加');
define('LIVE_00020', '没有关键词');
define('LIVE_00021', '没有此关键词');
define('LIVE_00022', '此人已在黑名单中');
define('LIVE_00023', '此人不在黑名单中');
//define('LIVE_00024', '订制信息数据为空');
define('LIVE_00025', '手机绑定状态接口错误');



define('LIVE_00015', '缓存更新失败');

//model层错误代码
define('LIVE_00031', '非法参数');
define('LIVE_00032', '您不是嘉宾,无权回答提问');
define('LIVE_00033', '您不是嘉宾或管理员，无权删除回答');
define('LIVE_00034', '内容含有非法关键词');
define('LIVE_00035', '您不是主持人，无法对问题加精');
define('LIVE_00036', '您不是主持人，无法对问题隐藏');
define('LIVE_00037', '您不是主持人，无权添加关键词');
define('LIVE_00038', '您不是主持人，无权删除关键词');
define('LIVE_00039', '您误将自己添加为黑名单一员了');
define('LIVE_00040', '您不是主持人，无权添加黑名单');
define('LIVE_00041', '您不是主持人，无权删除黑名单');
define('LIVE_00042', '未上传头像');
define('LIVE_00043', '未绑定手机');
define('LIVE_00044', '注册时间未超过指定时间');
define('LIVE_00045', '微博数未超过指定数量');
define('LIVE_00046', '粉丝数未超过指定数量');
define('LIVE_00047', '用户在黑名单中');
define('LIVE_00048', '获取当前用户信息失败');
define('LIVE_00049', '您无权删除其他嘉宾发布的回答');
define('LIVE_00050', '格式化数据失败');
define('LIVE_00051', '输入不能为空');
define('LIVE_00052', '您无权删除其他用户的微博信息');
define('LIVE_00053', '没有权限');


//--------------Live data error code---------------------
define('WEBCAST_00001', '参数不能为空,请检查参数!');
define('WEBCAST_00002', '参数过长,请检查参数!');
define('WEBCAST_00003', '操作失败');
define('WEBCAST_00004', '参数中缺少必填项id或id为空');



//--------------WEBCAST model error code---------------------
define('WEBCAST_00031', '非法参数');
define('WEBCAST_00032', '您没有隐藏微博的权限');
define('WEBCAST_00033', '获取直播列表失败');
define('WEBCAST_00034', '获取直播详情失败');
define('WEBCAST_00035', '获取主持人嘉宾信息失败');
define('WEBCAST_00036', '获取直播内容失败');
define('WEBCAST_00037', '获取网友互动失败');
define('WEBCAST_00038', '获取微博黑名单');
define('WEBCAST_00039', '隐藏微博失败');
define('WEBCAST_00040', '关注直播失败');
define('WEBCAST_00041', '取消关注直播失败');
define('WEBCAST_00042', '获取关注直播列表失败');
define('WEBCAST_00043', '获取视频列表失败');
define('WEBCAST_00044', '添加视频失败');
define('WEBCAST_00045', '修改视频失败');
define('WEBCAST_00046', '删除视频失败');
define('WEBCAST_00047', '获取用户角色失败');
define('WEBCAST_00048', '您无权发布微博');
define('WEBCAST_00049', '您无权添加新视频');
define('WEBCAST_00050', '您无权更新视频');
define('WEBCAST_00051', '您无权删除视频');
define('WEBCAST_00052', '您无权获取视频列表');
define('WEBCAST_00053', '更新静态页面失败');
define('WEBCAST_00054', '读取静态页面失败');
define('WEBCAST_00055', '视频地址错误');
define('WEBCAST_00056', '用户信息获取失败');
define('WEBCAST_00057', '用户添加失败');
define('WEBCAST_00058', '该用户已经存在');
define('WEBCAST_00059', '用户列表获取失败');
define('WEBCAST_00060', '用户删除失败');
define('WEBCAST_00061', '用户排序失败');
define('WEBCAST_00062', '用户昵称或简介过长');
define('WEBCAST_00063', '昵称或简介存在敏感关键词');
define('WEBCAST_00064', '您添加的嘉宾人数已满，请删除后再添加');
?>