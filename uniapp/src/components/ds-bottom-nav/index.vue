<template>
  <view>
    <!-- 底部占位，防止内容被导航栏遮挡 -->
    <view class="footer-placeholder"></view>

    <!-- 实际导航栏 -->
    <view class="footer">
      <view class="tab-bar">
        <view class="tab-item" v-for="(item, index) in navConfig.items" :key="index" @click="switchTab(item)"
          :style="getTabStyle(item)">
          <image class="icon" :src="isActive(item) ? formatImageUrl(item.activeIcon, ThumbnailPresets.small) : formatImageUrl(item.inactiveIcon, ThumbnailPresets.small)"
            :style="getIconStyle(item)" />
          <text class="text" :style="getTextStyle(item)">
            {{ item.title }}
          </text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'


const props = defineProps({
  configKey: {
    type: String,
    default: 'website:h5_system_bottom_nav'
  }
})




type NavItem = {
  name: string
  link: string
  inactiveIcon: string
  activeIcon: string
  title: string
}

type NavTheme = {
  displayMode: 'all' | 'text' | 'image'
  backgroundColor: string
  color: string
  fontSize: string
}

type NavConfig = {
  items: NavItem[]
  theme: NavTheme
}

const navConfig = ref<NavConfig>({
  items: [],
  theme: {
    displayMode: 'text',
    backgroundColor: '',
    color: '',
    fontSize: '',
  }
});



function isActive(item: NavItem): boolean {
  const pages = getCurrentPages();
  const currentPage = pages[pages.length - 1]; // 获取当前页面
  const currentRoute = currentPage.route || '';
  return currentRoute === item.link.replace(/^\//, '');
}


function switchTab(tab: NavItem) {
  if (!tab.link) return;

  // 检查是否是首页
  const isHomePage = tab.link.startsWith('/home/pages/index');

  if (isHomePage) {
    // 首页特殊处理：栈深度>=3时使用reLaunch
    if (getCurrentPages().length >= 3) {
      goToPage({
        url: tab.link,
        type: 'reLaunch'
      });
      return;
    }
  }

  // 统一使用 redirectTo（包括首页栈深度<3的情况和非首页） 关闭当前页面，跳转到应用内的某个页面。
  goToPage({
    url: tab.link,
    type: 'redirectTo'
  });
}

function getTabStyle(item: NavItem): Record<string, string> {
  const theme = navConfig.value.theme;
  if (isActive(item)) {
    return {
      color: theme.color || '#333', // 默认选中文字颜色
      backgroundColor: theme.backgroundColor || '#fff', // 默认选中背景颜色
      fontSize: theme.fontSize || '28rpx', // 默认选中字体大小
    };
  }
  return {};
}

function getIconStyle(item: NavItem): Record<string, string> {
  if (isActive(item)) {
    return {
      width: '46rpx', // 放大图标
      height: '46rpx',
    };
  }
  return {
    width: '46rpx',
    height: '46rpx',
  };
}

function getTextStyle(item: NavItem): Record<string, string> {
  const theme = navConfig.value.theme;
  if (isActive(item)) {
    return {
      fontWeight: 'normal',
      color: theme.color || 'red', // 默认选中文字颜色
    };
  }
  return {
    color: '#999',
  };
}

import { useConfigStore } from '@/stores/modules/config';
const configStore = useConfigStore();

// 获取底部导航数据
async function fetchBottomNav() {
  const bottomNavData = await configStore.fetchConfig(props.configKey);
  if (bottomNavData) {
    const parsedConfig = JSON.parse(bottomNavData);
    navConfig.value = {
      ...navConfig.value, // 保留默认值
      ...parsedConfig,
    };
  }
}


// 组件挂载时加载数据
onMounted(() => {
  fetchBottomNav();
})


</script>

<style lang="scss" scoped>
// 占位元素样式
.footer-placeholder {
  padding-bottom: env(safe-area-inset-bottom);
  height: 120rpx; // 比实际导航栏高一点，确保有足够空间
  width: 100%;
}

.footer {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  height: 100rpx;
  background: #fff;
  box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
  z-index: 999; // 确保导航栏在顶层
  padding-bottom: env(safe-area-inset-bottom);

  .tab-bar {
    display: flex;
    height: 100%;
    width: 100%;

    .tab-item {
      flex: 1;
      height: 100%;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;

      .icon {
        width: 48rpx;
        height: 48rpx;
      }

      .text {
        font-size: 24rpx;
        color: #999;
        margin-top: 4rpx;
      }
    }
  }
}
</style>