<template>
  <view class="diy-goods-list" :style="containerStyle">
    <view class="goods-header" v-if="props.config.settings.goodsSetting.is_show_header_title">
      <text class="goods-title">{{ props.config.settings.goodsSetting.header_title }}</text>
      <text class="goods-more" @click="goToPage({ url: props.config.settings.goodsSetting.header_more_link })">更多 ></text>
    </view>
    
    <view :class="['goods-container', `layout-${layoutType}`]">
      <view 
        v-for="(item, index) in goodsList" 
        :key="index" 
        class="goods-item"
        @click="goGoodsDetailPage(item)"
      >
        <image class="goods-image" :src="formatImageUrl(item.cover_image, ThumbnailPresets.medium, 'goods')" mode="aspectFill" />
        <view class="goods-info">
          <text class="goods-name" :style="fontStyle">{{ item.goods_name }}</text>
          <view class="goods-price-row">
            <view class="price-left">
              <text class="goods-price">¥{{ item.goodsSku.sku_price }}</text>
              <text class="goods-original-price" v-if="item.goodsSku.market_price">¥{{ item.goodsSku.market_price }}</text>
            </view>
            <text class="goods-sales">已售 {{ item.sales || 0 }}</text>
          </view>
        </view>
      </view>
    </view>


    <DsEmpty v-if="goodsList.length === 0" text="暂无商品数据" />


  </view>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';

import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

import { goToPage } from '@/utils/util'


import { getTblGoodsList } from '@/home/api/tbl-goods/tblGoods';
import { goGoodsDetailPage } from '@/utils/platform';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

import DsEmpty from '@/components/ds-empty/index.vue';



interface Settings {

  goodsSetting: {
        // 平台 all:全部 platform:平台
        platform: string,
        // 排序  default 默认  price 价格  sales 销量  new 新品  hot 热销  recommend 推荐
        sort: string,
        // 显示数量
        nums: number,
        // 商品分类ID
        category_id?: number | string,

        // 是否显示头部标题
        is_show_header_title: boolean,
        // 头部标题
        header_title: string,
        // 头部更多链接
        header_more_link: string,

  };
  styleSetting: {
    // 布局  grid 网格  list 列表  row2 一行两个  row3-scroll 一行三个(可滑动)
    layout: 'grid' | 'list' | 'row2' | 'row3-scroll';
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








// 商品列表数据
const goodsList = ref([]);
const loading = ref(false);

// 计算布局类型
const layoutType = computed(() => {
  return props.config.settings?.styleSetting?.layout || 'grid';
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 排序映射：将前端的 sort 值映射为后端的 sort_field 和 sort_type
const mapSortToParams = (sort: string) => {
  const sortMap: Record<string, { sort_field: string; sort_type: string }> = {
    'default': { sort_field: 'sales_num', sort_type: 'desc' },
    'price': { sort_field: 'goods_minprice', sort_type: 'asc' },
    'sales': { sort_field: 'sales_num', sort_type: 'desc' },
    'new': { sort_field: 'create_at', sort_type: 'desc' },
    'hot': { sort_field: 'click_num', sort_type: 'desc' },
    'recommend': { sort_field: 'sales_num', sort_type: 'desc' }, // 推荐商品会优先显示（后端逻辑）
  };
  
  return sortMap[sort] || sortMap['default'];
};

// 加载商品数据
const loadGoodsList = async () => {
  loading.value = true;
  try {
    const { goodsSetting } = props.config.settings || {};
    const sortValue = goodsSetting?.sort || 'new';
    const sortParams = mapSortToParams(sortValue);
    
    const params: any = {
      limit: goodsSetting?.nums || 10,
      sort_field: sortParams.sort_field,
      sort_type: sortParams.sort_type,
    };
    
    // 平台参数
    if (goodsSetting?.platform && goodsSetting.platform !== 'all' && goodsSetting.platform !== '') {
      params.platform = goodsSetting.platform;
    }

    // 分类参数
    if (goodsSetting?.category_id) {
      params.category_id = goodsSetting.category_id;
    }
    
    const response = await getTblGoodsList(params);
    if (response.code === 10000) {
      goodsList.value = response.data || [];
    }

  } catch (error) {
    console.error('加载商品列表失败:', error);
  } finally {
    loading.value = false;
  }
};





// 组件挂载时加载数据
onMounted(() => {
  loadGoodsList();
});
</script>

<style scoped lang="scss">
.diy-goods-list {
  box-sizing: border-box;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.goods-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #f5f5f5;
}

.goods-title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
}

.goods-more {
  font-size: 12px;
  color: #999;
}

.goods-container {
  padding: 10px;
}

/* 网格布局 */
.layout-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}

/* 列表布局 */
.layout-list .goods-item {
  display: flex;
  margin-bottom: 12px;
  border-bottom: 1px solid #f5f5f5;
  padding-bottom: 12px;
}

.layout-list .goods-image {
  width: 100px;
  height: 100px;
  margin-right: 12px;
}

.layout-list .goods-info {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
}

/* 网格布局的商品项 */
.layout-grid .goods-item {
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.layout-grid .goods-image {
  width: 100%;
  aspect-ratio: 1/1;
  height: auto;
}

.layout-grid .goods-info {
  padding: 12px;
}

/* 通用样式 */
.goods-name {
  font-size: 14px;
  color: #333;
  margin-bottom: 8px;
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  line-height: 1.4;
}

.goods-price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-top: 8px;
}

.price-left {
  display: flex;
  align-items: center;
  gap: 8px;
}

.goods-price {
  font-size: 16px;
  color: #ff6b6b;
  font-weight: bold;
}

.goods-original-price {
  font-size: 12px;
  color: #999;
  text-decoration: line-through;
}

.goods-sales {
  font-size: 12px;
  color: #999;
  background-color: #f5f5f5;
  padding: 4px 8px;
  border-radius: 12px;
  white-space: nowrap;
}


</style>
  