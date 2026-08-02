<?php



namespace app\api\service\user;

use think\facade\Db;

use app\deshang\base\service\BaseUserService;
use app\deshang\exceptions\CommonException;


use app\common\dao\user\UserDao;
use app\common\dao\user\UserWithdrawalLogDao;
use app\common\dao\user\UserWithdrawalAccountDao;



use app\deshang\service\user\DeshangUserBalanceService;

use app\common\enum\user\UserBalanceEnum;
use app\common\enum\user\UserWithdrawalEnum;

class UserWithdrawalService extends BaseUserService
{

    public function __construct()
    {
        parent::__construct();

    }




    // 获取用户提现记录
    function getUserWithdrawalLogPages($data){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];

        $result = (new UserWithdrawalLogDao())->getWithRelWithdrawalLogPages($condition);
        return $result;

    }

    // 获取用户提现记录详情
    function getUserWithdrawalLogInfo($id){
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserWithdrawalLogDao())->getWithdrawalLogInfo($condition);
        return $result;
    }


    // 申请提现
    function applyUserWithdrawal($data){

        $amount = ds_commerce_money($data['amount'], 2);
        $account_id = $data['account_id'];


        // 判断提现金额是否大于最小提现金额
        $min_withdrawal_amount = ds_commerce_money(sysConfig('user_withdrawal:withdrawal_min_amount'), 2);
        if($amount < $min_withdrawal_amount){
            throw new CommonException('最低提现金额为'.$min_withdrawal_amount.'元');
        }

        // 判断提现金额是否大于用户余额
        $user_info = (new UserDao())->getUserInfo([['id', '=', $this->user_id]], 'balance');
        if($user_info['balance'] < $amount){
            throw new CommonException('提现金额大于用户余额');
        }

        // 获取用户选择的提现账户信息
        $account_info = (new UserWithdrawalAccountDao())->getWithdrawalAccountInfo([['id', '=', $account_id],['user_id', '=', $this->user_id]]);
        if(!$account_info){
            throw new CommonException('提现账户不存在');
        }

        // 获取提现手续费  0.001
        $fee_rate = round(sysConfig('user_withdrawal:withdrawal_fee_rate')/100, 4);


        // 计算提现手续费和提现金额
        $fee_amount = ds_commerce_money($amount * $fee_rate, 2);
        $withdrawal_amount = ds_commerce_money($amount - $fee_amount, 2);


        
        // 事务处理
        Db::startTrans();
        try {

            // 创建提现记录
            $log_id = (new UserWithdrawalLogDao())->createWithdrawalLog([
                'user_id' => $this->user_id,
                'out_transfer_no'=>generateOutTradeNo('transfer', $this->user_id),
                'transfer_type'=>'',
                'transfer_remark'=>'',
                'account_type'=>$account_info['account_type'],
                'account_name'=>$account_info['account_name'],
                'account_number'=>$account_info['account_number'],
                'account_holder'=>$account_info['account_holder'],
                'apply_amount'=>$amount,
                'fee_amount'=>$fee_amount,
                'withdrawal_amount'=>$withdrawal_amount,
                'status'=>UserWithdrawalEnum::STATUS_APPLY,
            ]);


            // 更新用户余额
            $result = (new DeshangUserBalanceService())->modifyUserBalance([
                'user_id' => $this->user_id,
                'related_id' => $log_id,
                'change_type' => UserBalanceEnum::TYPE_WITHDRAWAL,
                'change_mode' => UserBalanceEnum::MODE_DECREASE,
                'change_amount' => $amount,
                'change_desc' => '余额提现，手续费'.$fee_amount.'元，提现金额'.$withdrawal_amount.'元',
            ]);


            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            // 直接抛出原异常，保持异常类型（SystemException、PermissionException等）
            throw $e;
        }


        return true;
        
    }


    // 提现账户
    function getUserWithdrawalAccountList(){

        $condition = [];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserWithdrawalAccountDao())->getWithdrawalAccountList($condition);
        return $result;

    }

    // 提现账户详情
    function getUserWithdrawalAccountInfo($id){

        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserWithdrawalAccountDao())->getWithdrawalAccountInfo($condition);
        return $result;

    }


    // 添加提现账户
    function createUserWithdrawalAccount($data){
        $data['user_id'] = $this->user_id;
        $result = (new UserWithdrawalAccountDao())->createWithdrawalAccount($data);
        return $result;
    }
    
    
    // 修改提现账户
    function updateUserWithdrawalAccount($id,$data){
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserWithdrawalAccountDao())->updateWithdrawalAccount($condition, $data);
        return $result;
    }

    // 删除提现账户
    function deleteUserWithdrawalAccount($id){
        $condition = [];
        $condition[] = ['id', '=', $id];
        $condition[] = ['user_id', '=', $this->user_id];
        $result = (new UserWithdrawalAccountDao())->deleteWithdrawalAccount($condition);
        return $result;
    }
    
    
    
    





}