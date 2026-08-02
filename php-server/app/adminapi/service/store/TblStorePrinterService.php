<?php

namespace app\adminapi\service\store;

use app\common\dao\order\TblOrderAddressDao;
use app\common\dao\order\TblOrderDao;
use app\common\dao\order\TblOrderGoodsDao;
use app\common\dao\store\TblStorePrinterDao;
use app\common\dao\store\TblStorePrinterLogDao;
use app\deshang\base\service\BaseAdminService;
use app\deshang\core\ThirdPartyLoader;
use app\deshang\exceptions\CommonException;
use app\deshang\service\system\DeshangPlatformProfileService;

/**
 * 店铺打印机服务
 */
class TblStorePrinterService extends BaseAdminService
{
    /**
     * 获取打印机分页列表
     */
    public function getTblStorePrinterPages(array $data): array
    {
        $condition = [];

        if (!empty($data['printer_name'])) {
            $condition[] = ['printer_name', 'like', '%' . $data['printer_name'] . '%'];
        }

        if (!empty($data['printer_sn'])) {
            $condition[] = ['printer_sn', 'like', '%' . $data['printer_sn'] . '%'];
        }

        if (!empty($data['printer_provider'])) {
            $condition[] = ['printer_provider', '=', $data['printer_provider']];
        }

        return (new TblStorePrinterDao())->getWithRelStorePrinterPages($condition);
    }

    /**
     * 获取打印机详情
     */
    public function getTblStorePrinterInfo(int $id): array
    {
        $printerInfo = (new TblStorePrinterDao())->getStorePrinterInfo([['id', '=', $id]]);
        if (empty($printerInfo)) {
            throw new CommonException('打印机不存在');
        }

        return $printerInfo;
    }

    /**
     * 创建打印机
     */
    public function createTblStorePrinter(array $data): int
    {
        return (new TblStorePrinterDao())->createStorePrinter($data);
    }

    /**
     * 更新打印机
     */
    public function updateTblStorePrinter(int $id, array $data): bool
    {
        $this->getTblStorePrinterInfo($id);

        return (new TblStorePrinterDao())->updateStorePrinter([['id', '=', $id]], $data);
    }

    /**
     * 删除打印机
     */
    public function deleteTblStorePrinter(int $id): int
    {
        $printerInfo = $this->getTblStorePrinterInfo($id);
        if ($printerInfo['printer_status'] == 1) {
            $this->unbindTblStorePrinter($id);
        }

        return (new TblStorePrinterDao())->deleteStorePrinter([['id', '=', $id]]);
    }

    /**
     * 绑定打印机
     */
    public function bindTblStorePrinter(int $printerId): bool
    {
        $printerInfo = $this->getTblStorePrinterInfo($printerId);

        if ($printerInfo['printer_status'] == 1) {
            throw new CommonException('打印机已经绑定');
        }

        $printerManager = ThirdPartyLoader::printer($printerInfo['printer_provider']);
        $result = $printerManager->addPrinter($printerInfo);

        if (!$result['success']) {
            throw new CommonException($result['message']);
        }

        return (new TblStorePrinterDao())->updateStorePrinter([['id', '=', $printerId]], [
            'printer_status' => 1,
        ]);
    }

    /**
     * 解绑打印机
     */
    public function unbindTblStorePrinter(int $printerId): bool
    {
        $printerInfo = $this->getTblStorePrinterInfo($printerId);

        if ($printerInfo['printer_status'] == 0) {
            throw new CommonException('打印机未绑定');
        }

        (new TblStorePrinterDao())->updateStorePrinter([['id', '=', $printerId]], [
            'printer_status' => 0,
        ]);

        $printerManager = ThirdPartyLoader::printer($printerInfo['printer_provider']);
        $result = $printerManager->deletePrinter($printerInfo);

        if (!$result['success']) {
            throw new CommonException($result['message']);
        }

        return true;
    }

    /**
     * 获取打印机日志分页列表
     */
    public function getTblStorePrinterLogPages(array $data): array
    {
        $condition = [];

        if (!empty($data['printer_id'])) {
            $condition[] = ['printer_id', '=', $data['printer_id']];
        }

        if (!empty($data['order_id'])) {
            $condition[] = ['order_id', '=', $data['order_id']];
        }

        if (isset($data['print_status']) && $data['print_status'] !== '') {
            $condition[] = ['print_status', '=', $data['print_status']];
        }

        if (!empty($data['print_type'])) {
            $condition[] = ['print_type', '=', $data['print_type']];
        }

        return (new TblStorePrinterLogDao())->getWithRelStorePrinterLogPages($condition);
    }

    /**
     * 打印订单小票
     */
    public function printTblOrder(int $orderId, int $printerId): bool
    {
        $printerInfo = $this->getTblStorePrinterInfo($printerId);

        if ($printerInfo['is_enabled'] != 1) {
            throw new CommonException('打印机未启用');
        }

        if ($printerInfo['printer_status'] != 1) {
            throw new CommonException('打印机未绑定');
        }

        $orderInfo = (new TblOrderDao())->getOrderInfo([
            ['id', '=', $orderId],
        ]);

        if (empty($orderInfo)) {
            throw new CommonException('订单不存在');
        }

        $orderGoods = (new TblOrderGoodsDao())->getOrderGoodsList([
            ['order_id', '=', $orderId],
        ]);

        $orderAddress = (new TblOrderAddressDao())->getOrderAddressInfo([
            ['order_id', '=', $orderId],
        ]);

        $printData = [
            'order_info' => $orderInfo,
            'order_goods' => $orderGoods,
            'order_address' => $orderAddress,
            'store_info' => [
                'store_name' => (new DeshangPlatformProfileService())->getPrintStoreName(),
            ],
        ];

        $printerManager = ThirdPartyLoader::printer($printerInfo['printer_provider']);
        $result = $printerManager->print($printerInfo, $printData, 'order');

        (new TblStorePrinterLogDao())->createStorePrinterLog([
            'printer_id' => $printerId,
            'order_id' => $orderId,
            'print_content' => json_encode($printData, JSON_UNESCAPED_UNICODE),
            'print_status' => $result['success'] ? 1 : 0,
            'print_result' => json_encode($result, JSON_UNESCAPED_UNICODE),
            'print_type' => 1,
        ]);

        if (!$result['success']) {
            throw new CommonException($result['message'] ?? '打印失败');
        }

        return true;
    }

    /**
     * 查询打印机状态
     */
    public function getTblStorePrinterStatus(int $printerId): array
    {
        $printerInfo = $this->getTblStorePrinterInfo($printerId);

        $printerManager = ThirdPartyLoader::printer($printerInfo['printer_provider']);
        $result = $printerManager->getPrinterStatus($printerInfo);

        if (!$result['success']) {
            throw new CommonException($result['message']);
        }

        return $result['data'];
    }
}
