<template>

  <view>
    <!-- 搜索框 -->
    <view class="search-box">
      <uni-search-bar v-model="goods_name" placeholder="搜索商品" radius="100" cancelButton="none"
        @confirm="handleSearch" />
    </view>

    <view class="category-container">
      <!-- 分类内容区 -->
      <view class="category-content">
        <!-- 左侧主分类 -->
        <scroll-view class="main-category" scroll-y>
          <view v-for="item in mainCategories" :key="item.id" class="main-category-item"
            :class="{ active: currentMainId === item.id }" @tap="handleMainCategoryClick(item)">
            <text>{{ item.name }}</text>
          </view>
        </scroll-view>

        <!-- 右侧子分类 -->
        <scroll-view class="sub-category" scroll-y>
          <!-- 分类标题 -->
          <view class="sub-category-title">{{ currentMainCategory?.name || '' }}</view>

          <!-- 子分类列表 -->
          <view class="sub-category-list">
            <view v-for="category in currentSubCategories" :key="category.id" class="category-section">
              <!-- 分类标题 -->
              <view class="section-title" v-if="category.name">
                {{ category.name }}
              </view>

              <!-- 分类项目网格 -->
              <view class="category-grid">
                <view v-for="item in category.children" :key="item.id" class="grid-item" @tap="handleItemClick(item)">
                  <image :src="formatImageUrl(item.image, ThumbnailPresets.small)" mode="aspectFill" />
                  <text>{{ item.name }}</text>
                </view>
              </view>
            </view>
          </view>
        </scroll-view>
      </view>
    </view>

  </view>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getTblGoodsCategoryTree } from '@/home/api/tbl-goods/tblGoodsCategoty'

const props = defineProps({
  platform: {
    type: String,
    default: ''
  }
})



// 分类数据
const mainCategories = ref<any[]>([])
const currentMainId = ref(0)

// 当前选中的主分类
const currentMainCategory = computed(() => {
  return mainCategories.value.find(item => item.id === currentMainId.value)
})

// 当前显示的子分类
const currentSubCategories = computed(() => {
  const main = currentMainCategory.value
  return main ? main.children || [] : []
})

// 获取分类数据
const getCategoryData = async () => {
  uni.showLoading({
    title: '加载中'
  });


  const res = await getTblGoodsCategoryTree({ platform: props.platform })
  if (res.code === 10000 && res.data) {
    mainCategories.value = res.data
    // 默认选中第一个分类
    if (res.data.length > 0) {
      currentMainId.value = res.data[0].id
    }
  }

  uni.hideLoading();

}

// 处理主分类点击
const handleMainCategoryClick = (category: any) => {
  currentMainId.value = category.id
}

// 处理子分类项目点击
const handleItemClick = (item: any) => {
  uni.navigateTo({
    url: `/home/platform/${props.platform}/pages/search/goodslist/index?category_id=${item.id}`
  })
}

// 处理搜索
const goods_name = ref('')
const handleSearch = () => {
  if (!goods_name.value.trim()) return
  goToPage({ url: '/home/platform/mall/pages/goods/list/index', params: { goods_name: goods_name.value } })
}

// 页面加载时获取数据
onMounted(() => {
  getCategoryData()
})
</script>

<style lang="scss" scoped>
.search-box {
  padding: 20rpx 30rpx;
  background: #fff;
  position: sticky;
  top: 0;
  z-index: 100;
}

.category-container {
  min-height: 100vh;
  background: #fff;


  .category-content {
    display: flex;
    height: calc(100vh - 88rpx);

    .main-category {
      width: 180rpx;
      height: 100%;
      background: #f8f8f8;

      &-item {
        height: 100rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28rpx;
        color: #333;
        position: relative;

        &.active {
          background: #fff;
          color: #FF6034;
          font-weight: 500;

          &::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6rpx;
            height: 36rpx;
            background: #FF6034;
            border-radius: 0 4rpx 4rpx 0;
          }
        }
      }
    }

    .sub-category {
      flex: 1;
      height: 100%;
      padding: 30rpx;
      box-sizing: border-box;

      &-title {
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 30rpx;
      }

      .category-section {
        margin-bottom: 40rpx;

        .section-title {
          font-size: 28rpx;
          color: #666;
          margin-bottom: 20rpx;
        }

        .category-grid {
          display: flex;
          flex-wrap: wrap;

          .grid-item {
            width: 33.33%;
            padding: 20rpx;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            align-items: center;

            image {
              width: 100rpx;
              height: 100rpx;
              margin-bottom: 16rpx;
            }

            text {
              font-size: 24rpx;
              color: #333;
              text-align: center;
            }
          }
        }
      }
    }
  }
}
</style>
