<?php

/**
 * Project:     radio
 * File:        ajax_editprogram.php
 * 
 * 添加收藏电台
 * 
 * @copyright sina.com
 * @author 高超 <gaochao@staff.sina.com.cn>
 * @package radio
 */
include_once SERVER_ROOT . "config/radioconf.php";
include_once SERVER_ROOT . "control/radio/insertFunc.php";
class EditProgram extends control {
    protected function checkPara() {
        //判断来源合法性
        if(!Check::checkReferer()){
            $this->setCError('M00004','Refer来源错误');
            return false;
        }

        //获取参数
        $this->para['rid'] = intval(request::post('rid', 'STR'));
        $this->para['day'] = request::post('day', 'INT');
        if($this->para['day'] == 0){
            $this->para['day'] = 7;
        }
        $this->para['name'] = request::post('name', 'STR');
        $this->para['begintime'] = request::post('begintime', 'STR');
        $this->para['endtime'] = request::post('endtime', 'STR');
        $this->para['dj_info'] = request::post('dj_info', 'STR');
        $this->para['pid'] = request::post('pid', 'STR');//picture_id
        $this->para['program_id'] = request::post('program_id', 'STR');
        $this->para['program_type_id'] =  request::post('program_type_id', 'STR');
        $this->para['intro'] = request::post('intro', 'STR');
        $this->para['is_del'] = intval(request::post('is_del', 'STR'));

        $mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
        if($mRadio->checkKeyWord($this->para['name'].$this->para['dj_info'].$this->para['intro'])==false){
            $this->display(array('code'=>'contain illigal word','msg'=>'你输入的内容含有违禁词，请修改', 'data'=>array()), 'json');
            exit;
        }

        if(Check::utf8_strlen($this->para['intro']) > 100){
            $this->display(array('code'=>'intro too long','msg'=>'节目简介超过100字，请检查', 'data'=>array()), 'json');
            exit;
        }

        if(Check::utf8_strlen($this->para['name']) > 12){
            $this->display(array('code'=>'name too long','msg'=>'节目名称超过12字，请检查', 'data'=>array()), 'json');
            exit;
        }

        //测试数据
/*
        $this->para['rid'] = '31';
        $this->para['day'] = '1';
        $this->para['name'] = '节目1,节目2,节目3,节目4,节目5,节目6,节目7,节目8,节目9,节目10,节目11';
        $this->para['begintime'] = '8:00,9:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00';
        $this->para['endtime'] = '9:00,10:00,11:00,12:00,13:00,14:00,15:00,16:00,17:00,18:00,19:00';
        $this->para['dj_info'] = '1660386667#http://weibo.com/1660386667#小飞|1661558660#http://weibo.com/1661558660#喻舟,1563750565#http://weibo.com/1563750565#亚婕,##,1660386667#http://weibo.com/1660386667#小飞,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕,1563750565#http://weibo.com/1563750565#亚婕';
 */		
        ////参数检测处理
        if(empty($this->para['rid']) || empty($this->para['day'])) {
            $this->setCError('M00009', '参数错误');
            return false;
        }

        //身份校验
        $person = clsFactory::create(CLASS_PATH.'model','mPerson','service');		
        $cuserInfo = $person->currentUser();
        $cuid = !empty($cuserInfo['uid']) ? $cuserInfo['uid'] : 0;
        if($cuid > 0){
            $mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');
            $radioInfo = $mRadio->getRadioInfoByRid(array($this->para['rid']));
            $radioInfo = $radioInfo['result'][$this->para['rid']];
            //global $RADIO_ADMIN;
            $admin_id = $mRadio->getAllPowerList();
            $admin_id = $admin_id['result'];
            if($cuid != $radioInfo['admin_uid'] && !in_array($cuid,$admin_id)){
                $this->setCError('M00009', '参数错误');
                return false;
            }
        }
        else{
            $this->setCError('M00010', '请登陆');
            return false;
        }
        foreach($this->para as $k =>$para){
            if(is_string($para)){
                $this->para[$k] = strip_tags(htmlspecialchars_decode($para));
            }
        }
    }
    protected function action() {
        if($this->hasCError()) {
            $errors = $this->getCErrors();
            $this->display(array('code'=>$errors[0]['errorno'],'msg'=>$errors[0]['errormsg'], 'data'=>$errors[0]['errormsg']), 'json');
            return false;
        }
        $mRadio = clsFactory::create(CLASS_PATH . 'model/radio', 'mRadio', 'service');


        $name = $this->para['name'];//电台名称
        $begintime = $this->para['begintime'];
        $endtime = $this->para['endtime'];
        if(isset($begintime) && $begintime >= $endtime && $this->para['is_del'] != 1){
            $this->display(array('code'=>'begin time greater than endtime','msg'=>'节目开始时间应小于结束时间', 'data'=>array()), 'json');
            exit;
        }


        $dj_infos = explode(',',$this->para['dj_info']);//dj_info 使用逗号分隔
//		print_r($dj_infos);
//		exit;
        $program_type_id = explode(',', $this->para['program_type_id']); // 1,2,3,4  todo  判断个数不超过3个
        $pid = $this->para['pid'];
        $pid = !empty($pid) ? $pid : "";
        $pic_path = !empty($pid) ? RADIO_PIC_PATH.$pid.'.jpg' : "";

        if(count($dj_infos) >4){
            $this->display(array('code'=>'dj info greater than 3','msg'=>'dj信息超过4个', 'data'=>array()), 'json');
            exit;
        }
        //记录dj_info
        foreach($dj_infos as $k => $dj_info){
            $tmp = explode('#',$dj_info);
            if(!empty($tmp[0])){
                $info[$tmp[0]] = array('uid' => $tmp[0]
                    ,'url' => $tmp[1]
                    ,'screen_name' => $tmp[2]);

                if(isset($tmp[1])){
                    if( 0 !== strpos(trim($tmp[1]), 'http') ){
                        $this->display(array('code'=>'dj\'s weibo url is illegal','msg'=>$tmp[0].'链接输入错误', 'data'=>array()), 'json');
                        exit;
                    }

                }
//                if(Check::utf8_strlen($tmp[2]) > 6){
                if(Check::utf8_strlen($tmp[2]) > 20){
                    $this->display(array('code'=>'dj name length greater than 6','msg'=>$tmp[2].'dj名字超过6个字', 'data'=>array()), 'json');
                    exit;

                }
            }				
        }
        if(is_array($dj_infos) && is_array($info) && count($dj_infos) != count($info)){//如果添加dj数  不等于实际dj数(实际dj数用uid作为key 不会有重复的)
            $this->display(array('code'=>'repeated dj','msg'=>'请勿重复添加DJ', 'data'=>array()), 'json');
            exit;
        }

        $rid = intval($this->para['rid']);
        $day = intval($this->para['day']);
        $program_id = $this->para['program_id'];//如果有这个id 表示为更新
        $intro = $this->para['intro'];
        $program = array();
        $program['rid'] = $rid;
        $program['day'] = $day;
        $program['program_name']=$name;
        $program['begintime']=$begintime; 
        $program['endtime']=$endtime; 
        $program['pic_id']=$pid;
        $program['pic_path'] = $pic_path;
        $program['intro'] = !empty($intro) ? $intro : '';
        $program['dj_info'] = serialize($info);//坑爹的序列化啊!!
        if(!empty($program_id)){
            $program['program_id'] = $program_id;
        }

        //时间冲突判断 start
        if($this->para['is_del'] != 1){//删除时不需要判断时间冲突
            $data = $mRadio->getRadioProgram($rid, $day);
            $conflicts = Check::hasTimeConflict(unserialize($data['program_info']), $program);
            if(!empty($conflicts)){
                $this->display(array('code'=>'time conflict','msg'=>'节目时间输入有误，请检查您输入的开始/结束时间', 'data'=>$conflicts), 'json');
                exit;
            }
        }
        //时间冲突判断 end

        if(Check::is_token_expired()){
            $this->display(array('code'=>'token failed','msg'=>'请勿重复提交.', 'data'=>array()), 'json');
        }
        if(empty($program_id)){
            $ret = $mRadio->insertRadioProgram($program, $program_type_id);
            $this->display(array('code'=>$ret['errorno'], 'msg'=>'节目已成功添加', 'data'=>$ret['result']),'json');
            Check::delete_token();
            exit;
        }else if($this->para['is_del']) {
            $program['program_id'] = $program_id;
            $program['is_del'] = $this->para['is_del'];
            $ret = $mRadio->updateRadioProgram($program, $program_type_id);
            $this->display(array('code'=>$ret['errorno'], 'msg'=>'节目已成功删除', 'data'=>$ret['result']),'json');
            Check::delete_token();
            exit;
        }else{//update this program
            $ret = $mRadio->updateRadioProgram($program, $program_type_id);
            $this->display(array('code'=>$ret['errorno'], 'msg'=>'节目已成功更新', 'data'=>$ret['result']),'json');
            Check::delete_token();
            exit;
        }

    }
}

new EditProgram(RADIO_APP_SOURCE);
?>
