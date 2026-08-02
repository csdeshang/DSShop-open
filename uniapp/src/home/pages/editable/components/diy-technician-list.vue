<template>
    <view class="diy-technician-list" :style="containerStyle">
        <view class="technician-header" v-if="technicianSetting.is_show_header_title">
            <text class="technician-title">{{ technicianSetting.header_title }}</text>
            <text class="technician-more" @click="goToPage({ url: technicianSetting.header_more_link })">更多 ></text>
        </view>

        <!-- 风格1：一行一个 -->
        <view v-if="technicianSetting.style === 'type1'" class="technician-list style-type1">
            <view 
                v-for="item in technicianList" 
                :key="item.id" 
                class="technician-item" 
                @click="goToPage({url:'/home/pages/technician/detail/index',params:{id:item.id}})"
            >
                <image class="technician-image" :src="formatImageUrl(item.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
                <view class="technician-content">
                    <text class="technician-item-title" :style="fontStyle">{{ item.name }}</text>
                    <view class="technician-item-meta">
                        <text class="technician-desc">{{ item.description || '暂无描述' }}</text>
                        <text class="technician-distance" v-if="item.distance">{{ formatDistance(item.distance) }}</text>
                    </view>
                    <view class="technician-stats">
                        <text class="technician-stat-item">访问: {{ item.visit_count || 0 }}次</text>
                        <text class="technician-stat-item">服务: {{ item.service_count || 0 }}单</text>
                    </view>
                </view>
            </view>
            <DsEmpty v-if="technicianList.length === 0" text="暂无师傅数据" />
        </view>

        <!-- 风格2：一行两个 -->
        <view v-if="technicianSetting.style === 'type2'" class="technician-grid style-type2">
            <view 
                v-for="item in technicianList" 
                :key="item.id" 
                class="technician-item" 
                @click="goToPage({url:'/home/pages/technician/detail/index',params:{id:item.id}})"
            >
                <view class="image-container">
                    <image class="technician-image" :src="formatImageUrl(item.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill" />
                </view>
                <text class="technician-item-title" :style="fontStyle">{{ item.name }}</text>
                <view class="technician-stats">
                    <text class="technician-stat-item">访问: {{ item.visit_count || 0 }}次</text>
                    <text class="technician-stat-item">服务: {{ item.service_count || 0 }}单 </text>
                </view>
                <view class="technician-item-meta">
                    <text class="technician-distance" v-if="item.distance">{{ formatDistance(item.distance) }}</text>
                </view>
            </view>
            <DsEmpty v-if="technicianList.length === 0" text="暂无师傅数据" />
        </view>
    </view>
</template>

<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue';
import { goToPage } from '@/utils/util';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';
import { getTechnicianList } from '@/home/api/technician/technician';
import DsEmpty from '@/components/ds-empty/index.vue';
import { useConfigStore } from '@/stores/modules/config';
// 引入位置服务
import { useLbsStore } from '@/stores/modules/lbs';
const lbsStore = useLbsStore();
const configStore = useConfigStore();



interface Settings {
    technicianSetting: {
        // style1 一行一个
        // style2 一行两个
        style: 'type1' | 'type2';
        // 师傅显示数量
        store_nums: string;

        // 是否显示销量
        is_show_sales: boolean;

        // 是否显示头部标题
        is_show_header_title: boolean;
        // 头部标题
        header_title: string;
        // 头部更多链接
        header_more_link: string;
    };
    baseStyles: BaseStyles;
}

// 扩展基础配置接口
interface BaseConfig extends BaseDiyConfig {
    settings: Settings;
}

// 接收父组件传递的配置
const props = defineProps<{
    config: BaseConfig;
}>();


const technicianList = ref([]);
const loading = ref(false);

// 获取师傅设置
const technicianSetting = computed(() => {
    return props.config.settings?.technicianSetting || {};
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 格式化距离
const formatDistance = (distance: number) => {
    if (!distance) return '';
    if (distance < 1000) {
        return `${distance.toFixed(0)}m`;
    } else {
        return `${(distance / 1000).toFixed(1)}km`;
    }
};

// 加载师傅列表数据
const loadTechnicianList = async () => {
    loading.value = true;
    try {

        const params = {
            limit: parseInt(technicianSetting.value.store_nums || '10', 10),
            latitude: 0,
            longitude: 0,
        };
        
        // 是否开启了定位
        const lbs_is_enabled = await configStore.fetchConfig('lbs:lbs_is_enabled');
        if (lbs_is_enabled == '1') {
            // 获取位置信息
            await lbsStore.getLocation();
            params.latitude = lbsStore.latitude || 0;
            params.longitude = lbsStore.longitude || 0;
        }
        

        const response = await getTechnicianList(params);
        technicianList.value = response.data || [];
    } catch (error) {
        console.error('加载师傅列表失败:', error);
    } finally {
        loading.value = false;
    }
};



// 组件挂载时加载数据
onMounted(() => {
    loadTechnicianList();
});
</script>

<style scoped lang="scss">
.diy-technician-list {
    box-sizing: border-box;
    background-color: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.technician-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 15px;
    border-bottom: 1px solid #f5f5f5;
}

.technician-title {
    font-size: 16px;
    font-weight: bold;
    color: #333;
}

.technician-more {
    font-size: 12px;
    color: #999;
}

.technician-list,
.technician-grid {
    padding: 10px 15px;
}

// 访问次数和服务次数通用样式
.technician-stats {
    display: flex;
    align-items: center;
    margin-top: 5px;
    
    .technician-stat-item {
        font-size: 12px;
        color: #666;
        margin-right: 15px;
        
        &:last-child {
            margin-right: 0;
        }
    }
}

// 风格1：一行一个
.style-type1 {
    .technician-item {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f5f5f5;

        &:last-child {
            border-bottom: none;
        }
    }

    .technician-image {
        width: 60px;
        height: 60px;
        border-radius: 4px;
        margin-right: 12px;
        flex-shrink: 0;
    }

    .technician-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .technician-item-title {
        font-size: 15px;
        font-weight: 500;
        margin-bottom: 6px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .technician-item-meta {
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #999;
    }

    .technician-desc {
        flex: 1;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .technician-distance {
        margin-left: 10px;
        color: #666;
    }
}

// 风格2：一行两个
.style-type2 {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;

    .technician-item {
        display: flex;
        flex-direction: column;
        background-color: #fff;
        border-radius: 6px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }

    .image-container {
        position: relative;
        width: 100%;
        height: 0;
        padding-bottom: 100%; /* 实现正方形效果 */
    }

    .technician-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .technician-item-title {
        font-size: 14px;
        font-weight: 500;
        margin: 8px 10px 4px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .technician-stats {
        margin: 0 10px;
    }

    .technician-item-meta {
        display: flex;
        justify-content: space-between;
        margin: 4px 10px 10px;
        font-size: 12px;
        color: #999;
    }

    .technician-distance {
        color: #666;
    }
}


</style>