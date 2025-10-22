<?php

/**
 * 手机端买家令牌模型
 */

namespace app\common\model;
use think\facade\Db;


/**
 * ============================================================================
 * DSMall多用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 数据层模型
 */
class Platformtoken extends BaseModel {

    /**
     * 查询
     * @access public
     * @author csdeshang
     * @param array $condition 查询条件
     * @return array
     */
    public function getPlatformtokenInfo($condition) {
        
        $platformtoken = Db::name('platformtoken')->where($condition)->find();
        if(!empty($platformtoken)){
            $platformtoken['platform_logintime_desc'] = date('Y-m-d H:i:s',$platformtoken['platform_logintime']);
            $platformtoken['platform_operationtime_desc'] = date('Y-m-d H:i:s',$platformtoken['platform_operationtime']);
            //更新最近活跃时间
            if(TIMESTAMP - $platformtoken['platform_operationtime'] > 60*60){
                Db::name('platformtoken')->where($condition)->update(array('platform_operationtime' => TIMESTAMP));
            }
        }
        return $platformtoken;
    }
    
    
    /**
     * 编辑
     * @access public
     * @author csdeshang
     * @param type $token 令牌
     * @param type $openId ID
     * @return type
     */
    public function editMemberOpenId($token, $openId) {
        return Db::name('platformtoken')->where(array('platform_token' => $token,))->update(array('platform_openid' => $openId,));
    }

    /**
     * 新增
     * @access public
     * @author csdeshang
     * @param array $data 参数内容
     * @return bool 布尔类型的返回结果
     */
    public function addPlatformtoken($data) {
        return Db::name('platformtoken')->insertGetId($data);
    }

    /**
     * 删除
     * @access public
     * @author csdeshang
     * @param int $condition 条件
     * @return bool 布尔类型的返回结果
     */
    public function delPlatformtoken($condition) {
        return Db::name('platformtoken')->where($condition)->delete();
    }
    
    public function getPlatformtokenList($condition) {
        $platformtoken_list = Db::name('platformtoken')->where($condition)->select()->toArray();
        foreach ($platformtoken_list as $key => $platformtoken) {
            $platformtoken_list[$key]['platform_logintime_desc'] = date('Y-m-d H:i:s',$platformtoken['platform_logintime']);
            $platformtoken_list[$key]['platform_operationtime_desc'] = date('Y-m-d H:i:s',$platformtoken['platform_operationtime']);
        }
        return $platformtoken_list;
    }
    
    //清除member_id 关联 的所有token
    public function clearMembertoken($member_id){
        //删除member_id 下的token
        Db::name('platformtoken')->where('platform_userid', $member_id)->where('platform_type', 'member')->delete();
        //判断用户下是否绑定了卖家账户,一个店铺下可有多个卖家账户
        $seller_id = Db::name('seller')->where('member_id', $member_id)->value('seller_id');
        if(isset($seller_id) && $seller_id>0){
            Db::name('platformtoken')->where('platform_userid', $seller_id)->where('platform_type', 'seller')->delete();
        }
    }
    
    
}

?>
