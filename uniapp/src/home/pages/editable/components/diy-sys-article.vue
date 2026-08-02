<template>
  <view class="diy-sys-article" :style="containerStyle">
    <view class="article-header" v-if="articleSetting.is_show_header_title">
      <text class="article-title">{{ articleSetting.header_title }}</text>
      <text class="article-more" @click="goToPage({ url: '/shared/pages/system/article/list' })">更多 ></text>
    </view>

    <!-- 风格1：列表不带图 -->
    <view v-if="articleSetting.style === 'type1'" class="article-list style-type1">
      <view v-for="item in articleList" :key="item.id" class="article-item" @click="goToPage({ url: `/shared/pages/system/article/detail?id=${item.id}` })">
        <text class="article-item-title" :style="fontStyle">{{ item.title }}</text>
        <view class="article-item-meta">
          <text v-if="articleSetting.is_show_date" class="article-date">{{ formatDate(item.publish_time) }}</text>
          <text v-if="articleSetting.is_show_views" class="article-views">{{ item.virtual_views + item.actual_views
            }}阅读</text>
        </view>
      </view>
      <DsEmpty v-if="articleList.length === 0" text="暂无文章数据" />
    </view>

    <!-- 风格2：列表带图 -->
    <view v-if="articleSetting.style === 'type2'" class="article-list style-type2">
      <view v-for="item in articleList" :key="item.id" class="article-item" @click="goToPage({ url: `/shared/pages/system/article/detail?id=${item.id}` })">
        <image class="article-image" :src="formatImageUrl(item.image, ThumbnailPresets.small)" mode="aspectFill" />
        <view class="article-content">
          <text class="article-item-title" :style="fontStyle">{{ item.title }}</text>
          <view class="article-item-meta">
            <text v-if="articleSetting.is_show_date" class="article-date">{{ formatDate(item.publish_time) }}</text>
            <text v-if="articleSetting.is_show_views" class="article-views">{{ item.virtual_views + item.actual_views
              }}阅读</text>
          </view>
        </view>
      </view>
      <DsEmpty v-if="articleList.length === 0" text="暂无文章数据" />

    </view>

    <!-- 风格3：一行2个 -->
    <view v-if="articleSetting.style === 'type3'" class="article-grid style-type3">
      <view v-for="item in articleList" :key="item.id" class="article-item" @click="goToPage({ url: `/shared/pages/system/article/detail?id=${item.id}` })">
        <image class="article-image" :src="formatImageUrl(item.image, ThumbnailPresets.small)" mode="aspectFill" />
        <text class="article-item-title" :style="fontStyle">{{ item.title }}</text>
        <view class="article-item-meta">
          <text v-if="articleSetting.is_show_date" class="article-date">{{ formatDate(item.publish_time) }}</text>
          <text v-if="articleSetting.is_show_views" class="article-views">{{ item.virtual_views + item.actual_views
            }}阅读</text>
        </view>
      </view>
      <DsEmpty v-if="articleList.length === 0" text="暂无文章数据" />

    </view>

    <!-- 风格4：图宽度100% -->
    <view v-if="articleSetting.style === 'type4'" class="article-list style-type4">
      <view v-for="item in articleList" :key="item.id" class="article-item" @click="goToPage({ url: `/shared/pages/system/article/detail?id=${item.id}` })">
        <image class="article-image" :src="formatImageUrl(item.image, ThumbnailPresets.small)" mode="aspectFill" />
        <view class="article-content">
          <text class="article-item-title" :style="fontStyle">{{ item.title }}</text>
          <view class="article-item-meta">
            <text v-if="articleSetting.is_show_date" class="article-date">{{ formatDate(item.publish_time) }}</text>
            <text v-if="articleSetting.is_show_views" class="article-views">{{ item.virtual_views + item.actual_views
              }}阅读</text>
          </view>
        </view>
      </view>
      <DsEmpty v-if="articleList.length === 0" text="暂无文章数据" />

    </view>
  </view>
</template>

<script setup lang="ts">
import { computed, ref, onMounted } from 'vue';
import { goToPage } from '@/utils/util';
import { formatImageUrl, ThumbnailPresets } from '@/utils/image';
import type { BaseStyles, BaseDiyConfig } from './composables/common-types';
import { useContainerStyle, useFontStyle } from './composables/use-styles';

import { getSysArticleList } from '@/api/system/sysArticle';

import DsEmpty from '@/components/ds-empty/index.vue';

interface Settings {
  articleSetting: {
    // 是否显示头部标题
    is_show_header_title: boolean;
    // 头部标题
    header_title: string;
    // 头部更多链接
    header_more_link: string;
    // 风格类型 type1:列表 type2:列表(带图片) type3:一行2个 type4:大图
    style: 'type1' | 'type2' | 'type3' | 'type4';
    // 文章分类id
    article_cid: string;
    // 文章数量
    article_nums: string;
        // 是否显示阅读量
    is_show_views: boolean;
    // 是否显示发布时间
    is_show_date: boolean;
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

// 获取文章列表数据
const articleList = ref([]);
const loading = ref(false);

// 获取文章设置
const articleSetting = computed(() => {
  return props.config.settings?.articleSetting || {};
});

// 获取基础样式
const baseStyles = computed(() => props.config.settings?.baseStyles);

// 使用公共函数计算样式
const containerStyle = useContainerStyle(baseStyles);
const fontStyle = useFontStyle(baseStyles);

// 格式化日期
const formatDate = (timestamp: number) => {
  if (!timestamp) return '';
  const date = new Date(timestamp * 1000);
  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
};

// 加载文章数据
const loadArticleList = async () => {
  loading.value = true;
  try {
    const params = {
      cid: articleSetting.value.article_cid || 0,
      limit: parseInt(articleSetting.value.article_nums || '6', 10),
      is_show: 1
    };

    const response = await getSysArticleList(params);
    articleList.value = response.data || [];
  } catch (error) {
    console.error('加载文章列表失败:', error);
  } finally {
    loading.value = false;
  }
};



// 组件挂载时加载数据
onMounted(() => {
  loadArticleList();
});
</script>

<style scoped lang="scss">
.diy-sys-article {

  box-sizing: border-box;
  background-color: #fff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.article-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 15px;
  border-bottom: 1px solid #f5f5f5;
}

.article-title {
  font-size: 16px;
  font-weight: bold;
  color: #333;
}

.article-more {
  font-size: 12px;
  color: #999;
}

.article-list,
.article-grid {
  padding: 10px 15px;
}

// 风格1：列表不带图
.style-type1 {
  .article-item {
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;

    &:last-child {
      border-bottom: none;
    }
  }

  .article-item-title {
    font-size: 14px;
    margin-bottom: 5px;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .article-item-meta {
    display: flex;
    font-size: 12px;
    color: #999;
  }

  .article-date {
    margin-right: 10px;
  }
}

// 风格2：列表带图
.style-type2 {
  .article-item {
    display: flex;
    padding: 10px 0;
    border-bottom: 1px solid #f5f5f5;

    &:last-child {
      border-bottom: none;
    }
  }

  .article-image {
    width: 80px;
    height: 60px;
    border-radius: 4px;
    margin-right: 10px;
    flex-shrink: 0;
  }

  .article-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
  }

  .article-item-title {
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .article-item-meta {
    display: flex;
    font-size: 12px;
    color: #999;
  }

  .article-date {
    margin-right: 10px;
  }
}

// 风格3：一行2个
.style-type3 {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;

  .article-item {
    display: flex;
    flex-direction: column;
  }

  .article-image {
    width: 100%;
    height: 100px;
    border-radius: 4px;
    margin-bottom: 8px;
  }

  .article-item-title {
    font-size: 14px;
    line-height: 1.4;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
  }

  .article-item-meta {
    display: flex;
    font-size: 12px;
    color: #999;
  }

  .article-date {
    margin-right: 10px;
  }
}

// 风格4：图宽度100%
.style-type4 {
  .article-item {
    margin-bottom: 15px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .article-image {
    width: 100%;
    height: 150px;
    border-radius: 4px;
    margin-bottom: 8px;
  }

  .article-item-title {
    font-size: 14px;
    font-weight: bold;
    line-height: 1.4;
    margin-bottom: 5px;
  }

  .article-item-meta {
    display: flex;
    font-size: 12px;
    color: #999;
  }

  .article-date {
    margin-right: 10px;
  }
}


</style>
