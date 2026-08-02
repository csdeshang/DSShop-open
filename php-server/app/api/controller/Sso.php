<?php

namespace app\api\controller;

use app\deshang\base\controller\BaseApiController;
use app\deshang\utils\JwtToken;
use app\deshang\exceptions\CommonException;

use app\api\service\login\LoginService;
use app\common\dao\user\UserDao;
use app\deshang\service\user\DeshangUserService;

use app\deshang\kv\KvManager;
use app\deshang\kv\keys\CacheKeyManager;

/**
 * SSO 单点登录（ticket 换 token）
 * 用于：商户PC、店铺PC、用户PC 之间免二次登录
 * 后台PC 不需要
 */
class Sso extends BaseApiController
{
    private const TICKET_EXPIRE = 60; // 60秒有效，一次性使用

    /**
     * 签发 ticket（需已登录，带 access-token）
     * POST /api/sso/ticket
     * 返回：{ ticket, expire_in }
     */
    public function ticket()
    {
        $token = $this->request->header('access-token', '');
        if (empty($token)) {
            throw new CommonException('请先登录');
        }

        $tokenInfo = (new JwtToken())->validateToken($token);
        if (empty($tokenInfo) || ($tokenInfo['role'] ?? '') !== 'user') {
            throw new CommonException('登录状态已失效，请重新登录');
        }

        $userId = (int)($tokenInfo['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new CommonException('用户信息异常');
        }

        $ticket = bin2hex(random_bytes(32));
        $cacheKey = sprintf(CacheKeyManager::SSO_TICKET_KEY, $ticket);
        $record = [
            'user_id' => $userId,
            'expires_at' => time() + self::TICKET_EXPIRE,
        ];
        // 与 TokenCache 等一致：走 KvManager（尊重 CACHE_ENABLED、默认/Redis 与全局配置一致）
        $saved = KvManager::cache()->set($cacheKey, $record, self::TICKET_EXPIRE, CacheKeyManager::SSO_TAG);
        if (!$saved) {
            throw new CommonException('SSO 票据写入失败，请检查缓存是否启用或缓存服务是否正常');
        }

        return ds_json_success('ok', [
            'ticket' => $ticket,
            'expire_in' => self::TICKET_EXPIRE,
        ]);
    }

    /**
     * 用 ticket 换 token（无需登录）
     * POST /api/sso/exchange
     * 参数：{ ticket }
     * 返回：{ access_token, refresh_token, userinfo } 与登录接口一致
     */
    public function exchange()
    {
        // 使用 param 兼容 JSON body / form-data / query
        $ticket = trim((string)($this->request->param('ticket', '')));
        if ($ticket === '') {
            throw new CommonException('ticket 不能为空');
        }
        // bin2hex(random_bytes(32))：64 位十六进制，i 忽略大小写
        if (!preg_match('/^[a-f0-9]{64}$/i', $ticket)) {
            throw new CommonException('ticket 格式无效');
        }

        $cacheKey = sprintf(CacheKeyManager::SSO_TICKET_KEY, $ticket);
        $record = KvManager::cache()->get($cacheKey);
        if (empty($record) || !is_array($record)) {
            throw new CommonException('ticket 无效或已过期');
        }
        // 立刻删除缓存，一次性消费；后续校验仅用内存中的 $record
        KvManager::cache()->delete($cacheKey);

        if (time() > (int)($record['expires_at'] ?? 0)) {
            throw new CommonException('ticket 已过期');
        }

        $userId = (int)($record['user_id'] ?? 0);
        if ($userId <= 0) {
            throw new CommonException('ticket 数据异常');
        }

        $user = (new UserDao())->getUserInfo([['id', '=', $userId], ['is_deleted', '=', 0]]);
        if (empty($user) || empty($user['id'])) {
            throw new CommonException('用户不存在');
        }
        if (isset($user['is_enabled']) && (int)$user['is_enabled'] !== 1) {
            throw new CommonException('用户已禁用');
        }

        $userInfo = (new DeshangUserService())->getUserInfoWithRoles($user);
        if (empty($userInfo) || empty($userInfo['id'])) {
            throw new CommonException('用户信息异常');
        }

        $result = (new LoginService())->generateUserToken($userInfo);

        return ds_json_success('ok', $result);
    }
}
