<?php

namespace app\common\model\store;

use app\deshang\base\BaseModel;
use app\common\enum\system\SysPrinterProviderEnum;

class TblStorePrinterModel extends BaseModel
{
    /**
     * 模型名称
     * @var string
     */
    protected $name = 'tbl_store_printer';

    // 获取器
    public function getPrinterProviderDescAttr($value, $data)
    {
        return SysPrinterProviderEnum::getPrinterProviderDesc($data['printer_provider']);
    }
}
