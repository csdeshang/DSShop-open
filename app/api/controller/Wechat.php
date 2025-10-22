<?php
/**
 * 公众号行为处理
 */

namespace app\api\controller;
use think\facade\Db;
use app\api\controller\WechatApi;
/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 微信控制器
 */
class Wechat extends MobileMall
{
    public $type;
    public $wxid;
    public $data;
    public $weixin;

    public function index()
    {
        //获取配置信息
        $wxConfig = model('wechat')->getOneWxconfig();
        $this->weixin = new WechatApi($wxConfig);
        $this->weixin->valid();
        $this->type = $this->weixin->getRev()->getRevType();  //获取消息类型MsgType
        $this->wxid = $this->weixin->getRev()->getRevFrom();  //获取消息类型MsgId
        $this->data = $this->weixin->getRevData();            //把获取的消息进行转码
        $reMsg = '';

        switch ($this->type) {
            //接收普通消息-文本消息
            case 'text':
                $content = $this->weixin->getRev()->getRevContent();
                break;
            //接收事件推送 事件类型，subscribe(订阅)、unsubscribe(取消订阅)
            case 'event':
                $event = $this->weixin->getRev()->getRevEvent();
                $content = json_encode($event);
                break;
            //接收普通消息-图片消息
            case 'image':
                $content = json_encode($this->weixin->getRev()->getRevPic());
                $reMsg = "图片很美！";
                break;
            default:
                $reMsg = '未识别信息';
        }
        /**
         *处理事件
         */
        if (!empty($reMsg)) {
            echo $this->weixin->text($reMsg)->reply();
            exit;
        }
        //一.接收事件推送
        if ($this->type == 'event') {
            //1.订阅(关注)事件
            if (isset($event['event']) && $event['event'] == 'subscribe') {
                $welcome = '欢迎关注';
                
                //当待了事件KEY值,则自动注册  KEY一般为推荐人ID
                if($event['key']){
                    $qrscene=explode("qrscene_", $event['key']);
                    $inviter_id=intval($qrscene[1]);
                    $config = model('wechat')->getOneWxconfig();
                    $wechat=new WechatApi($config);
                    $expire_time = $config['expires_in'];
                    if($expire_time > TIMESTAMP){
                        //有效期内
                        $wechat->access_token_= $config['access_token'];
                    }else{
                        $access_token=$wechat->checkAuth();
                        $web_expires = TIMESTAMP + 7000; // 提前200秒过期
                        Db::name('wxconfig')->where(array('id'=>$config['id']))->update(array('access_token'=>$access_token,'expires_in'=>$web_expires));
                    }
                    $userinfo=$wechat->getwxUserInfo($this->wxid);
                    $reg_info = array(
                        'member_h5_wxopenid' => $this->wxid,
                        'member_wxunionid' => $userinfo['unionid'],
                        'nickname' => isset($userinfo['nickname']) ? $userinfo['nickname'] : get_rand_nickname(),
                        'headimgurl' => isset($userinfo['headimgurl']) ? $userinfo['headimgurl'] : '',
                        'inviter_id' => $inviter_id
                    );

                    $logic_connect_api = model('connectapi', 'logic');
                    $wx_member = $logic_connect_api->wx_register($reg_info,'wx');
                    
                    if(!empty($wx_member)){
                        $member_model = model('member');
                        $member_model->getBuyerToken($wx_member['member_id'], $wx_member['member_name'],$wx_member['member_h5_wxopenid']);
                    }
                }
                
                $platformtoken_info = Db::name('platformtoken')->where('platform_openid',$this->wxid)->where('platform_type','member')->find();
                if(!empty($platformtoken_info)){
                    $ret_url='。系统已为您自动注册了一个账号，请<a href="'.config('ds_config.h5_site_url').'/pages/member/index/Index?key='.$platformtoken_info['platform_token'].'&username='.$platformtoken_info['platform_username'].'">点击修改信息</a>';
                }else{
                    $ret_url='';
                }
                echo $this->weixin->text($welcome.$ret_url)->reply();
                exit;
            }

            //2.扫码已关注
            if (isset($event['event']) && $event['event'] == 'SCAN') {
                $welcome = '已关注';
                echo $this->weixin->text($welcome)->reply(); 
                exit;
            }

            //4.点击菜单拉取消息时的事件推送
            if($event['event'] == 'CLICK'){
                $click=$event['key'];
                switch ($click) {
                    case "commend": //店铺推荐商品
                    case "hot":  //点击率商品
                    case "sale": //销售量
                    case "collect": //收藏量
                      $reMsg = $this->getGoods($click);
                    if(!empty($reMsg)) {
                        $this->MsgTypeNews($reMsg);
                    }else {
                        echo $this->weixin->text("success")->reply();
                        exit;
                    }
                    break;
                    //{后续可待添加}
                    default :
                        echo $this->weixin->text("未定义此菜单事件{$click}")->reply();
                        exit;
                }
            }
        }

        //二.文本消息(关键字回复/商品显示)
        if ($this->type == 'text') {
            //处理关键字
            $this->MsgTypeText($content);

            //处理商品的情况
            $reMsg = $this->getGoodsByKey($content);
            if(!empty($reMsg)) {
                $this->MsgTypeNews($reMsg);
            }
            /*处理其他输入文字*/
            echo $this->weixin->text("抱歉，暂时无法对您的输入作出处理。")->reply();
            exit;
        }
    }

    /**
    *文本格式消息回复
     */
    private function MsgTypeText($content)
    {
        //先处理是关键字的情况
        $value = $this->keywordsReply($content);
        if (!empty($value)) {
            echo $this->weixin->text($value['text'])->reply();
            exit;
        }
    }


    /**商品图文回复*/
    private function MsgTypeNews($reMsg){
            $k = 0;
            foreach ($reMsg as $v) {
                $newsData[$k]['Title'] = $v['goods_name'];
                $newsData[$k]['Description'] = strip_tags($v['goods_name']);
                $newsData[$k]['PicUrl'] = goods_cthumb($v['goods_image']);
                $newsData[$k]['Url'] = config('ds_config.h5_site_url') . '/pages/home/goodsdetail/Goodsdetail?goods_id='.$v['goods_id'];
                $k++;
            }
            echo $this->weixin->news($newsData)->reply();
            exit;
    }

    /**
     *关键字回复信息
     */
    public function keywordsReply($content)
    {
        //关键字查询
        $condtion['k.keyword'] = $content;
        $value = model('wechat')->getOneJoinWxkeyword($condtion,$field = 't.text');
        return $value;
    }

    /**关键字商品信息*/
    public function getGoodsByKey($key)
    {
        $condi = "(goods_name like '%{$key}%' or goods_advword like '%{$key}%')";
        $condi .= " and goods_state = 1";
        $res=Db::name('goods')->where($condi)->limit(4)->field('goods_id,goods_name,goods_image')->select()->toArray();
        $res=ds_change_arraykey($res,'goods_id');
        return $res;
    }

    /**菜单事件商品信息*/
    public function getGoods($type){
        //条件
        //后续可待添加
        $types=array('hot'=>'goods_click','sale'=>'goods_salenum','collect'=>'goods_collect','commend'=>'goods_commend');
        $condition = $types[$type].' DESC';
        $where = "goods_state = 1";
        $res = Db::name('goods')->field('goods_id,goods_name,goods_image')->where($where)->limit(4)->order($condition)->select()->toArray();
        $res=ds_change_arraykey($res,'goods_id');
        return $res;
    }
}