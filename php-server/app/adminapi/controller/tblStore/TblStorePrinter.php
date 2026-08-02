<?php

namespace app\adminapi\controller\tblStore;

use app\deshang\base\controller\BaseAdminController;
use app\adminapi\service\store\TblStorePrinterService;

/**
 * @OA\Tag(name="admin-api/tblStore/TblStorePrinter", description="店铺打印机管理接口")
 */
class TblStorePrinter extends BaseAdminController
{
    /**
     * @OA\Get(
     *     path="/adminapi/tbl-store/printers/pages",
     *     summary="获取打印机分页列表",
     *     description="管理员查看打印机分页列表",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(
     *         name="printer_name",
     *         in="query",
     *         required=false,
     *         description="打印机名称搜索",
     *         @OA\Schema(type="string", example="收银台打印机")
     *     ),
     *     @OA\Parameter(
     *         name="printer_provider",
     *         in="query",
     *         required=false,
     *         description="打印机服务商",
     *         @OA\Schema(type="string", example="yilian")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTblStorePrinterPages()
    {
        $data = array(
            'printer_name' => input('param.printer_name', ''),
            'printer_sn' => input('param.printer_sn', ''),
            'printer_provider' => input('param.printer_provider', ''),
        );
        
        // 验证器
        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.pages');
        
        $result = (new TblStorePrinterService())->getTblStorePrinterPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-store/printers/{id}",
     *     summary="获取店铺打印机详情",
     *     description="管理员查看指定打印机的详细信息",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="打印机ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTblStorePrinterInfo($id)
    {
        // 验证器
        $this->validate(['id' => $id], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.info');
        
        $result = (new TblStorePrinterService())->getTblStorePrinterInfo((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-store/printer-logs/pages",
     *     summary="获取店铺打印机日志分页列表",
     *     description="管理员查看所有店铺的打印机日志分页列表",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(
     *         name="printer_name",
     *         in="query",
     *         required=false,
     *         description="打印机名称搜索",
     *         @OA\Schema(type="string", example="收银台打印机")
     *     ),
     *     @OA\Parameter(
     *         name="order_id",
     *         in="query",
     *         required=false,
     *         description="订单ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="print_status",
     *         in="query",
     *         required=false,
     *         description="打印状态：0失败 1成功",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="页码",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="page_size",
     *         in="query",
     *         required=false,
     *         description="每页数量",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="操作成功",
     *         @OA\JsonContent(
     *             @OA\Property(property="code", type="integer", example=10000),
     *             @OA\Property(property="msg", type="string", example="操作成功"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function getTblStorePrinterLogPages()
    {
        $data = array(
            'printer_id' => input('param.printer_id', ''),
            'order_id' => input('param.order_id', ''),
            'print_status' => input('param.print_status', ''),
            'print_type' => input('param.print_type', ''),
        );
        
        // 验证器
        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.logs');
        
        $result = (new TblStorePrinterService())->getTblStorePrinterLogPages($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/adminapi/tbl-store/printers",
     *     summary="添加店铺打印机",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"printer_name", "printer_sn", "printer_key", "printer_provider"},
     *             @OA\Property(property="printer_name", type="string", example="收银台打印机"),
     *             @OA\Property(property="printer_sn", type="string", example="4004684410"),
     *             @OA\Property(property="printer_key", type="string", example="536657963285"),
     *             @OA\Property(property="printer_mobile", type="string", example="13800138000"),
     *             @OA\Property(property="printer_provider", type="string", example="yilian"),
     *             @OA\Property(property="is_enabled", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function createTblStorePrinter()
    {
        $data = [
            'printer_name' => input('param.printer_name', ''),
            'printer_sn' => input('param.printer_sn', ''),
            'printer_key' => input('param.printer_key', ''),
            'printer_mobile' => input('param.printer_mobile', ''),
            'printer_provider' => input('param.printer_provider', ''),
            'is_enabled' => input('param.is_enabled', 1, 'intval'),
        ];

        $this->validate($data, 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.create');

        $result = (new TblStorePrinterService())->createTblStorePrinter($data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Put(
     *     path="/adminapi/tbl-store/printers/{id}",
     *     summary="更新店铺打印机",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(@OA\Property(property="printer_name", type="string"), @OA\Property(property="printer_sn", type="string"), @OA\Property(property="printer_key", type="string"), @OA\Property(property="printer_mobile", type="string"), @OA\Property(property="printer_provider", type="string"), @OA\Property(property="is_enabled", type="integer"))),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function updateTblStorePrinter($id)
    {
        $data = [
            'printer_name' => input('param.printer_name', ''),
            'printer_sn' => input('param.printer_sn', ''),
            'printer_key' => input('param.printer_key', ''),
            'printer_mobile' => input('param.printer_mobile', ''),
            'printer_provider' => input('param.printer_provider', ''),
            'is_enabled' => input('param.is_enabled', 1, 'intval'),
        ];

        $this->validate(array_merge(['id' => $id], $data), 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.update');

        $result = (new TblStorePrinterService())->updateTblStorePrinter((int)$id, $data);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Delete(
     *     path="/adminapi/tbl-store/printers/{id}",
     *     summary="删除店铺打印机",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function deleteTblStorePrinter($id)
    {
        $this->validate(['id' => $id], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.delete');

        $result = (new TblStorePrinterService())->deleteTblStorePrinter((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-store/printers/{id}/bind",
     *     summary="绑定打印机",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function bindTblStorePrinter($id)
    {
        $this->validate(['printer_id' => $id], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.bind');

        $result = (new TblStorePrinterService())->bindTblStorePrinter((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Patch(
     *     path="/adminapi/tbl-store/printers/{id}/unbind",
     *     summary="解绑打印机",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function unbindTblStorePrinter($id)
    {
        $this->validate(['printer_id' => $id], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.unbind');

        $result = (new TblStorePrinterService())->unbindTblStorePrinter((int)$id);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Post(
     *     path="/adminapi/tbl-store/printers/{id}/print-order",
     *     summary="打印订单小票",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(required=true, @OA\JsonContent(required={"order_id"}, @OA\Property(property="order_id", type="integer", example=1))),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function printTblOrder($id)
    {
        $orderId = input('param.order_id', 0, 'intval');
        $printerId = (int)$id;

        $this->validate([
            'order_id' => $orderId,
            'printer_id' => $printerId,
        ], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.print');

        $result = (new TblStorePrinterService())->printTblOrder($orderId, $printerId);
        return ds_json_success('操作成功', $result);
    }

    /**
     * @OA\Get(
     *     path="/adminapi/tbl-store/printers/{id}/status",
     *     summary="查询打印机状态",
     *     tags={"admin-api/tblStore/TblStorePrinter"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="操作成功", @OA\JsonContent(@OA\Property(property="code", type="integer", example=10000), @OA\Property(property="msg", type="string", example="操作成功"), @OA\Property(property="data", type="object")))
     * )
     */
    public function getTblStorePrinterStatus($id)
    {
        $this->validate(['printer_id' => $id], 'app\adminapi\controller\tblStore\validate\TblStorePrinterValidate.status');

        $result = (new TblStorePrinterService())->getTblStorePrinterStatus((int)$id);
        return ds_json_success('操作成功', $result);
    }
}