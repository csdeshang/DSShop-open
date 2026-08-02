<template>
    <view class="address-container">
        <view v-if="addressList.length === 0" class="empty-list">
            <image src="/static/images/empty/empty.png" mode="aspectFit" class="empty-image"></image>
            <text class="empty-text">暂无收货地址，请添加~</text>
        </view>
        <view v-else class="address-list">
            <view v-for="(address, index) in addressList" :key="address.id" class="address-item">
                <view class="address-info">

                    <view class="user-info">
                        <view class="default-switch" @click="setDefaultAddress(address.id)">
                            <uni-icons :type="address.is_default ? 'checkbox-filled' : 'checkbox'" size="24"
                                :color="address.is_default ? '#FF6034' : '#ddd'"></uni-icons>
                        </view>
                        <text class="name">{{ address.real_name }}</text>
                        <text class="phone">{{ address.mob_phone }}</text>
                        <text v-if="address.is_default" class="default-tag">默认</text>
                    </view>
                    <view class="address-detail">
                        <text>{{ address.area_info }} {{ address.address_detail }}</text>
                    </view>
                </view>
                <view class="divider"></view>
                <view class="actions">
                    <view class="action-btn edit" @click="editAddress(address.id)">
                        <uni-icons type="compose" size="16" color="#FF6034"></uni-icons>
                        <text>编辑</text>
                    </view>
                    <view class="action-btn delete" @click="deleteAddress(address.id)">
                        <uni-icons type="trash" size="16" color="#999"></uni-icons>
                        <text>删除</text>
                    </view>

                </view>
            </view>
        </view>
        <view class="add-address-btn" @click="addAddress">
            <uni-icons type="plusempty" size="20" color="#fff"></uni-icons>
            <text>新增收货地址</text>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import { getUserAddressList, deleteUserAddress, setDefaultUserAddress } from '@/home/api/user/userAddress';
import { goToPage } from '@/utils/util';

const addressList = ref([]);

const loadAddressList = () => {
    getUserAddressList().then((res) => {
        if (res.code === 10000) {
            addressList.value = res.data;
        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }
    });
};

const editAddress = (id: string) => {
    goToPage({ url: `/home/pages/user/address/edit?id=${id}` });
};

const deleteAddress = (id: string) => {
    uni.showModal({
        title: '提示',
        content: '确定要删除该地址吗？',
        success: (res) => {
            if (res.confirm) {
                deleteUserAddress(Number(id)).then((res) => {
                    if (res.code === 10000) {
                        uni.showToast({
                            title: '删除成功',
                            icon: 'success'
                        });
                        loadAddressList();
                    } else {
                        uni.showToast({
                            title: res.message,
                            icon: 'none'
                        });
                    }
                });
            }
        }
    });
};

const setDefaultAddress = (id: string) => {
    setDefaultUserAddress(Number(id)).then((res) => {
        if (res.code === 10000) {
            // 本地更新默认地址状态
            addressList.value.forEach((address) => {
                address.is_default = address.id === id;
            });
        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }
    });
};

const addAddress = () => {
    goToPage({ url: '/home/pages/user/address/edit' });
};

onShow(() => {
    loadAddressList();
});
</script>

<style lang="scss" scoped>
.address-container {
    min-height: 100vh;
    background: #f8f8f8;
    padding: 20rpx 30rpx;
    box-sizing: border-box;
    padding-bottom: 140rpx;
}

.empty-list {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding-top: 200rpx;

    .empty-image {
        width: 240rpx;
        height: 240rpx;
        margin-bottom: 30rpx;
    }

    .empty-text {
        font-size: 28rpx;
        color: #999;
    }
}

.address-list {
    .address-item {
        margin-bottom: 20rpx;
        border-radius: 16rpx;
        background: #fff;
        position: relative;
        overflow: hidden;


        .address-info {
            padding: 30rpx;

            .default-switch {
                padding: 10rpx;
            }

            .user-info {
                display: flex;
                align-items: center;
                margin-bottom: 16rpx;

                .name {
                    font-size: 32rpx;
                    color: #333;
                    font-weight: 600;
                    margin-right: 20rpx;
                }

                .phone {
                    font-size: 28rpx;
                    color: #666;
                    margin-right: 20rpx;
                }

                .default-tag {
                    font-size: 22rpx;
                    color: #FF6034;
                    background: rgba(255, 96, 52, 0.1);
                    padding: 4rpx 16rpx;
                    border-radius: 20rpx;
                }
            }

            .address-detail {
                font-size: 28rpx;
                color: #666;
                line-height: 1.5;
            }
        }

        .divider {
            height: 2rpx;
            background: #f5f5f5;
            margin: 0 30rpx;
        }

        .actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 20rpx 30rpx;
            gap: 30rpx;

            .action-btn {
                display: flex;
                align-items: center;
                gap: 6rpx;
                padding: 10rpx 20rpx;
                border-radius: 26rpx;
                transition: all 0.3s;

                text {
                    font-size: 26rpx;
                }

                &.edit {
                    background: rgba(255, 96, 52, 0.1);

                    text {
                        color: #FF6034;
                    }

                    &:active {
                        background: rgba(255, 96, 52, 0.2);
                    }
                }

                &.delete {
                    background: #f7f7f7;

                    text {
                        color: #999;
                    }

                    &:active {
                        background: #f0f0f0;
                    }
                }
            }


        }
    }
}

.add-address-btn {
    position: fixed;
    left: 30rpx;
    right: 30rpx;
    bottom: 40rpx;
    height: 88rpx;
    background: linear-gradient(135deg, #FF6034, #FF8C6A);
    border-radius: 44rpx;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10rpx;
    color: #fff;
    font-size: 30rpx;
    font-weight: 500;
    box-shadow: 0 6rpx 20rpx rgba(255, 96, 52, 0.2);
    transition: all 0.3s;

    &:active {
        transform: scale(0.98);
        box-shadow: 0 4rpx 12rpx rgba(255, 96, 52, 0.15);
    }
}
</style>
