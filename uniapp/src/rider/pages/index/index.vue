<template>
	<!-- 未登录状态 -->
	<view v-if="!userInfo.id" class="not-login">
		<view class="login-tips">您尚未登录</view>
		<view class="login-desc">登录后可使用骑手相关功能</view>
		<button class="login-btn" @click="navigateToAuth('login', '/rider/pages/index/index')">立即登录</button>
	</view>

	<!-- 未成为骑手状态 -->
	<view v-else-if="userInfo.rider.length == 0 || userInfo.rider.apply_status != 1">
		<view class="rider-apply">
			<view class="title">成为骑手</view>
			<view class="desc">加入我们的骑手团队，开启灵活工作模式</view>
			<view class="feature-list">
				<view class="feature-item">
					<view class="feature-title">自由接单</view>
					<view class="feature-desc">灵活选择接单时间，兼职全职均可</view>
				</view>
				<view class="feature-item">
					<view class="feature-title">收入可观</view>
					<view class="feature-desc">多劳多得，额外奖励与补贴</view>
				</view>
				<view class="feature-item">
					<view class="feature-title">智能派单</view>
					<view class="feature-desc">系统智能分配，提高配送效率</view>
				</view>
				<view class="feature-item">
					<view class="feature-title">全程保障</view>
					<view class="feature-desc">提供配送保险，安心工作无忧</view>
				</view>
			</view>
			<view class="btn-wrapper">
				<button class="btn" @click="goToPage({ url: '/shared/pages/apply/rider' })">立即申请</button>
			</view>
		</view>
	</view>

	<!-- 已是骑手状态 -->
	<view v-else>
		<!-- 工作台 -->
		<RiderDashboard />
	</view>
</template>

<script lang="ts" setup>
import useUserStore from '@/stores/user'
import RiderDashboard from './rider-dashboard.vue'


import { storeToRefs } from 'pinia'
import { onMounted, ref } from 'vue'
import { goToPage, navigateToAuth } from '@/utils/util'
import { useConfigStore } from '@/stores/modules/config';

// 网站配置
const configStore = useConfigStore();
// 获取网站配置
const websiteConfig = ref<Record<string, string>>({});
async function fetchWebsiteConfig() {
	websiteConfig.value = await configStore.fetchConfigsByType('website');
}

const userStore = useUserStore()
const { userInfo } = storeToRefs(userStore)

// 确保userInfo加载完成
const isLoading = ref(true)

onMounted(async () => {
	isLoading.value = true
	await userStore.getUserInfoWithRoles()
	await fetchWebsiteConfig()
	isLoading.value = false
})

</script>

<style lang="scss" scoped>
.not-login {
	padding: 0rpx 30rpx;
	background-color: #fff;
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;

	.login-tips {
		font-size: 36rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 20rpx;
		text-align: center;
	}

	.login-desc {
		font-size: 28rpx;
		color: #999;
		margin-bottom: 60rpx;
		text-align: center;
	}

	.login-btn {
		background: linear-gradient(to right, #33a9ff, #0080ff);
		color: #fff;
		font-size: 32rpx;
		padding: 20rpx 120rpx;
		border-radius: 40rpx;
		border: none;
		box-shadow: 0 6rpx 12rpx rgba(51, 169, 255, 0.3);

		&:active {
			opacity: 0.9;
			transform: translateY(2rpx);
		}
	}
}

.rider-apply {
	padding: 0rpx 30rpx;
	background-color: #fff;
	min-height: 100vh;
	display: flex;
	flex-direction: column;
	align-items: center;
	justify-content: center;

	.title {
		font-size: 40rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 20rpx;
		text-align: center;
	}

	.desc {
		font-size: 28rpx;
		color: #666;
		margin-bottom: 40rpx;
		text-align: center;
	}

	.feature-list {
		width: 100%;
		margin-bottom: 40rpx;
	}

	.feature-item {
		margin-bottom: 30rpx;
		background-color: #f8f8f8;
		border-radius: 12rpx;
		padding: 20rpx;
		box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.05);
	}

	.feature-title {
		font-size: 28rpx;
		font-weight: bold;
		color: #333;
		margin-bottom: 10rpx;
	}

	.feature-desc {
		font-size: 24rpx;
		color: #666;
		line-height: 1.5;
	}

	.btn-wrapper {
		margin-top: 20rpx;
	}

	.btn {
		background-color: #33a9ff;
		color: #fff;
		font-size: 32rpx;
		padding: 10rpx 200rpx;
		border-radius: 20rpx;
		border: none;
		box-shadow: 0 4rpx 8rpx rgba(51, 169, 255, 0.2);

		&:active {
			opacity: 0.9;
			transform: translateY(2rpx);
		}
	}
}
</style>
