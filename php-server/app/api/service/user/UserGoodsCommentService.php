<?php



namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;

use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\goods\TblGoodsCommentDao;
use app\common\dao\goods\TblGoodsDao;
use app\common\dao\order\TblOrderDeliveryDao;
use app\common\dao\technician\TechnicianDao;
use app\common\dao\technician\TechnicianCommentDao;
use app\common\dao\rider\RiderDao;
use app\common\dao\rider\RiderCommentDao;




use app\common\enum\order\TblOrderEnum;

class UserGoodsCommentService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();
    }

    // 获取用户商品评论
    function getUserGoodsCommentPages($data)
    {

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new TblGoodsCommentDao())->getWithRelGoodsCommentPages($condition);
        return $result;
    }



    // 添加用户商品评论
    function addUserGoodsComment($data)
    {

        // 判断是否存在订单
        $condition = [];
        $condition[] = ['id', '=', $data['order_id']];
        $condition[] = ['user_id', '=', $this->user_id];
        $order_info = (new TblOrderDao())->getOrderInfo($condition);
        if (!$order_info) {
            throw new CommonException('订单不存在');
        }

        // 判断订单状态
        if ($order_info['order_status'] != TblOrderEnum::ORDER_STATUS_COMPLETED) {
            throw new CommonException('订单状态不正确');
        }

        // 订单是否已评价 （商品评价）
        if ($order_info['is_evaluate'] == 1) {
            throw new CommonException('订单已评价');
        }

        // 退款中或全额退款不可评价（与 getUserAvailableActions 中 evaluate 条件一致）
        if ($order_info['refunding_count'] > 0) {
            throw new CommonException('订单退款中，暂不可评价');
        }
        if ($order_info['refund_status'] == TblOrderEnum::REFUND_STATUS_FULL_REFUNDED) {
            throw new CommonException('订单已全额退款，不可评价');
        }


        // 判断是否存在评论
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $condition[] = ['order_id', '=', $data['order_id']];
        $comment_info = (new TblGoodsCommentDao())->getGoodsCommentInfo($condition);
        if ($comment_info) {
            throw new CommonException('评论已存在');
        }

        // 获取订单商品列表
        $condition = [];
        $condition[] = ['order_id', '=', $data['order_id']];
        $condition[] = ['user_id', '=', $this->user_id];
        $order_goods_list = (new TblOrderGoodsDao())->getOrderGoodsList($condition);
        if (!$order_goods_list) {
            throw new CommonException('订单商品不存在');
        }


        $goods_comments = $data['goods_comments'];

        if (count($goods_comments) != count($order_goods_list)) {
            throw new CommonException('商品评论数量与订单商品数量不一致');
        }

        // 将订单商品列表转换为以id为键的数组
        $order_goods_list = array_column($order_goods_list, null, 'id');

        $comment_data = [];

        // 更新商品评分
        $goods_ids = [];

        // 循环插入数据
        foreach ($goods_comments as $goods_comment) {

            $order_goods_info = $order_goods_list[$goods_comment['order_goods_id']];
            if (!$order_goods_info) {
                throw new CommonException('订单商品ID不匹配');
            }

            $comment_data[] = [
                'platform' => $order_info['platform'],
                'user_id' => $this->user_id,
                'order_id' => $data['order_id'],
                'order_goods_id' => $goods_comment['order_goods_id'],
                'goods_id' => $order_goods_info['goods_id'],
                'sku_id' => $order_goods_info['sku_id'],
                'content' => $goods_comment['content'],
                'images' => $goods_comment['images'],
                'is_anonymous' => $data['is_anonymous'],
                'goods_score' => $goods_comment['goods_score'],
                'describe_score' => $goods_comment['describe_score'],
                'logistics_score' => $goods_comment['logistics_score'],
                'service_score' => $goods_comment['service_score'],
                'is_show' => 1,
                'create_at' => time(),
                'update_at' => time()
            ];
            $goods_ids[] = $order_goods_info['goods_id'];
            // 自增商品评论数 [也可放事件 UserGoodsCommentListener 更新商品评分方法内]
            (new TblGoodsDao())->setGoodsInc([['id', '=', $order_goods_info['goods_id']]], 'evaluate_num');
        }


        // 批量插入数据
        $result = (new TblGoodsCommentDao())->createGoodsCommentAll($comment_data);
        if (!$result) {
            throw new CommonException('评论添加失败');
        }

        // 更新订单状态
        $condition = [];
        $condition[] = ['id', '=', $data['order_id']];
        $update_data = array(
            'is_evaluate' => 1,
            'evaluate_time' => time()
        );

        $result = (new TblOrderDao())->updateOrder($condition, $update_data);




        // 根据交付方式添加  骑手评价 师傅评价
        if (in_array($order_info['delivery_method'], [TblOrderEnum::DELIVERY_RIDER, TblOrderEnum::DELIVERY_TECHNICIAN])) {
            // 获取交付信息
            $delivery_info = (new TblOrderDeliveryDao())->getOrderDeliveryInfo(['order_id' => $order_info['id']]);
            if (empty($delivery_info)) {
                throw new CommonException('交付信息不存在');
            }

            if ($order_info['delivery_method'] == TblOrderEnum::DELIVERY_RIDER) {
                // 骑手评价
                $rider_info = (new RiderDao())->getRiderInfo(['id' => $delivery_info['rider_id']]);
                if (empty($rider_info)) {
                    throw new CommonException('骑手信息不存在');
                }
                $comment_data = [
                    'user_id' => $this->user_id,
                    'rider_id' => $delivery_info['rider_id'],
                    'order_id' => $data['order_id'],
                    'content' => $data['rider_content'],
                    'is_show' => 1,
                    'is_anonymous' => $data['is_anonymous'],
                    'service_score' => $data['rider_service_score'],
                ];
                // 插入骑手评价
                (new RiderCommentDao())->createRiderComment($comment_data);
                // 自增骑手评价数
                (new RiderDao())->setRiderInc([['id', '=', $delivery_info['rider_id']]], 'comment_count');
            }

            if ($order_info['delivery_method'] == TblOrderEnum::DELIVERY_TECHNICIAN) {
                // 师傅评价
                $technician_info = (new TechnicianDao())->getTechnicianInfo(['id' => $delivery_info['technician_id']]);
                if (empty($technician_info)) {
                    throw new CommonException('师傅信息不存在');
                }

                $comment_data = [
                    'user_id' => $this->user_id,
                    'technician_id' => $delivery_info['technician_id'],
                    'order_id' => $data['order_id'],
                    'content' => $data['technician_content'],
                    'is_show' => 1,
                    'is_anonymous' => $data['is_anonymous'],
                    'service_score' => $data['technician_service_score'],
                ];
                // 插入师傅评价
                (new TechnicianCommentDao())->createTechnicianComment($comment_data);
                // 自增师傅评价数
                (new TechnicianDao())->setTechnicianInc([['id', '=', $delivery_info['technician_id']]], 'comment_count');
            }
        }




        // 触发评论事件
        event('UserGoodsCommentListener', [
            'user_id' => $this->user_id,
            'order_id' => $data['order_id'],
            'goods_ids' => $goods_ids
        ]);



        return $result;
    }
}
