<?php

namespace app\common\model;
use think\facade\Db;

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
 * 数据层模型
 */
class Inviterclass extends BaseModel {

    //获取分销员所对应的等级
    public function getInviterclass($inviterclass_amount){
        $inviterclass_name='';
        $inviterclass_list=Db::name('inviterclass')->order('inviterclass_amount asc')->select()->toArray();
        foreach($inviterclass_list as $inviterclass){
            if($inviterclass_amount>=$inviterclass['inviterclass_amount']){
                $inviterclass_name=$inviterclass['inviterclass_name'];
            }
        }
        return $inviterclass_name;
    }

}
