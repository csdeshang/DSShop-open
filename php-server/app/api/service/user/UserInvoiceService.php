<?php

namespace app\api\service\user;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\NotFoundException;
use app\common\dao\user\UserInvoiceDao;

/**
 * 用户发票信息服务
 *
 * 负责用户常用发票信息的业务处理，包括列表、详情、增删改与默认项维护。
 */
class UserInvoiceService extends BaseUserService
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取当前用户发票信息列表
     *
     * @return array
     */
    public function getUserInvoiceList(): array
    {
        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        return (new UserInvoiceDao())->getUserInvoiceList($condition);
    }

    /**
     * 获取当前用户发票信息详情
     *
     * @param array $data
     * @return array
     */
    public function getUserInvoiceInfo(array $data): array
    {
        $condition = [];
        $condition[] = ['id', '=', $data['id']];
        $condition[] = ['user_id', '=', $this->user_id];
        $invoice_info = (new UserInvoiceDao())->getUserInvoiceInfo($condition);
        if (empty($invoice_info)) {
            throw new NotFoundException('发票信息不存在');
        }
        return $invoice_info;
    }

    /**
     * 创建用户发票信息
     *
     * 说明：
     * - 首条发票默认设置为默认项
     * - 若本次设置为默认，会先将该用户其他发票设为非默认
     *
     * @param array $data
     * @return bool
     */
    public function createUserInvoice(array $data): bool
    {
        $dao = new UserInvoiceDao();
        $data['user_id'] = $this->user_id;
        $data['create_at'] = time();
        $data['update_at'] = time();

        $has_invoice = $dao->getUserInvoiceCount([['user_id', '=', $this->user_id]]) > 0;
        if ($data['is_default'] == 1 || !$has_invoice) {
            $data['is_default'] = 1;
            $dao->updateUserInvoice([['user_id', '=', $this->user_id]], ['is_default' => 0, 'update_at' => time()]);
        }

        $dao->createUserInvoice($data);
        return true;
    }

    /**
     * 更新用户发票信息
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUserInvoice(int $id, array $data): bool
    {
        $dao = new UserInvoiceDao();

        $invoice_info = $dao->getUserInvoiceInfo([['id', '=', $id], ['user_id', '=', $this->user_id]]);
        if (empty($invoice_info)) {
            throw new NotFoundException('发票信息不存在');
        }

        $data['update_at'] = time();

        if ($data['is_default'] == 1) {
            // 设置其他发票为非默认
            $dao->updateUserInvoice([['user_id', '=', $this->user_id]], ['is_default' => 0, 'update_at' => time()]);
        }

        // 更新发票信息
        $dao->updateUserInvoice([['id', '=', $id], ['user_id', '=', $this->user_id]], $data);
        return true;
    }

    /**
     * 删除用户发票信息
     *
     * 说明：
     * - 删除默认项后，自动将剩余最新一条设为默认
     *
     * @param array $data
     * @return bool
     */
    public function deleteUserInvoice(array $data): bool
    {
        $dao = new UserInvoiceDao();

        $invoice_info = $dao->getUserInvoiceInfo([['id', '=', $data['id']], ['user_id', '=', $this->user_id]]);
        if (empty($invoice_info)) {
            throw new NotFoundException('发票信息不存在');
        }
        // 删除发票信息
        $dao->deleteUserInvoice([['id', '=', $data['id']], ['user_id', '=', $this->user_id]]);

        if ((int)$invoice_info['is_default'] === 1) {
            $list = $dao->getUserInvoiceList([['user_id', '=', $this->user_id]], 'id', 'id desc');
            if (!empty($list)) {
                $dao->updateUserInvoice([['id', '=', $list[0]['id']], ['user_id', '=', $this->user_id]], ['is_default' => 1, 'update_at' => time()]);
            }
        }

        return true;
    }

    /**
     * 设置默认用户发票信息
     *
     * @param array $data
     * @return bool
     */
    public function setDefaultUserInvoice(array $data): bool
    {
        $dao = new UserInvoiceDao();
        $this->getUserInvoiceInfo(['id' => $data['id']]);
        $dao->updateUserInvoice([['user_id', '=', $this->user_id]], ['is_default' => 0, 'update_at' => time()]);
        $dao->updateUserInvoice([['id', '=', $data['id']], ['user_id', '=', $this->user_id]], ['is_default' => 1, 'update_at' => time()]);
        return true;
    }
}

