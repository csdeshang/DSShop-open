<template>
    <uni-popup ref="popupRef" type="bottom">
        <view>
            <view class="ds-popup-header">
                <view class="cancel" @click="close">取消</view>
                <view class="title">请选择地址</view>
                <view class="confirm" @click="confirm">确定</view>
            </view>

            <scroll-view scroll-y="true" class="address-scroll">
                <view class="address-container">
                    <view v-if="addressList.length === 0" class="empty-address">
                        <text>暂无收货地址，请添加</text>
                    </view>
                    <view class="address-item" v-for="item in addressList"
                        :key="item.id" @click="confirm(item)">
                        <view class="location-icon">
                            <uni-icons type="location-filled" size="30" color="#FF4C4C"></uni-icons>
                        </view>
                        <view class="address-info">
                            <view class="contact-info">
                                <view class="contact-name">{{ item.real_name }}</view>
                                <view class="contact-phone">{{ item.mob_phone }}</view>
                            </view>
                            <view class="address-detail">{{ item.area_info }}{{ item.address_detail }}</view>
                        </view>
                        <view class="arrow-icon">
                            <uni-icons type="right" size="12" color="#4e545d"></uni-icons>
                        </view>
                    </view>
                </view>
            </scroll-view>

            <view class="add-address" @click="handleAdd">
                <uni-icons type="plusempty" size="18" color="#FF4C4C"></uni-icons>
                <text>添加收货地址</text>
            </view>
        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { getUserAddressList } from '@/home/api/user/userAddress';

const popupRef = ref();
const addressList = ref([]);

const emit = defineEmits(['confirm', 'add']);

const loadAddressList = () => {
    getUserAddressList().then((res) => {
        addressList.value = res.data;
    });
};

const open = () => {
    loadAddressList();
    popupRef.value.open();
};

const close = () => {
    popupRef.value.close();
};

// 确认选择
const confirm = (item: any) => {
    emit('confirm', item);
    close();
};

const handleAdd = () => {
    close();
    emit('add');
};

defineExpose({
    open,
    close
});
</script>

<style lang="scss" scoped>
.address-scroll {
    height: 30vh;
    background-color: #fff;
}

.address-container {
    margin: 30rpx;
}

.empty-address {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 60rpx 0;
    color: #999;
    font-size: 26rpx;
}

.address-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    margin-bottom: 20rpx;
    border-bottom: 1px solid #eee;
    padding-bottom: 20rpx;
    
    .location-icon {
        padding-right: 10rpx;
    }
    
    .address-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        line-height: 1.5;
        
        .contact-info {
            display: flex;
            flex-direction: row;
            
            .contact-name, .contact-phone {
                font-weight: bold;
            }
        }
        .address-detail{
            font-size: $ds-font-sm;
        }
    }
}

.add-address {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8rpx;
    padding: 24rpx 30rpx;
    padding-bottom: calc(24rpx + env(safe-area-inset-bottom));
    background: #fff;
    border-top: 1px solid #eee;
    color: #FF4C4C;
    font-size: 28rpx;

    &:active {
        background-color: #f8f8f8;
    }
}
</style>
