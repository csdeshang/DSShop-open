<template>
  <view class="level-container">
    <!-- 顶部等级卡片 -->
    <view class="level-card">
      <view class="card-header">
        <image class="user-avatar" :src="formatImageUrl(userInfo.avatar, ThumbnailPresets.small, 'avatar')" mode="aspectFill"></image>
        <view class="user-info">
          <text class="user-name">{{ userInfo.nickname || '尊敬的用户' }}</text>
          <text class="user-id">ID: {{ userInfo.id }}</text>
        </view>
      </view>
      
      <!-- 当前等级信息 -->
      <view class="current-level" v-if="levelInfo.level_progress">
        <view class="level-badge">
          <view class="level-icon">
            <uni-icons type="star-filled" size="24" color="#FFD700"></uni-icons>
          </view>
          <view class="level-info">
            <text class="level-name">{{ levelInfo.level_progress.current_level.level_name }}</text>
            <text class="level-desc">{{ levelInfo.level_progress.current_level.description }}</text>
          </view>
        </view>
        
        <!-- 成长值信息 -->
        <view class="growth-info">
          <view class="growth-value">{{ levelInfo.user_info.growth }}</view>
          <view class="growth-label">当前成长值</view>
        </view>
      </view>
      
      <!-- 等级进度 -->
      <view class="level-progress" v-if="levelInfo.level_progress && !levelInfo.level_progress.is_max_level">
        <view class="progress-header">
          <text class="progress-title">距离下一等级</text>
          <text class="progress-text">{{ levelInfo.level_progress.remaining_growth }}成长值</text>
        </view>
        <view class="progress-bar">
          <view class="progress-fill" :style="{ width: levelInfo.level_progress.progress_percent + '%' }"></view>
        </view>
        <view class="progress-info">
          <text class="next-level">{{ levelInfo.level_progress.next_level?.level_name }}</text>
          <text class="progress-percent">{{ levelInfo.level_progress.progress_percent }}%</text>
        </view>
      </view>
      
      <!-- 最高等级提示 -->
      <view class="max-level-tip" v-if="levelInfo.level_progress && levelInfo.level_progress.is_max_level">
        <uni-icons type="checkmarkempty" size="20" color="#52c41a"></uni-icons>
        <text class="tip-text">恭喜您已达到最高等级！</text>
      </view>
    </view>
    
    <!-- 等级特权 -->
    <view class="level-benefits">
      <view class="benefits-title">等级特权</view>
      <view class="benefits-grid">
        <view class="benefit-item" v-for="(benefit, index) in levelBenefits" :key="index">
          <view class="benefit-icon" :class="benefit.iconClass">
            <uni-icons :type="benefit.icon" size="20" color="#fff"></uni-icons>
          </view>
          <text class="benefit-name">{{ benefit.name }}</text>
          <text class="benefit-desc">{{ benefit.desc }}</text>
        </view>
      </view>
    </view>
    
    <!-- 等级列表 -->
    <view class="level-list">
      <view class="list-title">等级体系</view>
      <view class="level-items">
        <view class="level-item" v-for="(level, index) in levelList" :key="index"
          :class="level.is_current ? 'current' : level.is_achieved ? 'achieved' : 'locked'">
          <view class="level-rank">
            <view class="rank-number">{{ index + 1 }}</view>
            <view class="rank-line" v-if="index < levelList.length - 1"></view>
          </view>

          <view class="level-content">
            <view class="level-header">
              <text class="level-name">{{ level.level_name }}</text>
              <view class="level-status">
                <uni-icons v-if="level.is_current" type="checkmarkempty" size="16" color="#52c41a"></uni-icons>
                <uni-icons v-else-if="level.is_achieved" type="checkmarkempty" size="16" color="#1890ff"></uni-icons>
                <uni-icons v-else type="closeempty" size="16" color="#d9d9d9"></uni-icons>
              </view>
            </view>

            <text class="level-desc">{{ level.description }}</text>
            <text class="level-requirement">需要成长值: {{ level.min_growth }}</text>
          </view>
        </view>
      </view>
    </view>

    <!-- 成长值获取方式 -->
    <view class="growth-methods">
      <view class="methods-title">如何获得成长值</view>
      <view class="methods-list">
        <view class="method-item" v-for="(method, index) in activeGrowthMethods" :key="index">
          <view class="method-icon">
            <uni-icons :type="method.icon" size="18" color="#1890ff"></uni-icons>
          </view>
          <view class="method-info">
            <text class="method-name">{{ method.name }}</text>
            <text class="method-desc">{{ method.desc }}</text>
          </view>
          <text class="method-value">+{{ method.value }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { onShow } from '@dcloudio/uni-app'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
import { getUserLevelInfo, getUserLevelList } from '@/home/api/user/userLevel'
import { useConfigStore } from '@/stores/modules/config'

// 定义类型接口
interface LevelProgress {
  current_level: {
    level_name: string
    description: string
  }
  next_level?: {
    level_name: string
  }
  progress_percent: number
  remaining_growth: number
  is_max_level: boolean
}

interface UserInfo {
  growth: number
  growth_level_id: number
}

interface LevelInfo {
  user_info: UserInfo
  level_progress: LevelProgress | null
}

interface LevelItem {
  id: number
  level_name: string
  description: string
  min_growth: number
  is_current: boolean
  is_achieved: boolean
}



const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

// 网站配置
const configStore = useConfigStore()
// 获取成长值配置
const growthConfig = ref<Record<string, string>>({})

async function fetchGrowthConfig() {
  const configs = await configStore.fetchConfigsByType('growth')
  growthConfig.value = configs
}

// 等级信息
const levelInfo = ref<LevelInfo>({} as LevelInfo)
const levelList = ref<LevelItem[]>([])

// 等级特权
const levelBenefits = reactive([
  {
    name: '专属折扣',
    desc: '享受商品专属价格',
    icon: 'gift',
    iconClass: 'benefit-discount'
  },
  {
    name: '优先客服',
    desc: '享受优先客服服务',
    icon: 'chat',
    iconClass: 'benefit-service'
  }
])

// 根据配置动态生成成长值获取方式
const activeGrowthMethods = computed(() => {
  const methods = []
  
  // 登录获取成长值
  if (growthConfig.value.growth_login_enabled === '1') {
    methods.push({
      name: '每日登录',
      desc: '每日首次登录获得成长值',
      value: growthConfig.value.growth_login_amount,
      icon: 'calendar'
    })
  }
  
  // 注册获取成长值
  if (growthConfig.value.growth_register_enabled === '1') {
    methods.push({
      name: '新用户注册',
      desc: '新用户注册获得成长值',
      value: growthConfig.value.growth_register_amount,
      icon: 'personadd'
    })
  }
  
  // 支付获取成长值
  if (growthConfig.value.growth_pay_enabled === '1') {
    methods.push({
      name: '订单支付',
      desc: '每笔订单支付获得成长值',
      value: growthConfig.value.growth_pay_amount,
      icon: 'wallet'
    })
  }
  
  // 按比例获取成长值
  if (growthConfig.value.growth_payrate_enabled === '1') {
    methods.push({
      name: '消费成长值',
      desc: `每消费1元获得${growthConfig.value.growth_payrate_amount}成长值`,
      value: growthConfig.value.growth_payrate_amount,
      icon: 'shop'
    })
  }
  
  // 评价获取成长值
  if (growthConfig.value.growth_review_enabled === '1') {
    methods.push({
      name: '评价晒图',
      desc: '评价商品并上传图片',
      value: growthConfig.value.growth_review_amount,
      icon: 'camera'
    })
  }
  
  // 邀请获取成长值
  if (growthConfig.value.growth_invite_enabled === '1') {
    methods.push({
      name: '邀请好友',
      desc: '成功邀请新用户注册',
      value: growthConfig.value.growth_invite_amount,
      icon: 'personadd'
    })
  }
  
  return methods
})

// 获取等级信息
const fetchLevelInfo = async () => {
  try {
    const res = await getUserLevelInfo()
    levelInfo.value = res.data
  } catch (error) {
    console.error('获取等级信息失败:', error)
  }
}

// 获取等级列表
const fetchLevelList = async () => {
  try {
    const res = await getUserLevelList()
    levelList.value = res.data
  } catch (error) {
    console.error('获取等级列表失败:', error)
  }
}

onShow(() => {
  fetchLevelInfo()
  fetchLevelList()
  fetchGrowthConfig()
})
</script>

<style scoped lang="scss">
  .level-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #ff3b30 0%, #ff6b58 100%);
    padding: 20rpx;
  }

// 等级卡片
.level-card {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 24rpx;
  padding: 40rpx 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.1);
  backdrop-filter: blur(10rpx);
  
  .card-header {
    display: flex;
    align-items: center;
    margin-bottom: 30rpx;
    
          .user-avatar {
        width: 96rpx;
        height: 96rpx;
        border-radius: 48rpx;
        border: 4rpx solid rgba(255, 59, 48, 0.2);
        background-color: #f0f0f0;
      }
    
    .user-info {
      margin-left: 20rpx;
      
      .user-name {
        font-size: 32rpx;
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 6rpx;
      }
      
      .user-id {
        font-size: 24rpx;
        color: #999;
      }
    }
  }
  
  .current-level {
    margin-bottom: 30rpx;
    
    .level-badge {
      display: flex;
      align-items: center;
      background: linear-gradient(135deg, #ffd700, #ffed4e);
      border-radius: 16rpx;
      padding: 20rpx;
      margin-bottom: 20rpx;
      
      .level-icon {
        margin-right: 16rpx;
      }
      
      .level-info {
        .level-name {
          font-size: 28rpx;
          font-weight: 600;
          color: #333;
          display: block;
          margin-bottom: 4rpx;
        }
        
        .level-desc {
          font-size: 24rpx;
          color: #666;
        }
      }
    }
    
    .growth-info {
      text-align: center;
      
      .growth-value {
        font-size: 48rpx;
        font-weight: 700;
        color: #ff3b30;
        line-height: 1.2;
      }
      
      .growth-label {
        font-size: 24rpx;
        color: #999;
        margin-top: 8rpx;
      }
    }
  }
  
  .level-progress {
    .progress-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 16rpx;
      
      .progress-title {
        font-size: 26rpx;
        color: #333;
        font-weight: 500;
      }
      
      .progress-text {
        font-size: 24rpx;
        color: #ff3b30;
        font-weight: 500;
      }
    }
    
    .progress-bar {
      height: 12rpx;
      background: #f0f0f0;
      border-radius: 6rpx;
      overflow: hidden;
      margin-bottom: 12rpx;
      
      .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #ff3b30, #ff6b58);
        border-radius: 6rpx;
        transition: width 0.3s ease;
      }
    }
    
    .progress-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      
      .next-level {
        font-size: 24rpx;
        color: #333;
        font-weight: 500;
      }
      
      .progress-percent {
        font-size: 24rpx;
        color: #ff3b30;
        font-weight: 600;
      }
    }
  }
  
  .max-level-tip {
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #52c41a, #73d13d);
    border-radius: 16rpx;
    padding: 20rpx;
    
    .tip-text {
      font-size: 26rpx;
      color: #fff;
      font-weight: 500;
      margin-left: 12rpx;
    }
  }
}

// 等级特权
.level-benefits {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 24rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.1);
  
  .benefits-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 24rpx;
  }
  
  .benefits-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20rpx;
    
    .benefit-item {
      background: #f8f9fa;
      border-radius: 16rpx;
      padding: 24rpx;
      text-align: center;
      
      .benefit-icon {
        width: 60rpx;
        height: 60rpx;
        border-radius: 30rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16rpx;
        
        &.benefit-discount {
          background: linear-gradient(135deg, #ff6b6b, #ff8e8e);
        }
        
        &.benefit-service {
          background: linear-gradient(135deg, #45b7d1, #96c93d);
        }
      }
      
      .benefit-name {
        font-size: 26rpx;
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 8rpx;
      }
      
      .benefit-desc {
        font-size: 22rpx;
        color: #666;
        line-height: 1.4;
      }
    }
  }
}

// 等级列表
.level-list {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 24rpx;
  padding: 30rpx;
  margin-bottom: 30rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.1);
  
  .list-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 24rpx;
  }
  
  .level-items {
    .level-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 30rpx;
      
      &:last-child {
        margin-bottom: 0;
      }
      
      .level-rank {
        position: relative;
        margin-right: 20rpx;
        
        .rank-number {
          width: 48rpx;
          height: 48rpx;
          border-radius: 24rpx;
          background: #f0f0f0;
          color: #999;
          font-size: 24rpx;
          font-weight: 600;
          display: flex;
          align-items: center;
          justify-content: center;
        }
        
        .rank-line {
          position: absolute;
          left: 50%;
          top: 48rpx;
          width: 2rpx;
          height: 60rpx;
          background: #f0f0f0;
          transform: translateX(-50%);
        }
      }
      
      .level-content {
        flex: 1;
        
        .level-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 8rpx;
          
          .level-name {
            font-size: 28rpx;
            font-weight: 600;
            color: #333;
          }
          
          .level-status {
            display: flex;
            align-items: center;
          }
        }
        
        .level-desc {
          font-size: 24rpx;
          color: #666;
          line-height: 1.4;
          margin-bottom: 8rpx;
        }
        
        .level-requirement {
          font-size: 22rpx;
          color: #999;
        }
      }
      
      &.current {
        .level-rank .rank-number {
          background: linear-gradient(135deg, #ff3b30, #ff6b58);
          color: #fff;
        }
        
        .level-content .level-name {
          color: #ff3b30;
        }
      }
      
      &.achieved {
        .level-rank .rank-number {
          background: #52c41a;
          color: #fff;
        }
      }
      
      &.locked {
        opacity: 0.6;
      }
    }
  }
}

// 成长值获取方式
.growth-methods {
  background: rgba(255, 255, 255, 0.95);
  border-radius: 24rpx;
  padding: 30rpx;
  box-shadow: 0 8rpx 32rpx rgba(0, 0, 0, 0.1);
  margin-bottom: 30rpx;
  
  .methods-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 24rpx;
  }
  
  .methods-list {
    .method-item {
      display: flex;
      align-items: center;
      padding: 20rpx 0;
      border-bottom: 1rpx solid #f0f0f0;
      
      &:last-child {
        border-bottom: none;
      }
      
      .method-icon {
        width: 48rpx;
        height: 48rpx;
        border-radius: 24rpx;
        background: rgba(255, 59, 48, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 16rpx;
      }
      
      .method-info {
        flex: 1;
        
        .method-name {
          font-size: 26rpx;
          font-weight: 500;
          color: #333;
          display: block;
          margin-bottom: 4rpx;
        }
        
        .method-desc {
          font-size: 22rpx;
          color: #666;
        }
      }
      
      .method-value {
        font-size: 24rpx;
        font-weight: 600;
        color: #ff3b30;
      }
    }
  }
}
</style>
