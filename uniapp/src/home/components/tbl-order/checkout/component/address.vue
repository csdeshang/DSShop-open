<template>
    <view class="address-container">
        <view v-if="hasAddress" @click="selectAddress" class="address-info">
            <view class="icon-location">
                <uni-icons type="location-filled" size="30" color="#FF4C4C"></uni-icons>
            </view>
            <view class="user-info">
                <view class="contact-row">
                    <text class="name">{{ address.real_name }}</text>
                    <text class="phone">{{ address.mob_phone }}</text>
                </view>
                <view class="address-details">
                    <text v-if="address.is_default" class="default-tag">[默认]</text>
                    <text>{{ address.area_info }}{{ address.address_detail }}</text>
                </view>
            </view>
            <view class="arrow-icon">
                <uni-icons type="right" size="12" color="#4e545d"></uni-icons>
            </view>
        </view>
        <view v-else @click="goToPage({ url: '/home/pages/user/address/edit', params: { redirect: getCurrentPageUrl() } })" class="add-address">
            <uni-icons type="location-filled" size="30" color="#FF4C4C"></uni-icons>
            <text>请添加收货地址</text>
        </view>
    </view>

    <ds-user-address-picker ref="addressPicker" @confirm="handleAddressConfirm" />
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue';
import { goToPage, getCurrentPageUrl } from '@/utils/util'
import DsUserAddressPicker from '@/components/ds-user-address-picker/ds-user-address-picker.vue';


const props = defineProps({
    address: {
        type: Object,
        default: () => ({})
    }
});

// 检查地址是否存在
const hasAddress = computed(() => {
    return props.address && Object.keys(props.address).length > 0;
});

// 选择地址
const addressPicker = ref();
const selectAddress = () => {
    addressPicker.value.open();
};

const emit = defineEmits(['confirm']);
const handleAddressConfirm = (address: any) => {
    emit('confirm', address);
};


</script>

<style lang="scss" scoped>
.address-container {
  background-color: #ffffff;
  margin-bottom: 20rpx;
  border-radius: 12rpx;
  overflow: hidden;
  box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);
  
  .address-info {
    display: flex;
    padding: 30rpx 20rpx;
    align-items: center;
    position: relative;
    
    &:active {
      background-color: #f9f9f9;
    }
    
    .icon-location {
      margin-right: 15rpx;
    }
    
    .user-info {
      flex: 1;
      display: flex;
      flex-direction: column;
      
      .contact-row {
        display: flex;
        margin-bottom: 10rpx;
        
        .name {
          font-size: 28rpx;
          font-weight: bold;
          color: #333333;
          margin-right: 15rpx;
        }
        
        .phone {
          font-size: 26rpx;
          color: #666666;
        }
      }
      
      .address-details {
        font-size: 24rpx;
        color: #666666;
        line-height: 1.4;
        
        .default-tag {
          display: inline-block;
          color: #FF4C4C;
          background-color: rgba(255, 76, 76, 0.1);
          padding: 0 6rpx;
          border-radius: 4rpx;
          margin-right: 8rpx;
          font-size: 22rpx;
        }
      }
    }
    
    .arrow-icon {
      margin-left: 10rpx;
    }
  }
  
  .add-address {
    display: flex;
    
    align-items: center;
    padding: 30rpx;
    color: #666666;
    font-size: 28rpx;
    
    &:active {
      background-color: #f8f8f8;
    }
  }
}
</style>
