<template>
    <view class="publish-container">


        <!-- 主内容区域 -->
        <view class="content">
            <!-- 媒体预览区域 -->
            <view class="media-section">
                <!-- 内容类型切换 -->
                <view class="type-tabs">
                    <text v-for="item in contentTypeRange" :key="item.value"
                        :class="['type-tab', { active: formData.type === item.value }]" @click="selectType(item.value)">
                        {{ item.text }}
                    </text>
                </view>

                <!-- 封面设置 -->
                <view class="cover-section">
                    <view class="section-title">
                        <text class="title-text">封面</text>
                    </view>
                    <view class="cover-upload">
                        <ds-attachment-picker v-model="formData.cover_image" type="image" :limit="1" width="160rpx"
                            height="160rpx" />
                    </view>
                </view>

                <!-- 媒体上传区域 -->
                <view class="media-upload">
                    <!-- 视频上传 -->
                    <view v-if="formData.type === 1" class="video-area">
                        <ds-attachment-picker v-model="formData.video_url" type="video" :limit="1" width="200rpx"
                            height="200rpx" />
                    </view>

                    <!-- 图片上传 -->
                    <view v-else class="image-area">
                        <ds-attachment-picker v-model="formData.images_url" type="image" :limit="9" width="200rpx"
                            height="200rpx" />
                    </view>
                </view>
            </view>

            <!-- 标题输入 -->
            <view class="title-section">
                <textarea v-model="formData.title" placeholder="输入标题会收获更多赞喔~" class="title-input" maxlength="50"
                    auto-height />
                <text class="char-count">{{ formData.title.length }}/50</text>
            </view>

            <!-- 内容描述 -->
            <view class="desc-section">
                <textarea v-model="formData.description" placeholder="展开说说，你写的文字我们都很喜欢 :)" class="desc-input"
                    maxlength="500" auto-height />
                <text class="char-count">{{ formData.description.length }}/500</text>
            </view>

            <!-- 分类选择 -->
            <select-category v-model="formData.cid" @change="onCategoryChange" />

            <!-- 商品选择 -->
            <ds-select-goods v-model:goods-ids="formData.goods_ids" :limit="5" />
        </view>

        <!-- 底部发布按钮 -->
        <view class="bottom-bar">
            <button class="publish-btn" @click="handlePublish">
                发布
            </button>
        </view>
    </view>
</template>

<script setup lang="ts">
import { reactive, ref, computed } from 'vue';
import { onShow } from '@dcloudio/uni-app';
import DsAttachmentPicker from '@/components/ds-attachment/picker.vue'
import SelectCategory from '@/video/components/blogger/select-category.vue'
import DsSelectGoods from '@/components/ds-select/goods.vue'

import { publishVideoShort } from '@/video/api/blogger/videoShort'

import useUserStore from '@/stores/user';

const userStore = useUserStore();

// 表单数据
const initialFormData = {
    type: 1, // 1:video 2:image
    title: '',
    description: '',
    cid: '',
    cover_image: '',
    video_url: '',
    images_url: [] as string[],
    goods_ids: [] as string[], // 关联的商品ID数组
};

const formData = reactive({ ...initialFormData });

// 内容类型选项
const contentTypeRange = ref([
    { "value": 1, "text": "发布视频" },
    { "value": 2, "text": "发布图文" }
]);

// 是否可以发布
const canPublish = computed(() => {
    const hasTitle = formData.title.trim();
    const hasDescription = formData.description.trim();
    const hasCategory = formData.cid;
    const hasCover = formData.cover_image;
    const hasMedia = formData.type === 1 ? formData.video_url : formData.images_url.length > 0;

    return hasTitle && hasDescription && hasCategory && hasCover && hasMedia;
});

// 选择内容类型
const selectType = (type: number) => {
    formData.type = type;
    if (type === 1) { // video
        formData.images_url = [];
    } else { // image
        formData.video_url = '';
    }
};

// 分类选择回调
const onCategoryChange = (item: any) => {
    console.log('选择的分类:', item);
};

// 发布内容
const handlePublish = async () => {
    // 具体的验证和提示
    if (!formData.title.trim()) {
        uni.showToast({
            title: '请输入标题',
            icon: 'none'
        });
        return;
    }

    if (!formData.description.trim()) {
        uni.showToast({
            title: '请输入内容描述',
            icon: 'none'
        });
        return;
    }

    if (!formData.cid) {
        uni.showToast({
            title: '请选择分类',
            icon: 'none'
        });
        return;
    }

    if (!formData.cover_image) {
        uni.showToast({
            title: '请设置封面',
            icon: 'none'
        });
        return;
    }

    if (formData.type === 1 && !formData.video_url) {
        uni.showToast({
            title: '请上传视频',
            icon: 'none'
        });
        return;
    }

    if (formData.type === 2 && formData.images_url.length === 0) {
        uni.showToast({
            title: '请上传图片',
            icon: 'none'
        });
        return;
    }

    uni.showLoading({
        title: '正在发布...',
    });

    try {
        const publishData = {
            ...formData,
            blogger_id: userStore.userInfo?.id || '',
            audit_status: '0',
            create_at: new Date().toISOString(),
            publish_at: new Date().toISOString(),
            is_recommend: '0',
            is_top: '0', 
            is_hot: '0',
            view_count: '0',
            like_count: '0',
            comment_count: '0',
            share_count: '0',
            collect_count: '0',
        };

        // 调用发布API
        const response = await publishVideoShort(publishData);
        


        uni.hideLoading();

        if (response.code === 10000) {
            uni.showToast({
                title: '发布成功',
                icon: 'success'
            });
            
            // 重置表单
            Object.assign(formData, initialFormData);
            
            // setTimeout(() => {
            //     uni.navigateBack();
            // }, 1500);
        } else {
            uni.showToast({
                title: response.message || '发布失败',
                icon: 'none'
            });
        }
    } catch (error) {
        uni.hideLoading();
        uni.showToast({
            title: '发布失败，请重试',
            icon: 'none'
        });
    }
};

onShow(() => {
    // 页面显示时的逻辑
});
</script>

<style lang="scss" scoped>
.publish-container {
    height: 100vh;
    background: #fff;
    display: flex;
    flex-direction: column;
}


.content {
    flex: 1;
    padding: 0 32rpx;
    overflow-y: auto;
}

.media-section {
    padding: 40rpx 0;
    border-bottom: 1rpx solid #f8f8f8;
}

.type-tabs {
    display: flex;
    margin-bottom: 30rpx;

    .type-tab {
        padding: 16rpx 32rpx;
        margin-right: 20rpx;
        font-size: 28rpx;
        color: #999;
        background: #f8f8f8;
        border-radius: 20rpx;

        &.active {
            color: #ff2442;
            background: rgba(255, 36, 66, 0.1);
        }
    }
}

.cover-section {
    padding: 30rpx 0;
    border-bottom: 1rpx solid #f8f8f8;

    .section-title {
        display: flex;
        align-items: center;
        margin-bottom: 20rpx;

        .title-text {
            font-size: 30rpx;
            color: #666;

        }
    }


}

.media-upload {
    padding: 30rpx 0;

    .video-area,
    .image-area {
        min-height: 200rpx;
    }
}

.title-section,
.desc-section {
    padding: 30rpx 0;
    border-bottom: 1rpx solid #f8f8f8;

    .title-input,
    .desc-input {
        width: 100%;
        font-size: 32rpx;
        color: #333;
        line-height: 1.5;
        border: none;
        background: none;
    }

    .title-input {
        font-weight: 500;
    }

    .char-count {
        text-align: right;
        font-size: 24rpx;
        color: #ccc;
        margin-top: 10rpx;
    }
}

.bottom-bar {
    padding: 24rpx 32rpx;
    background: #fff;
    border-top: 1rpx solid #f8f8f8;
}

.publish-btn {
    width: 100%;
    height: 88rpx;
    background: linear-gradient(135deg, #ff2442 0%, #ff4757 100%);
    border-radius: 44rpx;
    border: none;
    color: #fff;
    font-size: 32rpx;
    font-weight: 500;
    box-shadow: 0 8rpx 24rpx rgba(255, 36, 66, 0.3);
    transition: all 0.3s ease;

    &:active {
        transform: translateY(2rpx);
        box-shadow: 0 4rpx 12rpx rgba(255, 36, 66, 0.4);
    }

    &::after {
        border: none;
    }
}
</style>