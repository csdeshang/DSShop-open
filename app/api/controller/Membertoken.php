<?php



namespace app\api\controller;
use think\facade\Db;
use think\facade\Lang;


class Membertoken extends MobileMember {

    protected $platform_userid = '';
    protected $platform_username = '';
    protected $platform_token = '';
    protected $platform_type = 'member';
    
    
    public function initialize() {
        parent::initialize();
        $this->platform_userid  = $this->member_info['member_id'];
        $this->platform_username  = $this->member_info['member_name'];
        $this->platform_token  = $this->member_info['member_token'];
    }
    
    
    //获取用户的Token列表(除了当前用户的Token)
    public function get_platformtoken_list(){
        $condition = array();
        $condition[] = array('platform_type','=',$this->platform_type);
        $condition[] = array('platform_userid','=',$this->platform_userid);
        $condition[] = array('platform_token','<>',$this->platform_token);
        
        
        $platformtoken_list = model('platformtoken')->getPlatformtokenList($condition);
        
        ds_json_encode(10000, '', array('platformtoken_list' => $platformtoken_list));
    }
    
    //获取当前用户的Token信息
    public function get_current_token(){
        
        $condition = array();
        $condition[] = array('platform_type','=',$this->platform_type);
        $condition[] = array('platform_userid','=',$this->platform_userid);
        $condition[] = array('platform_token','=',$this->platform_token);
        $platformtoken_info = model('platformtoken')->getPlatformtokenInfo($condition);
        if(empty($platformtoken_info)){
            ds_json_encode(10001, '获取当前用户信息错误');
        }else{
            ds_json_encode(10000, '', array('current_platformtoken' => $platformtoken_info));
        }
    }
    
    //获取特定用户的token信息
    public function get_platformtoken_info(){
        $platformtoken_id = input('param.platformtoken_id');
        
        $condition = array();
        $condition[] = array('platform_type','=',$this->platform_type);
        $condition[] = array('platform_userid','=',$this->platform_userid);
        $condition[] = array('platformtoken_id','=',$platformtoken_id);
        
        $platformtoken_info = model('platformtoken')->getPlatformtokenInfo($condition);
        
        if(empty($platformtoken_info)){
            ds_json_encode(10001, '获取当前用户信息错误');
        }else{
            ds_json_encode(10000, '', array('platformtoken_info' => $platformtoken_info));
        }
    }
    
    public function del_platformtoken_info(){
        $platformtoken_id = input('param.platformtoken_id');
        
        
        $condition = array();
        $condition[] = array('platform_type','=',$this->platform_type);
        $condition[] = array('platform_userid','=',$this->platform_userid);
        $condition[] = array('platformtoken_id','=',$platformtoken_id);
        
        $result = model('platformtoken')->delPlatformtoken($condition);
        
        if($result){
            ds_json_encode(10000, '删除设备成功');
        }else{
            ds_json_encode(10001, '删除设备失败');
        }
    }
    
    
}