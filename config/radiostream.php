<?php
//add 刷新配置文件的start参数，每周五更新这个值
define('CLASS_PATH','service/radio/');
if(!defined('SERVER_ROOT')){
	define('SERVER_ROOT', PATH_ROOT.CLASS_PATH);
}
include_once PATH_ROOT.CLASS_PATH.'config/config.php';
include_once PATH_ROOT.CLASS_PATH.'config/radioconf.php';
$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');

//获取有无特殊ID的时间信息
$startTime = $obj->getPicInfo(array('pic_id'=> RADIO_STARTTIME_PIC_ID));

//存在的基础上就是更新数据
if(isset($startTime['result']['content'][0]['pic_id'])){
	$start_param = $startTime['result']['content'][0]['img_url'];
	if(date('D') == 'Fri'){
		//周五，插入当前时间，判断如果今天已经更新了，就不更新了。
		$str_start = mktime(0, 0 , 0,date("m"),date("d"),date("Y"));
		$str_end = mktime(23,59,59,date("m"),date("d"),date("Y"));
		if($str_start<$start_param && $start_param<$str_end){
			$start_param = $start_param;
		}else{
			$start_param = time();
			$args = array(
					'pic_id' => RADIO_STARTTIME_PIC_ID, 
					'img_url' => $start_param,
					'link_url' => '',
					'upuid' => ''
			);
			$result = $obj->updatePicInfo($args);
		}
	}
}else{
	//插入特殊处理的电台start的参数
	$start_param = time();	
	$args = array(
			'pic_id' => RADIO_STARTTIME_PIC_ID, 
			'img_url' => $start_param,
			'link_url' => '',
			'upuid' => ''
	);	
	$result = $obj->addPicInfo($args);
}


// 张旭
global $RADIO_STREAM;
	$obj = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
	$tmp_radio = $obj->getRadioStream();
	$radios = array();
	foreach($tmp_radio as $k=>$v){
	//$tmp = explode('&start=',htmlspecialchars_decode($v['http']));
	//$end = explode('&end',$tmp[1]);
	$v['http'] =  htmlspecialchars_decode($v['http']);
	$v['mu'] = htmlspecialchars_decode($v['mu']);
	$radios[$k] = $v;
//	unset($tmp);
//	unset($end);
	unset($v['http']);
	unset($v['mu']);
  }
  $RADIO_STREAM = $radios;

/*
$RADIO_STREAM = array (
  2 => 
  array (
    'epg_id' => '1042927890',
    'radio_fm' => 'FM1006',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927890&start=1345030954&end=1352979754',
    'mu' => 'http://dload.kandian.com:22111/1042927890.m3u8',
  ),
  6 => 
  array (
    'epg_id' => '1042927900',
    'radio_fm' => 'FM1073',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927900&start=1345031206&end=1352980006',
    'mu' => 'http://dload.kandian.com:22111/1042927900.m3u8',
  ),
  7 => 
  array (
    'epg_id' => '1042927910',
    'radio_fm' => 'AM603',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927910&start=1345031596&end=1371297196',
    'mu' => 'http://dload.kandian.com:22111/1042927910.m3u8',
  ),
  8 => 
  array (
    'epg_id' => '1042927920',
    'radio_fm' => 'FM1039',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927920&start=1345031596&end=1371297196',
    'mu' => 'http://dload.kandian.com:22111/1042927920.m3u8',
  ),
  9 => 
  array (
    'epg_id' => '1042927930',
    'radio_fm' => 'FM1025',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927930&start=1345031596&end=1371297196',
    'mu' => 'http://dload.kandian.com:22111/1042927930.m3u8',
  ),
  10 => 
  array (
    'epg_id' => '1042927940',
    'radio_fm' => 'FM876',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927940&start=1345031596&end=1371297196',
    'mu' => 'http://dload.kandian.com:22111/1042927940.m3u8',
  ),
  11 => 
  array (
    'epg_id' => '1042927950',
    'radio_fm' => 'FM974',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927950&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042927950.m3u8',
  ),
  12 => 
  array (
    'epg_id' => '1042927960',
    'radio_fm' => 'AM774',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927960&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042927960.m3u8',
  ),
  31 => 
  array (
    'epg_id' => '1042927970',
    'radio_fm' => 'FM915',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927970&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042927970.m3u8',
  ),
  95 => 
  array (
    'epg_id' => '1042927980',
    'radio_fm' => 'FM1024',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927980&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042927980.m3u8',
  ),
  97 => 
  array (
    'epg_id' => '1042927990',
    'radio_fm' => 'FM878',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042927990&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042927990.m3u8',
  ),
  99 => 
  array (
    'epg_id' => '1042928000',
    'radio_fm' => 'FM887',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928000&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928000.m3u8',
  ),
  100 => 
  array (
    'epg_id' => '1042928010',
    'radio_fm' => 'FM1026',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928010&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928010.m3u8',
  ),
  101 => 
  array (
    'epg_id' => '1042928020',
    'radio_fm' => 'FM988',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928020&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928020.m3u8',
  ),
  102 => 
  array (
    'epg_id' => '1042928030',
    'radio_fm' => 'FM914',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928030&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928030.m3u8',
  ),
  103 => 
  array (
    'epg_id' => '1042928040',
    'radio_fm' => 'FM1068',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928040&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928040.m3u8',
  ),
  105 => 
  array (
    'epg_id' => '1042928050',
    'radio_fm' => 'FM940',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928050&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928050.m3u8',
  ),
  106 => 
  array (
    'epg_id' => '1042928060',
    'radio_fm' => 'FM1054',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928060&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928060.m3u8',
  ),
  108 => 
  array (
    'epg_id' => '1042928070',
    'radio_fm' => 'FM931',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928070&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928070.m3u8',
  ),
  110 => 
  array (
    'epg_id' => '1042928080',
    'radio_fm' => 'FM955',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928080&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928080.m3u8',
  ),
  112 => 
  array (
    'epg_id' => '1042928090',
    'radio_fm' => 'FM909',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928090&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928090.m3u8',
  ),
  113 => 
  array (
    'epg_id' => '1042928100',
    'radio_fm' => 'FM94',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928100&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928100.m3u8',
  ),
  114 => 
  array (
    'epg_id' => '1042928110',
    'radio_fm' => 'FM893',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928110&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928110.m3u8',
  ),
  115 => 
  array (
    'epg_id' => '1042928120',
    'radio_fm' => 'FM918',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928120&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928120.m3u8',
  ),
  118 => 
  array (
    'epg_id' => '1042928130',
    'radio_fm' => 'FM949',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928130&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928130.m3u8',
  ),
  119 => 
  array (
    'epg_id' => '1042928140',
    'radio_fm' => 'FM970',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928140&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928140.m3u8',
  ),
  121 => 
  array (
    'epg_id' => '1042928150',
    'radio_fm' => 'FM897',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928150&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928150.m3u8',
  ),
  122 => 
  array (
    'epg_id' => '1042928160',
    'radio_fm' => 'FM1058',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928160&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928160.m3u8',
  ),
  124 => 
  array (
    'epg_id' => '1042928170',
    'radio_fm' => 'FM901',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928170&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928170.m3u8',
  ),
  125 => 
  array (
    'epg_id' => '1042928180',
    'radio_fm' => 'FM944',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928180&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928180.m3u8',
  ),
  126 => 
  array (
    'epg_id' => '1042928190',
    'radio_fm' => 'FM935',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928190&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928190.m3u8',
  ),
  127 => 
  array (
    'epg_id' => '1042928200',
    'radio_fm' => 'FM950',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928200&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928200.m3u8',
  ),
  128 => 
  array (
    'epg_id' => '1042928210',
    'radio_fm' => 'FM913',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928210&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928210.m3u8',
  ),
  129 => 
  array (
    'epg_id' => '1042928220',
    'radio_fm' => 'FM1038',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928220&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928220.m3u8',
  ),
  137 => 
  array (
    'epg_id' => '1042928230',
    'radio_fm' => 'FM966',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928230&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928230.m3u8',
  ),
  154 => 
  array (
    'epg_id' => '1042928240',
    'radio_fm' => 'FM1046',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928240&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928240.m3u8',
  ),
  204 => 
  array (
    'epg_id' => '1042928250',
    'radio_fm' => 'FM895',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928250&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928250.m3u8',
  ),
  208 => 
  array (
    'epg_id' => '1042928260',
    'radio_fm' => 'FM1003',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928260&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928260.m3u8',
  ),
  209 => 
  array (
    'epg_id' => '1042928270',
    'radio_fm' => 'FM929',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928270&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928270.m3u8',
  ),
  210 => 
  array (
    'epg_id' => '1042928280',
    'radio_fm' => 'FM961',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928280&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928280.m3u8',
  ),
  221 => 
  array (
    'epg_id' => '1042928290',
    'radio_fm' => 'FM1028',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928290&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928290.m3u8',
  ),
  250 => 
  array (
    'epg_id' => '1042928300',
    'radio_fm' => 'FM986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928300&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928300.m3u8',
  ),
  251 => 
  array (
    'epg_id' => '1042928310',
    'radio_fm' => 'FM995',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928310&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928310.m3u8',
  ),
  255 => 
  array (
    'epg_id' => '1042928320',
    'radio_fm' => 'FM974',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928320&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928320.m3u8',
  ),
  256 => 
  array (
    'epg_id' => '1042928330',
    'radio_fm' => 'FM931',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928330&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928330.m3u8',
  ),
  261 => 
  array (
    'epg_id' => '1042928340',
    'radio_fm' => 'AM603',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928340&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928340.m3u8',
  ),
  262 => 
  array (
    'epg_id' => '1042928350',
    'radio_fm' => 'FM1038',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928350&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928350.m3u8',
  ),
  264 => 
  array (
    'epg_id' => '1042928360',
    'radio_fm' => 'FM1026',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928360&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928360.m3u8',
  ),
  265 => 
  array (
    'epg_id' => '1042928370',
    'radio_fm' => 'FM1050',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928370&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928370.m3u8',
  ),
  266 => 
  array (
    'epg_id' => '1042928380',
    'radio_fm' => 'FM1078',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928380&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928380.m3u8',
  ),
  267 => 
  array (
    'epg_id' => '1042928390',
    'radio_fm' => 'FM912',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928390&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928390.m3u8',
  ),
  268 => 
  array (
    'epg_id' => '1042928400',
    'radio_fm' => 'FM916',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928400&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928400.m3u8',
  ),
  269 => 
  array (
    'epg_id' => '1042928410',
    'radio_fm' => 'FM974',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928410&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928410.m3u8',
  ),
  271 => 
  array (
    'epg_id' => '1042928420',
    'radio_fm' => 'FM1043',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928420&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928420.m3u8',
  ),
  272 => 
  array (
    'epg_id' => '1042928430',
    'radio_fm' => 'FM107',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928430&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928430.m3u8',
  ),
  273 => 
  array (
    'epg_id' => '1042928440',
    'radio_fm' => 'FM953',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928440&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928440.m3u8',
  ),
  275 => 
  array (
    'epg_id' => '1042928450',
    'radio_fm' => 'FM1034',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928450&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928450.m3u8',
  ),
  277 => 
  array (
    'epg_id' => '1042928460',
    'radio_fm' => 'FM889',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928460&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928460.m3u8',
  ),
  278 => 
  array (
    'epg_id' => '1042928470',
    'radio_fm' => 'FM1039',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928470&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928470.m3u8',
  ),
  280 => 
  array (
    'epg_id' => '1042928480',
    'radio_fm' => 'FM1061',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928480&start=1345031597&end=1371297197',
    'mu' => 'http://dload.kandian.com:22111/1042928480.m3u8',
  ),
  281 => 
  array (
    'epg_id' => '1042928490',
    'radio_fm' => 'FM896',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928490&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928490.m3u8',
  ),
  288 => 
  array (
    'epg_id' => '1042928500',
    'radio_fm' => 'FM1059',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928500&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928500.m3u8',
  ),
  289 => 
  array (
    'epg_id' => '1042928510',
    'radio_fm' => 'FM908',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928510&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928510.m3u8',
  ),
  292 => 
  array (
    'epg_id' => '1042928520',
    'radio_fm' => 'FM985',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928520&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928520.m3u8',
  ),
  293 => 
  array (
    'epg_id' => '1042928530',
    'radio_fm' => 'FM905',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928530&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928530.m3u8',
  ),
  294 => 
  array (
    'epg_id' => '1042928540',
    'radio_fm' => 'FM907',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928540&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928540.m3u8',
  ),
  296 => 
  array (
    'epg_id' => '1042928550',
    'radio_fm' => 'FM1054',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928550&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928550.m3u8',
  ),
  297 => 
  array (
    'epg_id' => '1042928560',
    'radio_fm' => 'FM918',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928560&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928560.m3u8',
  ),
  304 => 
  array (
    'epg_id' => '1042928570',
    'radio_fm' => 'FM1062',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928570&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928570.m3u8',
  ),
  305 => 
  array (
    'epg_id' => '1042928580',
    'radio_fm' => 'FM900',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928580&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928580.m3u8',
  ),
  306 => 
  array (
    'epg_id' => '1042928590',
    'radio_fm' => 'FM952',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928590&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928590.m3u8',
  ),
  307 => 
  array (
    'epg_id' => '1042928600',
    'radio_fm' => 'FM989',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928600&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928600.m3u8',
  ),
  308 => 
  array (
    'epg_id' => '1042928610',
    'radio_fm' => 'FM916',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928610&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928610.m3u8',
  ),
  309 => 
  array (
    'epg_id' => '1042928620',
    'radio_fm' => 'FM99',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928620&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928620.m3u8',
  ),
  310 => 
  array (
    'epg_id' => '1042928630',
    'radio_fm' => 'FM942',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928630&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928630.m3u8',
  ),
  311 => 
  array (
    'epg_id' => '1042928640',
    'radio_fm' => 'FM946',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928640&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928640.m3u8',
  ),
  312 => 
  array (
    'epg_id' => '1042928650',
    'radio_fm' => 'AM810',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928650&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928650.m3u8',
  ),
  313 => 
  array (
    'epg_id' => '1042928660',
    'radio_fm' => 'FM972',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928660&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928660.m3u8',
  ),
  314 => 
  array (
    'epg_id' => '1042928670',
    'radio_fm' => 'FM104',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928670&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928670.m3u8',
  ),
  315 => 
  array (
    'epg_id' => '1042928680',
    'radio_fm' => 'FM1061',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928680&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928680.m3u8',
  ),
  316 => 
  array (
    'epg_id' => '1042928690',
    'radio_fm' => 'FM998',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928690&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928690.m3u8',
  ),
  317 => 
  array (
    'epg_id' => '1042928700',
    'radio_fm' => 'FM974',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928700&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928700.m3u8',
  ),
  318 => 
  array (
    'epg_id' => '1042928710',
    'radio_fm' => 'FM1056',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928710&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928710.m3u8',
  ),
  319 => 
  array (
    'epg_id' => '1042928720',
    'radio_fm' => 'FM1001',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928720&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928720.m3u8',
  ),
  320 => 
  array (
    'epg_id' => '1042928730',
    'radio_fm' => 'FM955',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928730&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928730.m3u8',
  ),
  322 => 
  array (
    'epg_id' => '1042928740',
    'radio_fm' => 'FM1061',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928740&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928740.m3u8',
  ),
  323 => 
  array (
    'epg_id' => '1042928750',
    'radio_fm' => 'FM946',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928750&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928750.m3u8',
  ),
  324 => 
  array (
    'epg_id' => '1042928760',
    'radio_fm' => 'FM1056',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928760&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928760.m3u8',
  ),
  325 => 
  array (
    'epg_id' => '1042928770',
    'radio_fm' => 'FM1006',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928770&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928770.m3u8',
  ),
  326 => 
  array (
    'epg_id' => '1042928780',
    'radio_fm' => 'FM1057',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928780&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928780.m3u8',
  ),
  327 => 
  array (
    'epg_id' => '1042928790',
    'radio_fm' => 'FM1027',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928790&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928790.m3u8',
  ),
  328 => 
  array (
    'epg_id' => '1042928800',
    'radio_fm' => 'AM666',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928800&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928800.m3u8',
  ),
  329 => 
  array (
    'epg_id' => '1042928810',
    'radio_fm' => 'FM1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928810&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928810.m3u8',
  ),
  330 => 
  array (
    'epg_id' => '1042928820',
    'radio_fm' => 'FM931',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928820&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928820.m3u8',
  ),
  331 => 
  array (
    'epg_id' => '1042928830',
    'radio_fm' => 'FM1012',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928830&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928830.m3u8',
  ),
  332 => 
  array (
    'epg_id' => '1042928840',
    'radio_fm' => 'FM886',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928840&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928840.m3u8',
  ),
  333 => 
  array (
    'epg_id' => '1042928850',
    'radio_fm' => 'FM999',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928850&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928850.m3u8',
  ),
  334 => 
  array (
    'epg_id' => '1042928860',
    'radio_fm' => 'AM927',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928860&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928860.m3u8',
  ),
  335 => 
  array (
    'epg_id' => '1042928870',
    'radio_fm' => 'FM1001',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928870&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928870.m3u8',
  ),
  336 => 
  array (
    'epg_id' => '1042928880',
    'radio_fm' => 'FM954',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928880&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928880.m3u8',
  ),
  338 => 
  array (
    'epg_id' => '1042928890',
    'radio_fm' => 'FM914',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928890&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928890.m3u8',
  ),
  339 => 
  array (
    'epg_id' => '1042928900',
    'radio_fm' => 'FM89',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928900&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928900.m3u8',
  ),
  340 => 
  array (
    'epg_id' => '1042928910',
    'radio_fm' => 'FM1057',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928910&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928910.m3u8',
  ),
  341 => 
  array (
    'epg_id' => '1042928920',
    'radio_fm' => 'FM1024',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928920&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928920.m3u8',
  ),
  345 => 
  array (
    'epg_id' => '1042928930',
    'radio_fm' => 'FM996',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928930&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928930.m3u8',
  ),
  346 => 
  array (
    'epg_id' => '1042928940',
    'radio_fm' => 'FM906',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928940&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928940.m3u8',
  ),
  347 => 
  array (
    'epg_id' => '1042928950',
    'radio_fm' => 'FM915',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928950&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928950.m3u8',
  ),
  349 => 
  array (
    'epg_id' => '1042928960',
    'radio_fm' => 'FM937',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928960&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928960.m3u8',
  ),
  350 => 
  array (
    'epg_id' => '1042928970',
    'radio_fm' => 'FM89',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928970&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928970.m3u8',
  ),
  351 => 
  array (
    'epg_id' => '1042928980',
    'radio_fm' => 'FM1033',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928980&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928980.m3u8',
  ),
  352 => 
  array (
    'epg_id' => '1042928990',
    'radio_fm' => 'FM1032',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042928990&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042928990.m3u8',
  ),
  353 => 
  array (
    'epg_id' => '1042929000',
    'radio_fm' => 'FM1069',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929000&start=1345031598&end=1371297198',
    'mu' => 'http://dload.kandian.com:22111/1042929000.m3u8',
  ),
  354 => 
  array (
    'epg_id' => '1042929010',
    'radio_fm' => 'FM928',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929010&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929010.m3u8',
  ),
  355 => 
  array (
    'epg_id' => '1042929020',
    'radio_fm' => 'FM992',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929020&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929020.m3u8',
  ),
  356 => 
  array (
    'epg_id' => '1042929030',
    'radio_fm' => 'FM107',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929030&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929030.m3u8',
  ),
  357 => 
  array (
    'epg_id' => '1042929040',
    'radio_fm' => 'FM1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929040&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929040.m3u8',
  ),
  359 => 
  array (
    'epg_id' => '1042929050',
    'radio_fm' => 'FM897',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929050&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929050.m3u8',
  ),
  360 => 
  array (
    'epg_id' => '1042929060',
    'radio_fm' => 'FM1036',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929060&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929060.m3u8',
  ),
  361 => 
  array (
    'epg_id' => '1042929070',
    'radio_fm' => 'FM998',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929070&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929070.m3u8',
  ),
  362 => 
  array (
    'epg_id' => '1042929080',
    'radio_fm' => 'FM895',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929080&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929080.m3u8',
  ),
  363 => 
  array (
    'epg_id' => '1042929090',
    'radio_fm' => 'FM956',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929090&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929090.m3u8',
  ),
  365 => 
  array (
    'epg_id' => '1042929100',
    'radio_fm' => 'FM887',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929100&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929100.m3u8',
  ),
  366 => 
  array (
    'epg_id' => '1042929110',
    'radio_fm' => 'FM1034',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929110&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929110.m3u8',
  ),
  367 => 
  array (
    'epg_id' => '1042929120',
    'radio_fm' => 'FM975',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929120&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929120.m3u8',
  ),
  368 => 
  array (
    'epg_id' => '1042929130',
    'radio_fm' => 'FM1044',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929130&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929130.m3u8',
  ),
  370 => 
  array (
    'epg_id' => '1042929140',
    'radio_fm' => 'FM930',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929140&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929140.m3u8',
  ),
  371 => 
  array (
    'epg_id' => '1042929150',
    'radio_fm' => 'FM887',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929150&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929150.m3u8',
  ),
  372 => 
  array (
    'epg_id' => '1042929160',
    'radio_fm' => 'FM100',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929160&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929160.m3u8',
  ),
  373 => 
  array (
    'epg_id' => '1042929170',
    'radio_fm' => 'FM99',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929170&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929170.m3u8',
  ),
  374 => 
  array (
    'epg_id' => '1042929180',
    'radio_fm' => 'FM918',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929180&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929180.m3u8',
  ),
  375 => 
  array (
    'epg_id' => '1042929190',
    'radio_fm' => 'FM1003',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929190&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929190.m3u8',
  ),
  377 => 
  array (
    'epg_id' => '1042929200',
    'radio_fm' => 'FM888',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929200&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929200.m3u8',
  ),
  392 => 
  array (
    'epg_id' => '1042929210',
    'radio_fm' => 'FM898',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929210&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929210.m3u8',
  ),
  395 => 
  array (
    'epg_id' => '1042929220',
    'radio_fm' => 'FM88',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929220&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929220.m3u8',
  ),
  396 => 
  array (
    'epg_id' => '1042929230',
    'radio_fm' => 'FM107',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929230&start=1345031667&end=1371297267',
    'mu' => 'http://dload.kandian.com:22111/1042929230.m3u8',
  ),
  399 => 
  array (
    'epg_id' => '1042929240',
    'radio_fm' => 'FM103',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929240&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929240.m3u8',
  ),
  400 => 
  array (
    'epg_id' => '1042929250',
    'radio_fm' => 'FM912',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929250&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929250.m3u8',
  ),
  401 => 
  array (
    'epg_id' => '1042929260',
    'radio_fm' => 'FM1017',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929260&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929260.m3u8',
  ),
  402 => 
  array (
    'epg_id' => '1042929270',
    'radio_fm' => 'FM1028',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929270&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929270.m3u8',
  ),
  403 => 
  array (
    'epg_id' => '1042929280',
    'radio_fm' => 'FM1047',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929280&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929280.m3u8',
  ),
  404 => 
  array (
    'epg_id' => '1042929290',
    'radio_fm' => 'FM901',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929290&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929290.m3u8',
  ),
  405 => 
  array (
    'epg_id' => '1042929300',
    'radio_fm' => 'FM1067',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929300&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929300.m3u8',
  ),
  407 => 
  array (
    'epg_id' => '1042929310',
    'radio_fm' => 'FM95',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929310&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929310.m3u8',
  ),
  416 => 
  array (
    'epg_id' => '1042929320',
    'radio_fm' => 'FM93',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929320&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929320.m3u8',
  ),
  417 => 
  array (
    'epg_id' => '1042929330',
    'radio_fm' => 'FM1031',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929330&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929330.m3u8',
  ),
  418 => 
  array (
    'epg_id' => '1042929340',
    'radio_fm' => 'FM1017',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929340&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929340.m3u8',
  ),
  419 => 
  array (
    'epg_id' => '1042929350',
    'radio_fm' => 'FM888',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929350&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929350.m3u8',
  ),
  421 => 
  array (
    'epg_id' => '1042929360',
    'radio_fm' => 'FM912',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929360&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929360.m3u8',
  ),
  422 => 
  array (
    'epg_id' => '1042929370',
    'radio_fm' => 'FM1045',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929370&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929370.m3u8',
  ),
  423 => 
  array (
    'epg_id' => '1042929380',
    'radio_fm' => 'FM984',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929380&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929380.m3u8',
  ),
  425 => 
  array (
    'epg_id' => '1042929390',
    'radio_fm' => 'FM1028',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929390&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929390.m3u8',
  ),
  426 => 
  array (
    'epg_id' => '1042929400',
    'radio_fm' => 'FM961',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929400&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929400.m3u8',
  ),
  428 => 
  array (
    'epg_id' => '1042929410',
    'radio_fm' => 'FM938',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929410&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929410.m3u8',
  ),
  429 => 
  array (
    'epg_id' => '1042929420',
    'radio_fm' => 'FM97',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929420&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929420.m3u8',
  ),
  430 => 
  array (
    'epg_id' => '1042929430',
    'radio_fm' => 'FM104',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929430&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929430.m3u8',
  ),
  431 => 
  array (
    'epg_id' => '1042929440',
    'radio_fm' => 'FM1065',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929440&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929440.m3u8',
  ),
  432 => 
  array (
    'epg_id' => '1042929450',
    'radio_fm' => 'FM991',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929450&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929450.m3u8',
  ),
  433 => 
  array (
    'epg_id' => '1042929460',
    'radio_fm' => 'FM1053',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929460&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929460.m3u8',
  ),
  436 => 
  array (
    'epg_id' => '1042929470',
    'radio_fm' => 'FM893',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929470&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929470.m3u8',
  ),
  438 => 
  array (
    'epg_id' => '1042929480',
    'radio_fm' => 'FM908',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929480&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929480.m3u8',
  ),
  439 => 
  array (
    'epg_id' => '1042929490',
    'radio_fm' => 'FM966',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929490&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929490.m3u8',
  ),
  440 => 
  array (
    'epg_id' => '1042929500',
    'radio_fm' => 'AM747',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929500&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929500.m3u8',
  ),
  441 => 
  array (
    'epg_id' => '1042929510',
    'radio_fm' => 'FM1018',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929510&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929510.m3u8',
  ),
  442 => 
  array (
    'epg_id' => '1042929520',
    'radio_fm' => 'FM900',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929520&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929520.m3u8',
  ),
  444 => 
  array (
    'epg_id' => '1042929530',
    'radio_fm' => 'FM1066',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929530&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929530.m3u8',
  ),
  445 => 
  array (
    'epg_id' => '1042929540',
    'radio_fm' => 'AM1053',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929540&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929540.m3u8',
  ),
  446 => 
  array (
    'epg_id' => '1042929550',
    'radio_fm' => 'FM993',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929550&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929550.m3u8',
  ),
  448 => 
  array (
    'epg_id' => '1042929560',
    'radio_fm' => 'fm920',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929560&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929560.m3u8',
  ),
  449 => 
  array (
    'epg_id' => '1042929570',
    'radio_fm' => 'FM951',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929570&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929570.m3u8',
  ),
  450 => 
  array (
    'epg_id' => '1042929580',
    'radio_fm' => 'FM1077',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929580&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929580.m3u8',
  ),
  451 => 
  array (
    'epg_id' => '1042929590',
    'radio_fm' => 'FM1055',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929590&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929590.m3u8',
  ),
  466 => 
  array (
    'epg_id' => '1042929600',
    'radio_fm' => 'FM887',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929600&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929600.m3u8',
  ),
  468 => 
  array (
    'epg_id' => '1042929610',
    'radio_fm' => 'FM1067',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929610&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929610.m3u8',
  ),
  471 => 
  array (
    'epg_id' => '1042929620',
    'radio_fm' => 'FM906',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929620&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929620.m3u8',
  ),
  472 => 
  array (
    'epg_id' => '1042929630',
    'radio_fm' => 'FM933',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929630&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929630.m3u8',
  ),
  473 => 
  array (
    'epg_id' => '1042929640',
    'radio_fm' => 'FM1027',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929640&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929640.m3u8',
  ),
  474 => 
  array (
    'epg_id' => '1042929650',
    'radio_fm' => 'AM666',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929650&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929650.m3u8',
  ),
  475 => 
  array (
    'epg_id' => '1042929660',
    'radio_fm' => 'AM783',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929660&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929660.m3u8',
  ),
  476 => 
  array (
    'epg_id' => '1042929670',
    'radio_fm' => 'FM939',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929670&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929670.m3u8',
  ),
  477 => 
  array (
    'epg_id' => '1042929680',
    'radio_fm' => 'FM107',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929680&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929680.m3u8',
  ),
  490 => 
  array (
    'epg_id' => '1042929690',
    'radio_fm' => 'FM1049',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929690&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929690.m3u8',
  ),
  492 => 
  array (
    'epg_id' => '1042929700',
    'radio_fm' => 'FM883',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929700&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929700.m3u8',
  ),
  493 => 
  array (
    'epg_id' => '1042929710',
    'radio_fm' => 'FM969',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929710&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929710.m3u8',
  ),
  494 => 
  array (
    'epg_id' => '1042929720',
    'radio_fm' => 'FM997',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929720&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929720.m3u8',
  ),
  495 => 
  array (
    'epg_id' => '1042929730',
    'radio_fm' => 'FM986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929730&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929730.m3u8',
  ),
  496 => 
  array (
    'epg_id' => '1042929740',
    'radio_fm' => 'FM1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929740&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929740.m3u8',
  ),
  497 => 
  array (
    'epg_id' => '1042929750',
    'radio_fm' => 'FM886',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929750&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929750.m3u8',
  ),
  498 => 
  array (
    'epg_id' => '1042929760',
    'radio_fm' => 'FM907',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929760&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929760.m3u8',
  ),
  500 => 
  array (
    'epg_id' => '1042929770',
    'radio_fm' => 'FM926',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929770&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929770.m3u8',
  ),
  501 => 
  array (
    'epg_id' => '1042929780',
    'radio_fm' => 'FM876',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929780&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929780.m3u8',
  ),
  502 => 
  array (
    'epg_id' => '1042929790',
    'radio_fm' => 'AM1179',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929790&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929790.m3u8',
  ),
  503 => 
  array (
    'epg_id' => '1042929800',
    'radio_fm' => 'FM996',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929800&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929800.m3u8',
  ),
  504 => 
  array (
    'epg_id' => '1042929810',
    'radio_fm' => 'FM1043',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929810&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929810.m3u8',
  ),
  506 => 
  array (
    'epg_id' => '1042929820',
    'radio_fm' => 'AM747',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929820&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929820.m3u8',
  ),
  509 => 
  array (
    'epg_id' => '1042929830',
    'radio_fm' => 'FM1014',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929830&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929830.m3u8',
  ),
  510 => 
  array (
    'epg_id' => '1042929840',
    'radio_fm' => 'FM1046',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929840&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929840.m3u8',
  ),
  511 => 
  array (
    'epg_id' => '1042929850',
    'radio_fm' => 'FM1076',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929850&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929850.m3u8',
  ),
  512 => 
  array (
    'epg_id' => '1042929860',
    'radio_fm' => 'FM978',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929860&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929860.m3u8',
  ),
  513 => 
  array (
    'epg_id' => '1042929870',
    'radio_fm' => 'AM846',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929870&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929870.m3u8',
  ),
  515 => 
  array (
    'epg_id' => '1042929880',
    'radio_fm' => 'FM1045',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929880&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929880.m3u8',
  ),
  516 => 
  array (
    'epg_id' => '1042929890',
    'radio_fm' => 'FM1053',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929890&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929890.m3u8',
  ),
  517 => 
  array (
    'epg_id' => '1042929900',
    'radio_fm' => 'FM1035',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929900&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929900.m3u8',
  ),
  518 => 
  array (
    'epg_id' => '1042929910',
    'radio_fm' => 'AM1422',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929910&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929910.m3u8',
  ),
  519 => 
  array (
    'epg_id' => '1042929920',
    'radio_fm' => 'FM1065',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929920&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929920.m3u8',
  ),
  520 => 
  array (
    'epg_id' => '1042929930',
    'radio_fm' => 'FM1037',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929930&start=1345031668&end=1371297268',
    'mu' => 'http://dload.kandian.com:22111/1042929930.m3u8',
  ),
  521 => 
  array (
    'epg_id' => '1042929940',
    'radio_fm' => 'FM889',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929940&start=1345031829&end=1371297429',
    'mu' => 'http://dload.kandian.com:22111/1042929940.m3u8',
  ),
  522 => 
  array (
    'epg_id' => '1042929950',
    'radio_fm' => 'FM986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929950&start=1345031829&end=1371297429',
    'mu' => 'http://dload.kandian.com:22111/1042929950.m3u8',
  ),
  523 => 
  array (
    'epg_id' => '1042929960',
    'radio_fm' => 'FM883',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929960&start=1345031829&end=1371297429',
    'mu' => 'http://dload.kandian.com:22111/1042929960.m3u8',
  ),
  525 => 
  array (
    'epg_id' => '1042929970',
    'radio_fm' => 'FM971',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929970&start=1345031829&end=1371297429',
    'mu' => 'http://dload.kandian.com:22111/1042929970.m3u8',
  ),
  25 => 
  array (
    'epg_id' => '1042929980',
    'radio_fm' => 'FM934',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929980&start=1345031903&end=1371297503',
    'mu' => 'http://dload.kandian.com:22111/1042929980.m3u8',
  ),
  109 => 
  array (
    'epg_id' => '1042929990',
    'radio_fm' => 'FM909',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042929990&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042929990.m3u8',
  ),
  111 => 
  array (
    'epg_id' => '1042930000',
    'radio_fm' => 'FM1017',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930000&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930000.m3u8',
  ),
  123 => 
  array (
    'epg_id' => '1042930010',
    'radio_fm' => 'FM945',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930010&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930010.m3u8',
  ),
  134 => 
  array (
    'epg_id' => '1042930020',
    'radio_fm' => 'FM1011',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930020&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930020.m3u8',
  ),
  136 => 
  array (
    'epg_id' => '1042930030',
    'radio_fm' => 'FM881',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930030&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930030.m3u8',
  ),
  249 => 
  array (
    'epg_id' => '1042930040',
    'radio_fm' => 'fm881',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930040&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930040.m3u8',
  ),
  263 => 
  array (
    'epg_id' => '1042930050',
    'radio_fm' => 'FM971',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930050&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930050.m3u8',
  ),
  270 => 
  array (
    'epg_id' => '1042930060',
    'radio_fm' => 'fm938',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930060&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930060.m3u8',
  ),
  276 => 
  array (
    'epg_id' => '1042930070',
    'radio_fm' => 'FM886',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930070&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930070.m3u8',
  ),
  279 => 
  array (
    'epg_id' => '1042930080',
    'radio_fm' => 'fm1035',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930080&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930080.m3u8',
  ),
  295 => 
  array (
    'epg_id' => '1042930090',
    'radio_fm' => 'FM1064',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930090&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930090.m3u8',
  ),
  298 => 
  array (
    'epg_id' => '1042930100',
    'radio_fm' => 'fm955',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930100&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930100.m3u8',
  ),
  299 => 
  array (
    'epg_id' => '1042930110',
    'radio_fm' => 'FM1007',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930110&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930110.m3u8',
  ),
  300 => 
  array (
    'epg_id' => '1042930120',
    'radio_fm' => 'fm90',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930120&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930120.m3u8',
  ),
  301 => 
  array (
    'epg_id' => '1042930130',
    'radio_fm' => 'FM1041',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930130&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930130.m3u8',
  ),
  302 => 
  array (
    'epg_id' => '1042930140',
    'radio_fm' => 'FM1066',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930140&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930140.m3u8',
  ),
  321 => 
  array (
    'epg_id' => '1042930150',
    'radio_fm' => 'FM963',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930150&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930150.m3u8',
  ),
  344 => 
  array (
    'epg_id' => '1042930160',
    'radio_fm' => 'FM1003',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930160&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930160.m3u8',
  ),
  358 => 
  array (
    'epg_id' => '1042930170',
    'radio_fm' => 'FM986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930170&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930170.m3u8',
  ),
  376 => 
  array (
    'epg_id' => '1042930180',
    'radio_fm' => 'FM1011',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930180&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930180.m3u8',
  ),
  378 => 
  array (
    'epg_id' => '1042930190',
    'radio_fm' => 'FM897',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930190&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930190.m3u8',
  ),
  381 => 
  array (
    'epg_id' => '1042930200',
    'radio_fm' => 'FM975',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930200&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930200.m3u8',
  ),
  391 => 
  array (
    'epg_id' => '1042930210',
    'radio_fm' => 'FM1011',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930210&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930210.m3u8',
  ),
  393 => 
  array (
    'epg_id' => '1042930220',
    'radio_fm' => 'FM997',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930220&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930220.m3u8',
  ),
  394 => 
  array (
    'epg_id' => '1042930230',
    'radio_fm' => 'FM937',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930230&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930230.m3u8',
  ),
  397 => 
  array (
    'epg_id' => '1042930240',
    'radio_fm' => 'FM1017',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930240&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930240.m3u8',
  ),
  398 => 
  array (
    'epg_id' => '1042930250',
    'radio_fm' => 'FM901',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930250&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930250.m3u8',
  ),
  406 => 
  array (
    'epg_id' => '1042930260',
    'radio_fm' => 'FM963',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930260&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930260.m3u8',
  ),
  408 => 
  array (
    'epg_id' => '1042930270',
    'radio_fm' => 'AM585',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930270&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930270.m3u8',
  ),
  409 => 
  array (
    'epg_id' => '1042930280',
    'radio_fm' => 'AM1053',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930280&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930280.m3u8',
  ),
  410 => 
  array (
    'epg_id' => '1042930290',
    'radio_fm' => 'FM949',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930290&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930290.m3u8',
  ),
  414 => 
  array (
    'epg_id' => '1042930300',
    'radio_fm' => 'AM846',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930300&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930300.m3u8',
  ),
  415 => 
  array (
    'epg_id' => '1042930310',
    'radio_fm' => 'FM977',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930310&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930310.m3u8',
  ),
  420 => 
  array (
    'epg_id' => '1042930320',
    'radio_fm' => 'FM985',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930320&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930320.m3u8',
  ),
  424 => 
  array (
    'epg_id' => '1042930330',
    'radio_fm' => 'FM1039',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930330&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930330.m3u8',
  ),
  427 => 
  array (
    'epg_id' => '1042930340',
    'radio_fm' => 'FM991',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930340&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930340.m3u8',
  ),
  435 => 
  array (
    'epg_id' => '1042930350',
    'radio_fm' => 'FM1061',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930350&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930350.m3u8',
  ),
  467 => 
  array (
    'epg_id' => '1042930360',
    'radio_fm' => 'FM976',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930360&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930360.m3u8',
  ),
  470 => 
  array (
    'epg_id' => '1042930370',
    'radio_fm' => 'AM1206',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930370&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930370.m3u8',
  ),
  491 => 
  array (
    'epg_id' => '1042930380',
    'radio_fm' => 'FM1001',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930380&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930380.m3u8',
  ),
  499 => 
  array (
    'epg_id' => '1042930390',
    'radio_fm' => 'FM975',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930390&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930390.m3u8',
  ),
  505 => 
  array (
    'epg_id' => '1042930400',
    'radio_fm' => 'FM950',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930400&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930400.m3u8',
  ),
  507 => 
  array (
    'epg_id' => '1042930410',
    'radio_fm' => 'FM1021',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930410&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930410.m3u8',
  ),
  508 => 
  array (
    'epg_id' => '1042930420',
    'radio_fm' => 'FM919',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930420&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930420.m3u8',
  ),
  524 => 
  array (
    'epg_id' => '1042930430',
    'radio_fm' => 'FM949',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930430&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930430.m3u8',
  ),
  526 => 
  array (
    'epg_id' => '1042930440',
    'radio_fm' => 'AM1116',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930440&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930440.m3u8',
  ),
  527 => 
  array (
    'epg_id' => '1042930450',
    'radio_fm' => 'FM1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930450&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930450.m3u8',
  ),
  528 => 
  array (
    'epg_id' => '1042930460',
    'radio_fm' => 'FM919',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930460&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930460.m3u8',
  ),
  533 => 
  array (
    'epg_id' => '1042930470',
    'radio_fm' => 'FM1035',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930470&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930470.m3u8',
  ),
  534 => 
  array (
    'epg_id' => '1042930480',
    'radio_fm' => 'FM1065',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930480&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930480.m3u8',
  ),
  535 => 
  array (
    'epg_id' => '1042930490',
    'radio_fm' => 'FM910',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930490&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930490.m3u8',
  ),
  536 => 
  array (
    'epg_id' => '1042930500',
    'radio_fm' => 'AUTORADIO2011',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930500&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930500.m3u8',
  ),
  537 => 
  array (
    'epg_id' => '1042930510',
    'radio_fm' => 'FM1017',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930510&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930510.m3u8',
  ),
  538 => 
  array (
    'epg_id' => '1042930520',
    'radio_fm' => 'FM1074',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930520&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930520.m3u8',
  ),
  539 => 
  array (
    'epg_id' => '1042930530',
    'radio_fm' => 'FM982',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930530&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930530.m3u8',
  ),
  548 => 
  array (
    'epg_id' => '1042930540',
    'radio_fm' => 'FM1074',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930540&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930540.m3u8',
  ),
  549 => 
  array (
    'epg_id' => '1042930550',
    'radio_fm' => 'FM1049',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930550&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930550.m3u8',
  ),
  550 => 
  array (
    'epg_id' => '1042930560',
    'radio_fm' => 'FM1014',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930560&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930560.m3u8',
  ),
  551 => 
  array (
    'epg_id' => '1042930570',
    'radio_fm' => 'FM888',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930570&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930570.m3u8',
  ),
  552 => 
  array (
    'epg_id' => '1042930580',
    'radio_fm' => 'FM927',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930580&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930580.m3u8',
  ),
  553 => 
  array (
    'epg_id' => '1042930590',
    'radio_fm' => 'FM937',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930590&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930590.m3u8',
  ),
  554 => 
  array (
    'epg_id' => '1042930600',
    'radio_fm' => 'FM986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930600&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930600.m3u8',
  ),
  555 => 
  array (
    'epg_id' => '1042930610',
    'radio_fm' => 'FM1003',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930610&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930610.m3u8',
  ),
  556 => 
  array (
    'epg_id' => '1042930620',
    'radio_fm' => 'FM878',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930620&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930620.m3u8',
  ),
  557 => 
  array (
    'epg_id' => '1042930630',
    'radio_fm' => 'FM1056',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930630&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930630.m3u8',
  ),
  558 => 
  array (
    'epg_id' => '1042930640',
    'radio_fm' => 'FM1055',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930640&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930640.m3u8',
  ),
  559 => 
  array (
    'epg_id' => '1042930650',
    'radio_fm' => 'ZWHQ0318',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930650&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930650.m3u8',
  ),
  560 => 
  array (
    'epg_id' => '1042930660',
    'radio_fm' => 'fm936',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930660&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930660.m3u8',
  ),
  561 => 
  array (
    'epg_id' => '1042930670',
    'radio_fm' => 'fm100',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930670&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930670.m3u8',
  ),
  562 => 
  array (
    'epg_id' => '1042930680',
    'radio_fm' => 'fm970',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930680&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930680.m3u8',
  ),
  563 => 
  array (
    'epg_id' => '1042930690',
    'radio_fm' => 'fm101',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930690&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930690.m3u8',
  ),
  564 => 
  array (
    'epg_id' => '1042930700',
    'radio_fm' => 'fm1029',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930700&start=1345031904&end=1371297504',
    'mu' => 'http://dload.kandian.com:22111/1042930700.m3u8',
  ),
  565 => 
  array (
    'epg_id' => '1042930710',
    'radio_fm' => 'fm961',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930710&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930710.m3u8',
  ),
  568 => 
  array (
    'epg_id' => '1042930720',
    'radio_fm' => 'fm955',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930720&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930720.m3u8',
  ),
  569 => 
  array (
    'epg_id' => '1042930730',
    'radio_fm' => 'fm1038',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930730&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930730.m3u8',
  ),
  570 => 
  array (
    'epg_id' => '1042930740',
    'radio_fm' => 'fm1075',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930740&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930740.m3u8',
  ),
  571 => 
  array (
    'epg_id' => '1042930750',
    'radio_fm' => 'fm1839',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930750&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930750.m3u8',
  ),
  572 => 
  array (
    'epg_id' => '1042930760',
    'radio_fm' => 'fm954',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930760&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930760.m3u8',
  ),
  573 => 
  array (
    'epg_id' => '1042930770',
    'radio_fm' => 'fm1079',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930770&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930770.m3u8',
  ),
  574 => 
  array (
    'epg_id' => '1042930780',
    'radio_fm' => 'fm883',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930780&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930780.m3u8',
  ),
  575 => 
  array (
    'epg_id' => '1042930790',
    'radio_fm' => 'fm1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930790&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930790.m3u8',
  ),
  577 => 
  array (
    'epg_id' => '1042930800',
    'radio_fm' => 'fm900',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930800&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930800.m3u8',
  ),
  578 => 
  array (
    'epg_id' => '1042930810',
    'radio_fm' => 'fm889',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930810&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930810.m3u8',
  ),
  579 => 
  array (
    'epg_id' => '1042930820',
    'radio_fm' => 'qmoon911',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930820&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930820.m3u8',
  ),
  580 => 
  array (
    'epg_id' => '1042930830',
    'radio_fm' => 'qmoon55',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930830&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930830.m3u8',
  ),
  581 => 
  array (
    'epg_id' => '1042930840',
    'radio_fm' => 'fm951',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930840&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930840.m3u8',
  ),
  582 => 
  array (
    'epg_id' => '1042930850',
    'radio_fm' => 'hk32',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930850&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930850.m3u8',
  ),
  584 => 
  array (
    'epg_id' => '1042930860',
    'radio_fm' => 'huaxiazi2012',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930860&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930860.m3u8',
  ),
  585 => 
  array (
    'epg_id' => '1042930870',
    'radio_fm' => 'fm928',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930870&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930870.m3u8',
  ),
  586 => 
  array (
    'epg_id' => '1042930880',
    'radio_fm' => 'crionline2012',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930880&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930880.m3u8',
  ),
  587 => 
  array (
    'epg_id' => '1042930890',
    'radio_fm' => 'fm1069',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930890&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930890.m3u8',
  ),
  588 => 
  array (
    'epg_id' => '1042930900',
    'radio_fm' => 'fm931',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930900&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930900.m3u8',
  ),
  589 => 
  array (
    'epg_id' => '1042930910',
    'radio_fm' => 'fm1048',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930910&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930910.m3u8',
  ),
  590 => 
  array (
    'epg_id' => '1042930920',
    'radio_fm' => 'fm1015',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930920&start=1345031905&end=1371297505',
    'mu' => 'http://dload.kandian.com:22111/1042930920.m3u8',
  ),
  290 => 
  array (
    'epg_id' => '1042930930',
    'radio_fm' => 'fm1036',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930930&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930930.m3u8',
  ),
  411 => 
  array (
    'epg_id' => '1042930940',
    'radio_fm' => 'fm894',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930940&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930940.m3u8',
  ),
  591 => 
  array (
    'epg_id' => '1042930950',
    'radio_fm' => 'fm1067',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930950&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930950.m3u8',
  ),
  592 => 
  array (
    'epg_id' => '1042930960',
    'radio_fm' => 'fm905',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930960&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930960.m3u8',
  ),
  594 => 
  array (
    'epg_id' => '1042930970',
    'radio_fm' => 'am702',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930970&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930970.m3u8',
  ),
  595 => 
  array (
    'epg_id' => '1042930980',
    'radio_fm' => 'fm902',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930980&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930980.m3u8',
  ),
  596 => 
  array (
    'epg_id' => '1042930990',
    'radio_fm' => 'am954',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042930990&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042930990.m3u8',
  ),
  597 => 
  array (
    'epg_id' => '1042931000',
    'radio_fm' => 'fm1037',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931000&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931000.m3u8',
  ),
  602 => 
  array (
    'epg_id' => '1042931010',
    'radio_fm' => 'izemo1026',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931010&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931010.m3u8',
  ),
  608 => 
  array (
    'epg_id' => '1042931020',
    'radio_fm' => 'fm963',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931020&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931020.m3u8',
  ),
  610 => 
  array (
    'epg_id' => '1042931030',
    'radio_fm' => 'fm105',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931030&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931030.m3u8',
  ),
  611 => 
  array (
    'epg_id' => '1042931040',
    'radio_fm' => 'uradiohk22',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931040&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931040.m3u8',
  ),
  612 => 
  array (
    'epg_id' => '1042931050',
    'radio_fm' => 'fm1036',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931050&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931050.m3u8',
  ),
  615 => 
  array (
    'epg_id' => '1042931060',
    'radio_fm' => 'fm1058',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931060&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931060.m3u8',
  ),
  617 => 
  array (
    'epg_id' => '1042931070',
    'radio_fm' => 'fm903',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931070&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931070.m3u8',
  ),
  618 => 
  array (
    'epg_id' => '1042931080',
    'radio_fm' => 'fm893',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931080&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931080.m3u8',
  ),
  619 => 
  array (
    'epg_id' => '1042931090',
    'radio_fm' => 'am603',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931090&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931090.m3u8',
  ),
  620 => 
  array (
    'epg_id' => '1042931100',
    'radio_fm' => 'am1251',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931100&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931100.m3u8',
  ),
  632 => 
  array (
    'epg_id' => '1042931110',
    'radio_fm' => 'fm896',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931110&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931110.m3u8',
  ),
  633 => 
  array (
    'epg_id' => '1042931120',
    'radio_fm' => 'fm927',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931120&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931120.m3u8',
  ),
  634 => 
  array (
    'epg_id' => '1042931130',
    'radio_fm' => 'fm97',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931130&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931130.m3u8',
  ),
  636 => 
  array (
    'epg_id' => '1042931140',
    'radio_fm' => 'fm952',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931140&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931140.m3u8',
  ),
  637 => 
  array (
    'epg_id' => '1042931150',
    'radio_fm' => 'fm993',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931150&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931150.m3u8',
  ),
  649 => 
  array (
    'epg_id' => '1042931160',
    'radio_fm' => 'fm900',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931160&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931160.m3u8',
  ),
  650 => 
  array (
    'epg_id' => '1042931170',
    'radio_fm' => 'fmyhc1022',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931170&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931170.m3u8',
  ),
  651 => 
  array (
    'epg_id' => '1042931180',
    'radio_fm' => 'fm932',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931180&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931180.m3u8',
  ),
  652 => 
  array (
    'epg_id' => '1042931190',
    'radio_fm' => 'fm941',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931190&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931190.m3u8',
  ),
  653 => 
  array (
    'epg_id' => '1042931200',
    'radio_fm' => 'tiktok999',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931200&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931200.m3u8',
  ),
  654 => 
  array (
    'epg_id' => '1042931210',
    'radio_fm' => 'fm2012',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931210&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931210.m3u8',
  ),
  655 => 
  array (
    'epg_id' => '1042931220',
    'radio_fm' => 'fm975',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931220&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931220.m3u8',
  ),
  656 => 
  array (
    'epg_id' => '1042931230',
    'radio_fm' => 'fm895',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931230&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931230.m3u8',
  ),
  657 => 
  array (
    'epg_id' => '1042931240',
    'radio_fm' => 'fm959',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931240&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931240.m3u8',
  ),
  658 => 
  array (
    'epg_id' => '1042931250',
    'radio_fm' => 'fm1029',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931250&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931250.m3u8',
  ),
  659 => 
  array (
    'epg_id' => '1042931260',
    'radio_fm' => 'fm927',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931260&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931260.m3u8',
  ),
  660 => 
  array (
    'epg_id' => '1042931270',
    'radio_fm' => 'fm1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931270&start=1345031982&end=1371297582',
    'mu' => 'http://dload.kandian.com:22111/1042931270.m3u8',
  ),
  661 => 
  array (
    'epg_id' => '1042931280',
    'radio_fm' => 'fm987',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931280&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931280.m3u8',
  ),
  678 => 
  array (
    'epg_id' => '1042931290',
    'radio_fm' => 'fm979',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931290&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931290.m3u8',
  ),
  694 => 
  array (
    'epg_id' => '1042931300',
    'radio_fm' => 'fm948',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931300&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931300.m3u8',
  ),
  696 => 
  array (
    'epg_id' => '1042931310',
    'radio_fm' => 'fm958',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931310&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931310.m3u8',
  ),
  697 => 
  array (
    'epg_id' => '1042931320',
    'radio_fm' => 'myfm880',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931320&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931320.m3u8',
  ),
  698 => 
  array (
    'epg_id' => '1042931330',
    'radio_fm' => 'fm946',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931330&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931330.m3u8',
  ),
  699 => 
  array (
    'epg_id' => '1042931340',
    'radio_fm' => 'am1341',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931340&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931340.m3u8',
  ),
  700 => 
  array (
    'epg_id' => '1042931350',
    'radio_fm' => 'am1620',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931350&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931350.m3u8',
  ),
  701 => 
  array (
    'epg_id' => '1042931360',
    'radio_fm' => 'zhimadiantai2012',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931360&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931360.m3u8',
  ),
  702 => 
  array (
    'epg_id' => '1042931370',
    'radio_fm' => 'fm959',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931370&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931370.m3u8',
  ),
  703 => 
  array (
    'epg_id' => '1042931380',
    'radio_fm' => 'fm1039',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931380&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931380.m3u8',
  ),
  705 => 
  array (
    'epg_id' => '1042931390',
    'radio_fm' => 'cx521',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931390&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931390.m3u8',
  ),
  706 => 
  array (
    'epg_id' => '1042931400',
    'radio_fm' => 'fm988',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931400&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931400.m3u8',
  ),
  709 => 
  array (
    'epg_id' => '1042931410',
    'radio_fm' => 'fm1027',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931410&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931410.m3u8',
  ),
  712 => 
  array (
    'epg_id' => '1042931420',
    'radio_fm' => 'fm899',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931420&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931420.m3u8',
  ),
  714 => 
  array (
    'epg_id' => '1042931430',
    'radio_fm' => 'fm1029',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931430&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931430.m3u8',
  ),
  716 => 
  array (
    'epg_id' => '1042931440',
    'radio_fm' => 'fm1016',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931440&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931440.m3u8',
  ),
  717 => 
  array (
    'epg_id' => '1042931450',
    'radio_fm' => 'fm999',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931450&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931450.m3u8',
  ),
  718 => 
  array (
    'epg_id' => '1042931460',
    'radio_fm' => 'fm910',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931460&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931460.m3u8',
  ),
  720 => 
  array (
    'epg_id' => '1042931470',
    'radio_fm' => 'xj163',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931470&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931470.m3u8',
  ),
  721 => 
  array (
    'epg_id' => '1042931480',
    'radio_fm' => 'fm964',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931480&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931480.m3u8',
  ),
  725 => 
  array (
    'epg_id' => '1042931490',
    'radio_fm' => 'fm101',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931490&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931490.m3u8',
  ),
  728 => 
  array (
    'epg_id' => '1042931500',
    'radio_fm' => 'fm968',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931500&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931500.m3u8',
  ),
  730 => 
  array (
    'epg_id' => '1042931510',
    'radio_fm' => 'fm1019',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931510&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931510.m3u8',
  ),
  732 => 
  array (
    'epg_id' => '1042931520',
    'radio_fm' => 'fm940',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931520&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931520.m3u8',
  ),
  734 => 
  array (
    'epg_id' => '1042931530',
    'radio_fm' => 'fm1065',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931530&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931530.m3u8',
  ),
  735 => 
  array (
    'epg_id' => '1042931540',
    'radio_fm' => 'fm969',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931540&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931540.m3u8',
  ),
  736 => 
  array (
    'epg_id' => '1042931550',
    'radio_fm' => 'fm1026',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931550&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931550.m3u8',
  ),
  737 => 
  array (
    'epg_id' => '1042931560',
    'radio_fm' => 'fm940',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931560&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931560.m3u8',
  ),
  738 => 
  array (
    'epg_id' => '1042931570',
    'radio_fm' => 'fm1006',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931570&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931570.m3u8',
  ),
  739 => 
  array (
    'epg_id' => '1042931580',
    'radio_fm' => 'fm1003',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931580&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931580.m3u8',
  ),
  741 => 
  array (
    'epg_id' => '1042931590',
    'radio_fm' => 'fm1069',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931590&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931590.m3u8',
  ),
  742 => 
  array (
    'epg_id' => '1042931600',
    'radio_fm' => 'am1008',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931600&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931600.m3u8',
  ),
  743 => 
  array (
    'epg_id' => '1042931610',
    'radio_fm' => 'am1170',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931610&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931610.m3u8',
  ),
  744 => 
  array (
    'epg_id' => '1042931620',
    'radio_fm' => 'fm1024',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931620&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931620.m3u8',
  ),
  745 => 
  array (
    'epg_id' => '1042931630',
    'radio_fm' => 'fm981',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931630&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931630.m3u8',
  ),
  747 => 
  array (
    'epg_id' => '1042931640',
    'radio_fm' => 'am900',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931640&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931640.m3u8',
  ),
  748 => 
  array (
    'epg_id' => '1042931650',
    'radio_fm' => 'fm1058',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931650&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931650.m3u8',
  ),
  750 => 
  array (
    'epg_id' => '1042931660',
    'radio_fm' => 'fm1043',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931660&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931660.m3u8',
  ),
  751 => 
  array (
    'epg_id' => '1042931670',
    'radio_fm' => 'fm904',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931670&start=1345031983&end=1371297583',
    'mu' => 'http://dload.kandian.com:22111/1042931670.m3u8',
  ),
    753 => 
  array (
    'epg_id' => '1042931680',
    'radio_fm' => 'fm106',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931680&start=1345189001&end=1371454601',
    'mu' => 'http://dload.kandian.com:22111/1042931680.m3u8',
  ),
    754 => 
  array (
    'epg_id' => '1042931690',
    'radio_fm' => 'fm932',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931690&start=1345189002&end=1371454602',
    'mu' => 'http://dload.kandian.com:22111/1042931690.m3u8',
  ),
    756 => 
  array (
    'epg_id' => '1042931700',
    'radio_fm' => 'fm918',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931700&start=1345189002&end=1371454602',
    'mu' => 'http://dload.kandian.com:22111/1042931700.m3u8',
  ),
  757  => 
  array (
    'epg_id' => '1042931710',
    'radio_fm' => 'wradiofm133',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931710&start=1345189002&end=1371454602',
    'mu' => 'http://dload.kandian.com:22111/1042931710.m3u8',
  ),
  758  => 
  array (
    'epg_id' => '1042931720',
    'radio_fm' => 'fm981',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931720&start=1345189002&end=1371454602',
    'mu' => 'http://dload.kandian.com:22111/1042931720.m3u8',
  ),
    760 => 
  array (
    'epg_id' => '1042931800',
    'radio_fm' => 'fm883',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931800&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931800.m3u8',
  ),
   761 => 
  array (
    'epg_id' => '1042931810',
    'radio_fm' => 'fm1022',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931810&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931810.m3u8 ',
  ),
   762 => 
  array (
    'epg_id' => '1042931820',
    'radio_fm' => 'cfm986',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931820&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931820.m3u8',
  ),
   763 => 
  array (
    'epg_id' => '1042931830',
    'radio_fm' => 'cfm1075',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931830&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931830.m3u8',
  ),
   764 => 
  array (
    'epg_id' => '1042931840',
    'radio_fm' => 'cfm1065',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931840&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931840.m3u8',
  ),
   765 => 
  array (
    'epg_id' => '1042931850',
    'radio_fm' => 'cfm1043',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931850&start=1345619294&end=1371884894',
    'mu' => 'http://dload.kandian.com:22111/1042931850.m3u8',
  ),
  766 => 
  array (
    'epg_id' => '1042931860',
    'radio_fm' => 'fm965',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931860&start=1345619295&end=1371884895',
    'mu' => 'http://dload.kandian.com:22111/1042931860.m3u8',
  ),
    767 => 
  array (
    'epg_id' => '1042932030',
    'radio_fm' => 'fm967',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042932030&start=1346055579&end=1372321179',
    'mu' => 'http://dload.kandian.com:22111/1042932030.m3u8',
  ),
   768 => 
  array (
    'epg_id' => '1042931870',
    'radio_fm' => 'fm1234',
    'http' => 'http://cdn.kandian.com/movies?cmd=play&id=1042931870&start=1345619295&end=1371884895',
    'mu' => 'http://dload.kandian.com:22111/1042931870.m3u8',
  ),
   770 => 
  array (
    'epg_id' => '1042932190',
    'radio_fm' => 'fm948',
    'http' => 'http://cdn.kandian.com/movies.dll?cmd=play&id=1042932190&start=1346062205&end=1354011005',
    'mu' => 'http://dload.kandian.com:22111/1042932190.m3u8',
  ),
   791 => 
  array (
    'epg_id' => '1042932200',
    'radio_fm' => 'fm881',
    'http' => ' http://cdn.kandian.com/movies.dll?cmd=play&id=1042932200&start=1346062438&end=1354011238',
    'mu' => 'http://dload.kandian.com:22111/1042932200.m3u8',
  ),
);
  */
  
?>
