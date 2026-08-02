<template>
  <view class="sign-container">
    <view class="sign-card">
      <view class="sign-header">
        <text class="sign-title">每日签到</text>
        <text class="sign-desc">连续签到积分翻倍</text>
      </view>
      <view class="sign-status" v-if="!loading">
        <view class="status-row">
          <text class="status-label">今日状态</text>
          <text class="status-value" :class="{ signed: signStatus.signed_today }">
            {{ signStatus.signed_today ? '已签到' : '未签到' }}
          </text>
        </view>
        <view class="status-row">
          <text class="status-label">连续签到</text>
          <text class="status-value streak">{{ signStatus.streak || 0 }} 天</text>
        </view>
        <view class="status-row" v-if="signStatus.sign_last_date">
          <text class="status-label">最后签到</text>
          <text class="status-value">{{ signStatus.sign_last_date }}</text>
        </view>
      </view>
      <view class="sign-actions">
        <button
          class="sign-btn"
          :class="{ disabled: signStatus.signed_today }"
          :disabled="signStatus.signed_today || signing"
          :loading="signing"
          @click="handleSign"
        >
          {{ signStatus.signed_today ? '今日已签到' : '立即签到' }}
        </button>
      </view>
    </view>
    <view class="sign-rules" v-if="pointsConfig.points_sign_enabled === '1' && pointsConfig.points_sign_amount">
      <view class="rules-title">签到规则</view>
      <view class="rules-list">
        <text class="rules-item">· 每日签到可获得 {{ pointsConfig.points_sign_amount || '0' }} 积分</text>
        <text class="rules-item" v-if="pointsConfig.points_sign_streak_enabled === '1'">
          · 连续签到满 {{ pointsConfig.points_sign_streak_after_days || '3' }} 天起享受 {{ pointsConfig.points_sign_streak_multiplier || '1.5' }} 倍奖励
        </text>
        <text class="rules-item" v-if="pointsConfig.points_sign_streak_enabled === '1' && pointsConfig.points_sign_streak_max_days">
          · 连续天数封顶 {{ pointsConfig.points_sign_streak_max_days }} 天
        </text>
      </view>
    </view>
    <view class="sign-tips">
      <text class="tips-text">每日签到可获得积分，连续签到奖励更多</text>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { getUserPointsSignStatus, doUserPointsSign } from '@/home/api/user/user'
import { useConfigStore } from '@/stores/modules/config'
import useUserStore from '@/stores/user'

const loading = ref(true)
const signing = ref(false)
const pointsConfig = ref<Record<string, string>>({})
const signStatus = ref<{
  signed_today: boolean
  streak: number
  sign_last_date: string | null
}>({
  signed_today: false,
  streak: 0,
  sign_last_date: null,
})

const configStore = useConfigStore()
const userStore = useUserStore()

const fetchStatus = async () => {
  try {
    loading.value = true
    const res = await getUserPointsSignStatus()
    if (res.code === 10000 && res.data) {
      signStatus.value = {
        signed_today: !!res.data.signed_today,
        streak: res.data.streak || 0,
        sign_last_date: res.data.sign_last_date || null,
      }
    }
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

const handleSign = async () => {
  if (signStatus.value.signed_today || signing.value) return
  try {
    signing.value = true
    const res = await doUserPointsSign()
    if (res.code === 10000 && res.data) {
      uni.showToast({
        title: `签到成功，+${res.data.points} 积分`,
        icon: 'success',
      })
      signStatus.value.signed_today = true
      signStatus.value.streak = res.data.streak || signStatus.value.streak + 1
      signStatus.value.sign_last_date = new Date().toISOString().slice(0, 10)
      userStore.getUserInfoWithRoles(true)
    }
  } catch (e: any) {
    uni.showToast({
      title: e?.message || e?.msg || '签到失败',
      icon: 'none',
    })
  } finally {
    signing.value = false
  }
}

async function fetchPointsConfig() {
  pointsConfig.value = await configStore.fetchConfigsByType('points')
}

onMounted(() => {
  fetchStatus()
  fetchPointsConfig()
})
</script>

<style scoped lang="scss">
.sign-container {
  min-height: 100vh;
  background-color: #fff5f5;
  padding: 30rpx;
}

.sign-card {
  background: linear-gradient(135deg, #ff3b30, #ff6b58);
  border-radius: 24rpx;
  padding: 40rpx 30rpx;
  color: #fff;
  box-shadow: 0 8rpx 16rpx rgba(255, 59, 48, 0.2);
  margin-bottom: 24rpx;
}

.sign-header {
  text-align: center;
  margin-bottom: 40rpx;
  .sign-title {
    font-size: 40rpx;
    font-weight: 700;
    display: block;
    margin-bottom: 12rpx;
  }
  .sign-desc {
    font-size: 26rpx;
    opacity: 0.9;
  }
}

.sign-status {
  background: rgba(255, 255, 255, 0.15);
  border-radius: 16rpx;
  padding: 24rpx;
  margin-bottom: 32rpx;
  .status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16rpx 0;
    border-bottom: 2rpx solid rgba(255, 255, 255, 0.2);
    &:last-child {
      border-bottom: none;
    }
  }
  .status-label {
    font-size: 28rpx;
    opacity: 0.9;
  }
  .status-value {
    font-size: 28rpx;
    font-weight: 600;
    &.signed {
      color: #ffe66d;
    }
    &.streak {
      color: #ffe66d;
    }
  }
}

.sign-actions {
  text-align: center;
  .sign-btn {
    width: 100%;
    height: 88rpx;
    line-height: 88rpx;
    font-size: 32rpx;
    font-weight: 600;
    color: #ff3b30;
    background: #fff;
    border-radius: 44rpx;
    border: none;
    margin: 0;
    &.disabled {
      background: rgba(255, 255, 255, 0.6);
      color: #999;
    }
  }
}

.sign-rules {
  background: #fff;
  border-radius: 24rpx;
  padding: 28rpx 30rpx;
  margin-bottom: 24rpx;
  box-shadow: 0 4rpx 12rpx rgba(0, 0, 0, 0.05);
  .rules-title {
    font-size: 30rpx;
    font-weight: 600;
    color: #333;
    margin-bottom: 20rpx;
    padding-left: 16rpx;
    border-left: 6rpx solid #ff3b30;
  }
  .rules-list {
    display: flex;
    flex-direction: column;
    gap: 12rpx;
    .rules-item {
      font-size: 26rpx;
      color: #666;
      line-height: 1.5;
    }
  }
}

.sign-tips {
  padding: 20rpx 0;
  text-align: center;
  .tips-text {
    font-size: 24rpx;
    color: #999;
  }
}
</style>
