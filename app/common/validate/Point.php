<?php

namespace app\common\validate;


use think\Validate;
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
 * 验证器
 */
class Point extends Validate
{
    protected $rule = [
        'member_name'=>'require',
        'points_num'=>'number|min:1',
        'goodsname'=>'require',
        'goodsprice'=>'require',
        'goodspoints'=>'require|number',
        'goodsserial'=>'require',
        'goodsstorage'=>'require|number',
        'sort'=>'require|number',
        'limitnum'=>'checkPointLimitnum:1',
        'starttime'=>'checkPointStartTime:1',
        'endtime'=>'checkPointEndTime:1',
        'shippingcode'=>'require'
    ];
    protected $message = [
        'member_name.require'=>'会员信息错误，请重新填写会员名',
        'points_num.number'=>'积分值必须为数字',
        'points_num.min'=>'积分值必须大于0',
        'goodsname.require'=>'请添加礼品名称',
        'goodsprice.require'=>'礼品原价必须为数字且大于等于0',
        'goodspoints.require'=>'兑换积分为整数且大于等于0',
        'goodspoints.number'=>'兑换积分为整数且大于等于0',
        'goodsserial.require'=>'请添加礼品编号',
        'goodsstorage.require'=>'礼品库存必须为整数且大于等于0',
        'goodsstorage.number'=>'礼品库存必须为整数且大于等于0',
        'sort.require'=>'礼品排序为整数且大于等于0',
        'sort.number'=>'礼品排序为整数且大于等于0',
        'limitnum.checkPointLimitnum'=>'礼品排序为整数且大于等于0',
        'starttime.checkPointStartTime'=>'请添加开始时间',
        'endtime.checkPointEndTime'=>'请添加结束时间',
        'shippingcode.require'=>'请添加物流单号'
    ];
    protected $scene = [
        'pointslog' => ['member_name', 'points_num'],
        'prod_add' => ['goodsname', 'goodsprice', 'goodspoints', 'goodsserial', 'goodsstorage', 'sort', 'limitnum', 'starttime', 'endtime'],
        'prod_edit' => ['goodsname', 'goodsprice', 'goodspoints', 'goodsserial', 'goodsstorage', 'sort', 'limitnum', 'starttime', 'endtime'],
        'order_ship' => ['shippingcode'],
    ];

    protected function checkPointLimitnum($value)
    {
        if (input('post.sort') == 1 && !is_numeric($value)) {
            return '礼品排序为整数且大于等于0';
        }
        return true;
    }

    protected function checkPointStartTime($value)
    {
        if (input('post.islimittime')) {
            if (empty($value)) {
                return '请添加开始时间';
            }
        }
        return true;
    }

    protected function checkPointEndTime($value)
    {
        if (input('post.islimittime')) {
            if (empty($value)) {
                return '请添加结束时间';
            }
        }
        return true;
    }
}