<?php

namespace app\deshang\third_party\printer\providers;
use app\deshang\core\InternalResultHelper;
use app\deshang\exceptions\CommonException;


use GuzzleHttp\Client;

/**
 * 新业打印机驱动
 */
class Xinye extends BasePrinter
{
    private $client;
    private $apiUrl = 'http://www.xpyun.net/api/openapi';

    public function __construct(array $config)
    {
        parent::__construct($config);
        $this->client = new Client([
            'timeout' => 30,
            'connect_timeout' => 10
        ]);
    }

    /**
     * 打印小票
     */
    public function print(array $printerInfo, array $printerData, string $template = 'default'): array
    {
        throw new CommonException('打印异常：打印机不支持打印');
    }

    /**
     * 查询打印机状态
     */
    public function getPrinterStatus(array $printerInfo): array
    {
        throw new CommonException('打印异常：打印机不支持打印');
    }

    /**
     * 添加打印机
     */
    public function addPrinter(array $printerInfo): array
    {
        throw new CommonException('打印异常：打印机不支持打印');
    }

    /**
     * 删除打印机
     */
    public function deletePrinter(array $printerInfo): array
    {
        throw new CommonException('打印异常：打印机不支持打印');
    }

    /**
     * 格式化打印内容
     */
    private function formatContent(array $data, string $template): string
    {
        switch ($template) {
            case 'order':
                return $this->formatOrderContent($data);
            case 'receipt':
                return $this->formatReceiptContent($data);
            default:
                return $this->formatDefaultContent($data);
        }
    }

    /**
     * 格式化订单内容（兼容嵌套 printData 与扁平字段）
     */
    private function formatOrderContent(array $data): string
    {
        $orderInfo = $data['order_info'] ?? $data;
        $orderGoods = $data['order_goods'] ?? ($data['goods_list'] ?? []);
        $orderAddress = $data['order_address'] ?? [];
        $storeName = $data['store_info']['store_name']
            ?? $data['store_name']
            ?? '平台商城';

        $orderSn = $orderInfo['order_sn'] ?? ($data['order_sn'] ?? '');
        $addTime = $orderInfo['add_time'] ?? ($data['create_time'] ?? time());
        if (is_numeric($addTime)) {
            $addTime = date('Y-m-d H:i:s', (int) $addTime);
        }

        $content = "<CB>{$storeName}</CB><BR>";
        $content .= "<B>订单号：{$orderSn}</B><BR>";
        $content .= "下单时间：{$addTime}<BR>";
        $content .= "--------------------------------<BR>";

        foreach ($orderGoods as $goods) {
            $name = $goods['goods_name'] ?? '';
            $num = $goods['goods_num'] ?? ($goods['quantity'] ?? 1);
            $price = $goods['pay_price'] ?? ($goods['goods_price'] ?? 0);
            $content .= "{$name} x{$num}<BR>";
            $content .= "  ￥{$price}<BR>";
        }

        $content .= "--------------------------------<BR>";
        $content .= "商品总额：￥" . ($orderInfo['goods_amount'] ?? ($data['goods_amount'] ?? 0)) . "<BR>";
        $content .= "配送费：￥" . ($orderInfo['shipping_amount'] ?? ($data['shipping_amount'] ?? 0)) . "<BR>";
        $content .= "<B>订单总额：￥" . ($orderInfo['order_amount'] ?? ($data['order_amount'] ?? 0)) . "</B><BR>";
        $content .= "--------------------------------<BR>";
        $content .= "收货地址：" . ($orderAddress['reciver_address'] ?? ($data['address'] ?? '')) . "<BR>";
        $content .= "联系电话：" . ($orderAddress['reciver_mobile'] ?? ($data['phone'] ?? '')) . "<BR>";
        $content .= "--------------------------------<BR>";
        $content .= "<QR>https://example.com/order/{$orderSn}</QR><BR>";

        return $content;
    }

    /**
     * 格式化收据内容
     */
    private function formatReceiptContent(array $data): string
    {
        $content = "<CB>收据</CB><BR>";
        $content .= "时间：{$data['create_time']}<BR>";
        $content .= "金额：￥{$data['amount']}<BR>";
        $content .= "--------------------------------<BR>";
        
        return $content;
    }

    /**
     * 格式化默认内容
     */
    private function formatDefaultContent(array $data): string
    {
        $content = "<CB>打印测试</CB><BR>";
        $content .= "时间：" . date('Y-m-d H:i:s') . "<BR>";
        $content .= "内容：{$data['content']}<BR>";
        $content .= "--------------------------------<BR>";
        
        return $content;
    }
} 