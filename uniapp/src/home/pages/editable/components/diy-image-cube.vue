<template>
    <view class="diy-image-cube" :style="containerStyle">
        <!-- 布局1: 单张图片 -->
        <view v-if="cubeSetting.layout === 'style1'" class="cube-layout layout-style1" :style="{ height: cubeHeight }">
            <view 
                v-if="imageList.length > 0" 
                class="cube-item" 
                @click="handleImageClick(imageList[0])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局2: 1行2个 -->
        <view v-else-if="cubeSetting.layout === 'style2'" class="cube-layout layout-style2" :style="{ height: cubeHeight }">
            <view 
                v-for="(item, index) in imageList.slice(0, 2)" 
                :key="index" 
                class="cube-item" 
                @click="handleImageClick(item)"
            >
                <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局3: 1行3个 -->
        <view v-else-if="cubeSetting.layout === 'style3'" class="cube-layout layout-style3" :style="{ height: cubeHeight }">
            <view 
                v-for="(item, index) in imageList.slice(0, 3)" 
                :key="index" 
                class="cube-item" 
                @click="handleImageClick(item)"
            >
                <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局4: 1行4个 -->
        <view v-else-if="cubeSetting.layout === 'style4'" class="cube-layout layout-style4" :style="{ height: cubeHeight }">
            <view 
                v-for="(item, index) in imageList.slice(0, 4)" 
                :key="index" 
                class="cube-item" 
                @click="handleImageClick(item)"
            >
                <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局5: 1左2右 -->
        <view v-else-if="cubeSetting.layout === 'style5'" class="cube-layout layout-style5" :style="{ height: cubeHeight }">
            <view 
                v-if="imageList.length > 0" 
                class="cube-item cube-item-left" 
                @click="handleImageClick(imageList[0])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
            <view class="cube-right">
                <view 
                    v-for="(item, index) in imageList.slice(1, 3)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
        </view>

        <!-- 布局6: 1左3右 -->
        <view v-else-if="cubeSetting.layout === 'style6'" class="cube-layout layout-style6" :style="{ height: cubeHeight }">
            <view 
                v-if="imageList.length > 0" 
                class="cube-item cube-item-left" 
                @click="handleImageClick(imageList[0])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
            <view class="cube-right">
                <view 
                    v-for="(item, index) in imageList.slice(1, 4)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
        </view>

        <!-- 布局7: 2左1右 -->
        <view v-else-if="cubeSetting.layout === 'style7'" class="cube-layout layout-style7" :style="{ height: cubeHeight }">
            <view class="cube-left">
                <view 
                    v-for="(item, index) in imageList.slice(0, 2)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
            <view 
                v-if="imageList.length > 2" 
                class="cube-item cube-item-right" 
                @click="handleImageClick(imageList[2])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[2].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局8: 3左1右 -->
        <view v-else-if="cubeSetting.layout === 'style8'" class="cube-layout layout-style8" :style="{ height: cubeHeight }">
            <view class="cube-left">
                <view class="cube-item" 
                      v-if="imageList.length > 0"
                      @click="handleImageClick(imageList[0])">
                    <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
                <view class="cube-item" 
                      v-if="imageList.length > 1"
                      @click="handleImageClick(imageList[1])">
                    <image class="cube-image" :src="formatImageUrl(imageList[1].image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
                <view class="cube-item" 
                      v-if="imageList.length > 2"
                      @click="handleImageClick(imageList[2])">
                    <image class="cube-image" :src="formatImageUrl(imageList[2].image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
            <view 
                v-if="imageList.length > 3" 
                class="cube-item cube-item-right" 
                @click="handleImageClick(imageList[3])">
                <image class="cube-image" :src="formatImageUrl(imageList[3].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局9: 1上2下 -->
        <view v-else-if="cubeSetting.layout === 'style9'" class="cube-layout layout-style9" :style="{ height: cubeHeight }">
            <view 
                v-if="imageList.length > 0" 
                class="cube-item cube-item-top" 
                @click="handleImageClick(imageList[0])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
            <view class="cube-bottom">
                <view 
                    v-for="(item, index) in imageList.slice(1, 3)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
        </view>

        <!-- 布局10: 1上3下 -->
        <view v-else-if="cubeSetting.layout === 'style10'" class="cube-layout layout-style10" :style="{ height: cubeHeight }">
            <view 
                v-if="imageList.length > 0" 
                class="cube-item cube-item-top" 
                @click="handleImageClick(imageList[0])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[0].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
            <view class="cube-bottom">
                <view 
                    v-for="(item, index) in imageList.slice(1, 4)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
        </view>

        <!-- 布局11: 2上1下 -->
        <view v-else-if="cubeSetting.layout === 'style11'" class="cube-layout layout-style11" :style="{ height: cubeHeight }">
            <view class="cube-top">
                <view 
                    v-for="(item, index) in imageList.slice(0, 2)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
            <view 
                v-if="imageList.length > 2" 
                class="cube-item cube-item-bottom" 
                @click="handleImageClick(imageList[2])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[2].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>

        <!-- 布局12: 3上1下 -->
        <view v-else-if="cubeSetting.layout === 'style12'" class="cube-layout layout-style12" :style="{ height: cubeHeight }">
            <view class="cube-top">
                <view 
                    v-for="(item, index) in imageList.slice(0, 3)" 
                    :key="index" 
                    class="cube-item" 
                    @click="handleImageClick(item)"
                >
                    <image class="cube-image" :src="formatImageUrl(item.image, ThumbnailPresets.original)" mode="widthFix" />
                </view>
            </view>
            <view 
                v-if="imageList.length > 3" 
                class="cube-item cube-item-bottom" 
                @click="handleImageClick(imageList[3])"
            >
                <image class="cube-image" :src="formatImageUrl(imageList[3].image, ThumbnailPresets.original)" mode="widthFix" />
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

interface ImageItem {
    image: string;
    link: string;
}

interface Settings {
    imageList: ImageItem[];
    cubeSetting: {
        // style1 单个 style2 1行2个 style3 1行3个 style4 1行4个 style5 1左2右 style6 1左3右 style7 2左1右 style8 3左1右 style9 1上2下 style10 1上3下 style11 2上1下 style12 3上1下
        layout: 'style1' | 'style2' | 'style3' | 'style4' | 'style5' | 'style6' | 'style7' | 'style8' | 'style9' | 'style10' | 'style11' | 'style12';
        // 是否固定高度
        is_auto_height: boolean;
        // 高度
        height: number;
    };
    baseStyles: BaseStyles; // 使用共享接口
}

// 扩展基础配置接口
interface BaseConfig extends BaseDiyConfig {
    settings: Settings;
}

// 接收父组件传递的配置
const props = defineProps<{
    config: BaseConfig;
}>();

// 获取图片列表数据
const imageList = computed(() => {
    return props.config.settings?.imageList || [];
});

// 获取图片魔方设置
const cubeSetting = computed(() => {
    return props.config.settings?.cubeSetting || {};
});

// 获取高度 如果is_auto_height为true，则高度为设置的height，否则为样式固定的高度
const cubeHeight = computed(() => {
    if (cubeSetting.value.is_auto_height) {
        return cubeSetting.value.height ? `${cubeSetting.value.height}rpx` : '';
    }
    return '';
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);



// 处理图片点击
const handleImageClick = (item: ImageItem) => {
    if (item.link) {
        uni.navigateTo({
            url: item.link,
            fail: (err) => {
                console.error('导航失败:', err);
            }
        });
    }
};
</script>

<style lang="scss" scoped>
.diy-image-cube {
    box-sizing: border-box;
    
    .cube-layout {
        display: flex;
        flex-wrap: wrap;
        gap: 0;
        width: 100%;
    }
    
    .cube-item {
        overflow: hidden;
        border-radius: 4px;
        position: relative;
        
        &:active {
            opacity: 0.9;
        }
    }
    
    .cube-image {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }
    
    // 单张图片
    .layout-style1 {
        .cube-item {
            width: 100%;
            
            .cube-image {
                height: auto;
            }
        }
    }
    
    // 1行2个
    .layout-style2 {
        .cube-item {
            width: 50%;
            height: 100%;
        }
    }
    
    // 1行3个
    .layout-style3 {
        .cube-item {
            width: 33.333%;
            height: 100%;
        }
    }
    
    // 1行4个
    .layout-style4 {
        .cube-item {
            width: 25%;
            height: 100%;
        }
    }
    
    // 1左2右
    .layout-style5 {
        flex-wrap: nowrap;
        
        .cube-item-left {
            width: 50%;
            height: 100%;
        }
        
        .cube-right {
            width: 50%;
            display: flex;
            flex-direction: column;
            height: 100%;
            
            .cube-item {
                width: 100%;
                height: 50%;
            }
        }
    }
    
    // 1左3右
    .layout-style6 {
        flex-wrap: nowrap;
        
        .cube-item-left {
            width: 50%;
            height: 100%;
        }
        
        .cube-right {
            width: 50%;
            display: flex;
            flex-direction: column;
            flex-wrap: wrap;
            height: 100%;
            
            .cube-item {
                width: 100%;
                height: 33.333%;
            }
        }
    }
    
    // 2左1右
    .layout-style7 {
        flex-wrap: nowrap;
        
        .cube-left {
            width: 50%;
            display: flex;
            flex-direction: column;
            height: 100%;
            
            .cube-item {
                width: 100%;
                height: 50%;
            }
        }
        
        .cube-item-right {
            width: 50%;
            height: 100%;
        }
    }
    
    // 3左1右
    .layout-style8 {
        flex-wrap: nowrap;
        
        .cube-left {
            width: 50%;
            height: 100%;
            display: flex;
            flex-direction: column;
            
            .cube-item {
                width: 100%;
                height: 33.333%;
            }
        }
        
        .cube-item-right {
            width: 50%;
            height: 100%;
        }
    }
    
    // 1上2下
    .layout-style9 {
        flex-direction: column;
        
        .cube-item-top {
            width: 100%;
            height: 50%;
        }
        
        .cube-bottom {
            display: flex;
            width: 100%;
            height: 50%;
            
            .cube-item {
                width: 50%;
                height: 100%;
            }
        }
    }
    
    // 1上3下
    .layout-style10 {
        flex-direction: column;
        
        .cube-item-top {
            width: 100%;
            height: 40%;
        }
        
        .cube-bottom {
            display: flex;
            width: 100%;
            height: 60%;
            
            .cube-item {
                width: 33.333%;
                height: 100%;
            }
        }
    }
    
    // 2上1下
    .layout-style11 {
        flex-direction: column;
        
        .cube-top {
            display: flex;
            width: 100%;
            height: 60%;
            
            .cube-item {
                width: 50%;
                height: 100%;
            }
        }
        
        .cube-item-bottom {
            width: 100%;
            height: 40%;
        }
    }
    
    // 3上1下
    .layout-style12 {
        flex-direction: column;
        
        .cube-top {
            display: flex;
            width: 100%;
            height: 60%;
            
            .cube-item {
                width: 33.333%;
                height: 100%;
            }
        }
        
        .cube-item-bottom {
            width: 100%;
            height: 40%;
        }
    }
}
</style>