<?php


namespace app\api\controller\trade;


use app\api\service\trade\TradeNotifyService;

use app\deshang\base\BaseService;


class TradeNotify extends BaseService
{




    // 支付回调
    public function notify(string $trade_channel, string $trade_scene, string $trade_type)
    {
        $result = (new TradeNotifyService())->notify($trade_channel, $trade_scene, $trade_type);
        return $result;
    }



}
