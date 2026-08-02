<?php

namespace app\deshang\service\system;

use app\deshang\service\BaseDeshangService;

/**
 * 平台自营资料（发货地 / 打印抬头等）
 */
class DeshangPlatformProfileService extends BaseDeshangService
{
    /**
     * 获取平台发货地信息
     * 仅保留运费计算、订单发货人、打印抬头实际用到的字段
     */
    public function getShipFromInfo(): array
    {
        $website = $this->getWebsiteConfig();

        return [
            'store_name' => $this->resolveStoreName($website),
            'contact_phone' => (string) ($website['website_phone'] ?? ''),
            'address' => (string) ($website['website_address'] ?? ''),
            'store_longitude' => (string) ($website['ship_longitude'] ?? ''),
            'store_latitude' => (string) ($website['ship_latitude'] ?? ''),
            'area_id' => (int) ($website['ship_area_id'] ?? 0),
        ];
    }

    /**
     * 打印抬头名称
     */
    public function getPrintStoreName(): string
    {
        return $this->resolveStoreName($this->getWebsiteConfig());
    }

    private function resolveStoreName(array $website): string
    {
        $storeName = trim((string) ($website['h5_site_name'] ?? ''));
        if ($storeName === '') {
            $storeName = trim((string) ($website['pc_site_name'] ?? ''));
        }
        if ($storeName === '') {
            $storeName = trim((string) ($website['admin_site_name'] ?? ''));
        }
        if ($storeName === '') {
            $storeName = '平台商城';
        }
        return $storeName;
    }

    private function getWebsiteConfig(): array
    {
        try {
            return (new DeshangSysConfigService())->getSysConfigByType('website', false);
        } catch (\Throwable $e) {
            return [];
        }
    }
}
