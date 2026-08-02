<template>
    <view class="contact-us">
        <view class="contact-us-title">联系客服</view>

        <!-- 企业信息卡片 -->
        <view class="contact-card">
            <!-- 工作时间 -->
            <view class="contact-item" v-if="websiteConfig.website_work_hours">
                <view class="item-icon">
                    <uni-icons type="calendar" size="24" color="#FF4141"></uni-icons>
                </view>
                <view class="item-content">
                    <view class="item-label">工作时间</view>
                    <view class="item-value">{{ websiteConfig.website_work_hours }}</view>
                </view>
            </view>

            <!-- 联系电话 -->
            <view class="contact-item" v-if="websiteConfig.website_phone"
                @click="callPhone(websiteConfig.website_phone)">
                <view class="item-icon">
                    <uni-icons type="phone" size="24" color="#FF4141"></uni-icons>
                </view>
                <view class="item-content">
                    <view class="item-label">联系电话</view>
                    <view class="item-value">{{ websiteConfig.website_phone }}</view>
                </view>
            </view>

            <!-- 邮箱 -->
            <view class="contact-item" v-if="websiteConfig.website_email"
                @click="copyText(websiteConfig.website_email, '邮箱已复制')">
                <view class="item-icon">
                    <uni-icons type="email" size="24" color="#FF4141"></uni-icons>
                </view>
                <view class="item-content">
                    <view class="item-label">电子邮箱</view>
                    <view class="item-value">{{ websiteConfig.website_email }}</view>
                </view>
            </view>

            <!-- 地址 -->
            <view class="contact-item" v-if="websiteConfig.website_address"
                @click="copyText(websiteConfig.website_address, '地址已复制')">
                <view class="item-icon">
                    <uni-icons type="location" size="24" color="#FF4141"></uni-icons>
                </view>
                <view class="item-content">
                    <view class="item-label">公司地址</view>
                    <view class="item-value">{{ websiteConfig.website_address }}</view>
                </view>
            </view>
        </view>

        <!-- 二维码 -->
        <view class="qrcode-section" v-if="websiteConfig.website_qrcode">
            <view class="qrcode-title">扫码联系我们</view>
            <image class="qrcode-image" :src="formatImageUrl(websiteConfig.website_qrcode, ThumbnailPresets.medium)" mode="aspectFit"></image>
            <view class="qrcode-tip">长按保存图片或扫描二维码</view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { useConfigStore } from '@/stores/modules/config';
import { ref, onMounted } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
// 提现申请配置
const configStore = useConfigStore();
const websiteConfig = ref<Record<string, string>>({});

// 获取网站配置
async function fetchWebsiteConfig() {
    websiteConfig.value = await configStore.fetchConfigsByType('website');
}

// 拨打电话
const callPhone = (phone: string) => {
    if (!phone) return;
    uni.makePhoneCall({
        phoneNumber: phone
    });
};

// 复制文本
const copyText = (text: string, successMsg: string) => {
    if (!text) return;
    uni.setClipboardData({
        data: text,
        success: () => {
            uni.showToast({
                title: successMsg,
                icon: 'none'
            });
        }
    });
};

// 页面加载时获取网站配置
onMounted(() => {
    fetchWebsiteConfig();
});
</script>

<style lang="scss" scoped>
.contact-us {
    min-height: 100vh;
    background-color: #f5f5f5;
    padding: 30rpx;

    .contact-us-title {
        font-size: 40rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 40rpx;
        text-align: center;
    }

    .contact-card {
        background-color: #fff;
        border-radius: 12rpx;
        padding: 20rpx;
        margin-bottom: 30rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .contact-item {
            display: flex;
            align-items: center;
            padding: 30rpx 20rpx;
            border-bottom: 1rpx solid #f5f5f5;

            &:last-child {
                border-bottom: none;
            }

            .item-icon {
                width: 80rpx;
                height: 80rpx;
                border-radius: 50%;
                background-color: rgba(255, 65, 65, 0.1);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 20rpx;
            }

            .item-content {
                flex: 1;

                .item-label {
                    font-size: 28rpx;
                    color: #666;
                    margin-bottom: 6rpx;
                }

                .item-value {
                    font-size: 32rpx;
                    color: #333;
                    font-weight: 500;
                }
            }
        }
    }

    .qrcode-section {
        background-color: #fff;
        border-radius: 12rpx;
        padding: 40rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .qrcode-title {
            font-size: 32rpx;
            font-weight: bold;
            color: #333;
            margin-bottom: 30rpx;
        }

        .qrcode-image {
            width: 300rpx;
            height: 300rpx;
            margin-bottom: 20rpx;
        }

        .qrcode-tip {
            font-size: 24rpx;
            color: #999;
        }
    }
}
</style>
