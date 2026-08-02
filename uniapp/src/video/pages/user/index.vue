<template>
	<view class="user-page">
		<!-- 主要内容区域 -->
		<view class="main-content">
			<!-- 顶部个人信息区域 -->
		<view class="top-section" :style="topSectionStyle">
			<!-- 顶部操作按钮 -->
			<view class="top-actions">
				<view class="action-btn" @click="goToPage({ url: '/video/pages/blogger/setting/index' })" v-if="isLogin && userInfo.blogger">
					<uni-icons type="gear" size="22" color="#fff"></uni-icons>
				</view>
				<view class="action-btn" @click="goToPage({ url: '/shared/pages/system/notice/index' })">
					<uni-icons type="notification" size="22" color="#fff"></uni-icons>
				</view>
			</view>

			<!-- 用户信息 -->
			<view class="user-profile"
				@click="isLogin ? goToPage({ url: '/video/pages/blogger/setting/profile' }) : navigateToAuth('login', '/video/pages/user/index')">
				<view class="avatar-container">
					<image class="avatar" :src="formatImageUrl(userInfo.blogger?.avatar || userInfo.avatar, ThumbnailPresets.small, 'avatar') + '?' + time" mode="aspectFill" />
					<!-- 认证标识 -->
					<view class="verification-badge" v-if="isLogin && userInfo.blogger?.verification_status === 1">
						<uni-icons type="checkmarkempty" size="16" color="#fff"></uni-icons>
					</view>
				</view>
				<view class="user-name">{{ isLogin ? (userInfo.blogger?.blogger_name || userInfo.nickname) : '点击登录' }}</view>
				<view class="user-desc" v-if="isLogin && userInfo.blogger?.description">
					{{ userInfo.blogger.description }}
				</view>
			</view>
		</view>

		<!-- 数据统计区域 -->
		<view class="stats-container">
			<view class="stats-grid">
				<view class="stat-item">
					<view class="stat-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.follower_count) : '0' }}</view>
					<view class="stat-label">粉丝</view>
				</view>
				<view class="stat-item">
					<view class="stat-value">{{ isLogin && userInfo.following_blogger_count ? formatNumber(userInfo.following_blogger_count) : '0' }}</view>
					<view class="stat-label">关注</view>
				</view>
				<view class="stat-item">
					<view class="stat-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.video_count) : '0' }}</view>
					<view class="stat-label">作品</view>
				</view>
				<view class="stat-item">
					<view class="stat-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.total_likes) : '0' }}</view>
					<view class="stat-label">获赞</view>
				</view>
			</view>
		</view>

		<!-- 内容管理模块 -->
		<view class="card" v-if="isLogin && userInfo.blogger">
			<view class="card-header" @click="goToPage({ url: '/video/pages/blogger/short/list' })">
				<view class="header-title">
					<view class="title-icon"><uni-icons type="videocam" size="18" color="#fff"></uni-icons></view>
					<text>内容管理</text>
				</view>
				<view class="header-action">
					<text>查看全部</text>
					<uni-icons type="right" size="14" color="#999"></uni-icons>
				</view>
			</view>

			<view class="content-icons">
				<view class="content-icon-item" @click="goToPage({ url: '/video/pages/blogger/short/publish' })">
					<view class="icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_feedback_pre.png')"
							class="icon-image" />
					</view>
					<text class="icon-text">发布视频</text>
				</view>
				<view class="content-icon-item" @click="goToPage({ url: '/video/pages/blogger/short/list' })">
					<view class="icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_consult_pre.png')" class="icon-image" />
					</view>
					<text class="icon-text">我的作品</text>
				</view>
                <!--
				<view class="content-icon-item" @click="goToPage({ url: '/video/pages/blogger/drama/index' })" v-if="userInfo.blogger?.is_drama_enabled">
					<view class="icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_drama.png')" class="icon-image" />
					</view>
					<text class="icon-text">短剧管理</text>
				</view>
				<view class="content-icon-item" @click="goToPage({ url: '/video/pages/blogger/live/index' })" v-if="userInfo.blogger?.is_live_enabled">
					<view class="icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_live.png')" class="icon-image" />
					</view>
					<text class="icon-text">直播管理</text>
				</view>
                -->
			</view>
		</view>

		<!-- 数据分析模块 -->
		<view class="card" v-if="isLogin && userInfo.blogger">
			<view class="card-header" @click="goToPage({ url: '/video/pages/blogger/analytics/index' })">
				<view class="header-title">
					<view class="title-icon"><uni-icons type="bars" size="18" color="#fff"></uni-icons></view>
					<text>数据分析</text>
				</view>
			</view>

			<view class="analytics-overview">
				<view class="analytics-item">
					<view class="analytics-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.total_views) : '0' }}</view>
					<view class="analytics-label">总播放量</view>
				</view>
				<view class="analytics-item">
					<view class="analytics-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.total_likes) : '0' }}</view>
					<view class="analytics-label">总点赞数</view>
				</view>
				<view class="analytics-item">
					<view class="analytics-value">{{ isLogin && userInfo.blogger ? formatNumber(userInfo.blogger.total_collect) : '0' }}</view>
					<view class="analytics-label">总收藏数</view>
				</view>
			</view>
		</view>

		<!-- 常用工具 -->
		<view class="card">
			<view class="card-header">
				<view class="header-title">
					<view class="title-icon"><uni-icons type="gear" size="18" color="#fff"></uni-icons></view>
					<text>常用工具</text>
				</view>
			</view>

			<view class="menu-grid">
				<view class="menu-item" @click="goToPage({ url: '/shared/pages/system/notice/index' })">
					<view class="menu-icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_notify_pre.png')" class="menu-icon" />
					</view>
					<text class="menu-text">消息通知</text>
				</view>
				<view class="menu-item" @click="goToPage({ url: '/shared/pages/contact_us' })">
					<view class="menu-icon-wrapper">
						<image :src="fetchRemoteIconUrl('/uniapp/user/index/icon_offence_pre.png')" class="menu-icon" />
					</view>
					<text class="menu-text">帮助中心</text>
				</view>
			</view>
		</view>
		</view>

		<!-- 底部导航 -->
		<VideoBottomNav 
			current="profile"
			theme="light"
		/>
	</view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
// @ts-ignore
import useUserStore from '@/stores/user'
import { storeToRefs } from 'pinia'
// @ts-ignore
import { goToPage, navigateToAuth } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets, fetchRemoteIconUrl } from '@/utils/image'
import { onShow } from '@dcloudio/uni-app'
// @ts-ignore
import VideoBottomNav from '@/video/components/video-bottom-nav/index.vue'

const userStore = useUserStore()
const { userInfo, isLogin } = storeToRefs(userStore)
const time = ref(Date.now())

// 格式化数字显示
const formatNumber = (num: number) => {
	if (num >= 10000) {
		return (num / 10000).toFixed(1) + 'w'
	} else if (num >= 1000) {
		return (num / 1000).toFixed(1) + 'k'
	}
	return num.toString()
}

// 计算顶部区域的样式
const topSectionStyle = computed(() => {
    return {
      backgroundImage: `url(${fetchRemoteIconUrl('/uniapp/user_header_bg.png')})`
    }
})


onShow(async () => {
	await userStore.getUserInfoWithRoles()
	time.value = Date.now() // 更新时间戳避免头像缓存
})
</script>

<style lang="scss" scoped>
.user-page {
	display: flex;
	flex-direction: column;
	min-height: 100vh;
	background-color: #f8f8f8;
}

.main-content {
	flex: 1;
	padding-bottom: 40rpx;
}

// 顶部个人信息区域
.top-section {
	position: relative;
	height: 420rpx;
	overflow: visible;
	background-color: #FF6B6B;
	background-size: 100% auto;
	background-position: center;
	background-repeat: no-repeat;

	.top-actions {
		position: absolute;
		top: 40rpx;
		left: 0;
		right: 0;
		z-index: 10;
		padding: 0 30rpx;
		display: flex;
		justify-content: space-between;
		pointer-events: none;

		.action-btn {
			width: 64rpx;
			height: 64rpx;
			display: flex;
			align-items: center;
			justify-content: center;
			background: rgba(255, 255, 255, 0.2);
			border-radius: 50%;
			backdrop-filter: blur(10px);
			transition: all 0.3s;
			pointer-events: auto;

			&:active {
				transform: scale(0.95);
				background: rgba(255, 255, 255, 0.3);
			}
		}
	}

	.user-profile {
		position: relative;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding-top: 40rpx;
		margin-bottom: 80rpx;
		z-index: 3;

		.avatar-container {
			position: relative;
			width: 120rpx;
			height: 120rpx;
			border-radius: 60rpx;
			background: #fff;
			display: flex;
			align-items: center;
			justify-content: center;
			box-shadow: 0 4rpx 20rpx rgba(0, 0, 0, 0.1);
			overflow: hidden;
			margin-bottom: 16rpx;
			border: 4rpx solid rgba(255, 255, 255, 0.6);
			transition: all 0.3s;

			&:active {
				transform: scale(0.98);
			}

			.avatar {
				width: 100%;
				height: 100%;
				border-radius: 50%;
				object-fit: cover;
			}

			.verification-badge {
				position: absolute;
				bottom: -2rpx;
				right: -2rpx;
				width: 32rpx;
				height: 32rpx;
				background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
				border-radius: 50%;
				display: flex;
				align-items: center;
				justify-content: center;
				border: 3rpx solid #fff;
			}
		}

		.user-name {
			font-size: 32rpx;
			color: #fff;
			font-weight: 600;
			margin-bottom: 8rpx;
			text-shadow: 0 2rpx 4rpx rgba(0, 0, 0, 0.1);
		}



		.user-desc {
			font-size: 24rpx;
			color: rgba(255, 255, 255, 0.9);
			text-align: center;
			max-width: 500rpx;
			line-height: 1.4;
			padding: 0 20rpx;
		}
	}
}

// 数据统计区域
.stats-container {
	position: relative;
	margin-top: -70rpx;
	margin-bottom: 20rpx;
	z-index: 5;
}

.stats-grid {
	margin: 0 20rpx;
	height: 140rpx;
	background: #fff;
	border-radius: 16rpx;
	box-shadow: 0 4rpx 16rpx rgba(255, 107, 107, 0.12);
	display: flex;
	align-items: center;
	position: relative;
	overflow: hidden;

	.stat-item {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		justify-content: center;
		position: relative;

		&:not(:last-child)::after {
			content: '';
			position: absolute;
			right: 0;
			top: 50%;
			transform: translateY(-50%);
			width: 2rpx;
			height: 50rpx;
			background: #f5f5f5;
		}

		.stat-value {
			font-size: 32rpx;
			color: #FF6B6B;
			font-weight: 600;
			margin-bottom: 8rpx;
		}

		.stat-label {
			font-size: 24rpx;
			color: #666;
		}
	}
}

// 卡片样式
.card {
	margin: 20rpx 20rpx;
	background: #fff;
	border-radius: 16rpx;
	overflow: hidden;
	box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

	.card-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		padding: 24rpx 30rpx;
		border-bottom: 2rpx solid #f5f5f5;

		.header-title {
			display: flex;
			align-items: center;

			.title-icon {
				width: 40rpx;
				height: 40rpx;
				border-radius: 20rpx;
				background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
				display: flex;
				align-items: center;
				justify-content: center;
				margin-right: 12rpx;
				box-shadow: 0 4rpx 8rpx rgba(255, 107, 107, 0.2);
			}

			text {
				font-size: 30rpx;
				font-weight: 600;
				color: #333;
			}
		}

		.header-action {
			display: flex;
			align-items: center;

			text {
				font-size: 26rpx;
				color: #999;
				margin-right: 6rpx;
			}
		}
	}
}

// 内容图标
.content-icons {
	display: flex;
	padding: 30rpx 20rpx;
	flex-wrap: wrap;

	.content-icon-item {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;
		min-width: 25%;

		.icon-wrapper {
			width: 96rpx;
			height: 96rpx;
			display: flex;
			align-items: center;
			justify-content: center;

			.icon-image {
				width: 56rpx;
				height: 56rpx;
			}
		}

		.icon-text {
			font-size: 26rpx;
			color: #666;
			margin-top: 16rpx;
		}
	}
}

// 数据分析概览
.analytics-overview {
	display: flex;
	padding: 20rpx 30rpx 30rpx;

	.analytics-item {
		flex: 1;
		display: flex;
		flex-direction: column;
		align-items: center;

		.analytics-value {
			font-size: 28rpx;
			color: #FF6B6B;
			font-weight: 600;
			margin-bottom: 8rpx;
		}

		.analytics-label {
			font-size: 22rpx;
			color: #999;
		}
	}
}

// 菜单网格
.menu-grid {
	display: flex;
	flex-wrap: wrap;
	padding: 10rpx 0;

	.menu-item {
		width: 33.33%;
		display: flex;
		flex-direction: column;
		align-items: center;
		padding: 20rpx 0;

		.menu-icon-wrapper {
			width: 88rpx;
			height: 88rpx;
			display: flex;
			align-items: center;
			justify-content: center;
			margin-bottom: 12rpx;

			.menu-icon {
				width: 48rpx;
				height: 48rpx;
			}
		}

		.menu-text {
			font-size: 24rpx;
			color: #666;
		}
	}
}
</style>