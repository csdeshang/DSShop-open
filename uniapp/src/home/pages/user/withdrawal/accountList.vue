<template>
    <view class="account-list-page">


        <!-- 账户列表 -->
        <view class="account-list" v-if="accountList.length > 0">
            <view class="account-item" v-for="account in accountList" :key="account.id">
                <view class="account-info">
                    <view class="account-name">
                        <text class="type-tag">{{ getAccountTypeName(account.account_type) }}</text>
                        <text class="name">{{ account.account_name }}</text>
                        <text class="default-tag" v-if="account.is_default === 1">默认</text>
                    </view>

                    <text class="account-number">{{ account.account_number }}</text>
                    <text class="account-holder">{{ account.account_holder }}</text>
                </view>

                <view class="account-actions">
                    <view class="action-btn edit" @click.stop="editAccount(account)">
                        <uni-icons type="compose" size="20" color="#666"></uni-icons>
                    </view>
                    <view class="action-btn delete" @click.stop="deleteAccount(account.id)">
                        <uni-icons type="trash" size="20" color="#666"></uni-icons>
                    </view>
                </view>
            </view>
        </view>

        <view v-else>
            <DsEmpty :text="'暂无提现账户'" :description="'添加账户以便快速提现'" />
        </view>

        <!-- 添加账户按钮 -->
        <button class="ds-btn-lg" @click="addAccount">
            <uni-icons type="plusempty" size="20" color="#fff"></uni-icons>
            <text class="button-text">添加提现账户</text>
        </button>

        <!-- 编辑账户弹窗 -->
        <account-edit ref="accountEditRef" @success="handleSuccess" />
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import { getUserWithdrawalAccountList, deleteUserWithdrawalAccount } from '@/home/api/user/userWithdrawal';
import AccountEdit from './accountEdit.vue';
import DsEmpty from '@/components/ds-empty/index.vue';

interface AccountInfo {
    id: number;
    user_id: number;
    account_type: string;
    account_name: string;
    account_number: string;
    account_holder: string;
    is_default: number;
    create_at: number;
    update_at: string;
}

const accountList = ref<AccountInfo[]>([]);







// 获取账户类型名称
const getAccountTypeName = (type: string) => {
    switch (type) {
        case 'bank':
            return '银行卡';
        case 'alipay':
            return '支付宝';
        case 'wechat':
            return '微信';
        default:
            return '未知';
    }
};

// 加载账户列表
const loadAccountList = async () => {
    try {
        const res = await getUserWithdrawalAccountList();
        if (res.code === 10000 && res.data) {
            accountList.value = res.data;
        }
    } catch (error) {
        console.error('获取提现账户失败:', error);
        uni.showToast({
            title: '获取账户列表失败',
            icon: 'none'
        });
    }
};

// 添加账户
const accountEditRef = ref();
const addAccount = () => {

    accountEditRef.value.setDialogData();
    accountEditRef.value.openDialog();


};

// 编辑账户
const editAccount = (account: AccountInfo) => {
    accountEditRef.value.setDialogData(account);

    accountEditRef.value.openDialog();
};

// 删除账户
const deleteAccount = (id: number) => {
    uni.showModal({
        title: '提示',
        content: '确定要删除此提现账户吗？',
        success: async (res) => {
            if (res.confirm) {
                try {
                    const result = await deleteUserWithdrawalAccount(id);
                    if (result.code === 10000) {
                        uni.showToast({
                            title: '删除成功',
                            icon: 'success'
                        });
                        await loadAccountList();
                    } else {
                        uni.showToast({
                            title: result.message || '删除失败',
                            icon: 'none'
                        });
                    }
                } catch (error) {
                    console.error('删除账户失败:', error);
                    uni.showToast({
                        title: '删除失败',
                        icon: 'none'
                    });
                }
            }
        }
    });
};

// 处理编辑/添加成功
const handleSuccess = async () => {
    await loadAccountList();
};

onShow(() => {
    loadAccountList();
});
</script>

<style scoped lang="scss">
.account-list-page {
    min-height: 100vh;
    background: #f8f8f8;
    padding: 30rpx;
}



.account-list {
    margin-bottom: 40rpx;

        .account-item {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 12rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        position: relative;
        box-shadow: 0 2rpx 8rpx rgba(0, 0, 0, 0.05);

        .account-info {
            flex: 1;

            .account-name {
                display: flex;
                align-items: center;
                margin-bottom: 8rpx;
                flex-wrap: wrap;
                gap: 10rpx;

                .name {
                    font-size: 30rpx;
                    font-weight: bold;
                    color: #333;
                }

                .type-tag {
                    font-size: 22rpx;
                    color: #666;
                    background: #f0f0f0;
                    padding: 4rpx 12rpx;
                    border-radius: 4rpx;
                }

                .default-tag {
                    font-size: 22rpx;
                    color: #fff;
                    background: #FF4444;
                    padding: 4rpx 10rpx;
                    border-radius: 4rpx;
                }
            }

            .account-number {
                font-size: 28rpx;
                color: #666;
                margin-bottom: 8rpx;
                display: block;
            }

            .account-holder {
                font-size: 26rpx;
                color: #999;
            }
        }

        .account-actions {
            display: flex;
            align-items: center;

            .action-btn {
                width: 60rpx;
                height: 60rpx;
                display: flex;
                justify-content: center;
                align-items: center;
                margin-left: 10rpx;

                &.edit {
                    color: #3399FF;
                }

                &.delete {
                    color: #FF4444;
                }
            }
        }
    }
}
</style>
