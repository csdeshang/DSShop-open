<template>
    <uni-popup ref="popupRef" type="bottom">
        <view>
            <view class="ds-popup-header">
                <view class="cancel" @click="close">取消</view>
                <view class="title">请选择地址</view>
                <view class="confirm" @click="confirm">确定</view>
            </view>

            <scroll-view scroll-y="true" style="height:30vh;background-color: #fff;">
                <view class="address-container">
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

        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { getUserAddressList } from '@/home/api/user/userAddress';

const popupRef = ref();
const addressList = ref([]);

const open = () => {
    popupRef.value.open();
};

const close = () => {
    popupRef.value.close();
};

const emit = defineEmits(['confirm']);
// 确认选择
const confirm = (item: any) => {
    emit('confirm', item);
    close();
};


const loadAddressList = () => {
    getUserAddressList().then((res) => {
        addressList.value = res.data;
    });
};

loadAddressList();

defineExpose({
    open,
    close
});


</script>

<style lang="scss" scoped>
.address-container {
    margin: 30rpx;
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
</style>