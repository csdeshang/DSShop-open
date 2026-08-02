<template>
    <view class="relation-container">


        <!-- 使用z-paging组件 -->
        <z-paging ref="paging" v-model="dataList" @query="queryList">
            <!-- 层级选择 -->
            <view class="level-tabs">
                <view class="tab-item" :class="{ active: currentLevel === 1 }" @click="switchLevel(1)">
                    一级推荐
                </view>
                <view class="tab-item" :class="{ active: currentLevel === 2 }" @click="switchLevel(2)">
                    二级推荐
                </view>
            </view>
            <!-- 关系列表 -->
            <view class="relation-list" v-if="dataList.length > 0">
                <view class="relation-item" v-for="item in dataList" :key="item.id">
                    <!-- 会员基本信息 -->
                    <view class="user-info">
                        <image class="avatar" :src="formatImageUrl(item.avatar, ThumbnailPresets.small, 'avatar')"
                            mode="aspectFill"></image>
                        <view class="user-detail">
                            <view class="username">{{ item.username }}</view>
                            <view class="distributor-tag" v-if="item.is_distributor === 1">分销商</view>
                        </view>
                    </view>

                    <!-- 佣金信息 -->
                    <view class="commission-info">
                        <view class="commission-item">
                            <text class="label">累计收入</text>
                            <text class="value income">¥{{ item.distributor_balance_in  }}</text>
                        </view>
                        <view class="commission-item">
                            <text class="label">累计支出</text>
                            <text class="value outcome">¥{{ item.distributor_balance_out  }}</text>
                        </view>
                    </view>
                </view>
            </view>

        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getDistributorRelationPages } from '@/home/api/distributor/distributor'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// 定义会员关系类型
interface RelationItem {
    id: number;
    username: string;
    avatar: string | null;
    is_distributor: number;
    distributor_balance_in: number;
    distributor_balance_out: number;
}

// 当前选中的层级(1或2)
const currentLevel = ref(1)

// 切换层级
const switchLevel = (level: number) => {
    if (currentLevel.value !== level) {
        currentLevel.value = level
        // 刷新列表
        if (paging.value) {
            paging.value.reload()
        }
    }
}

const paging = ref<any>(null)
const dataList = ref<RelationItem[]>([])

const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getDistributorRelationPages({
            page_current,
            page_size,
            level: currentLevel.value // 添加层级参数
        })
        paging.value?.complete(result.data.data)
    } catch (e) {
        console.log('下拉加载', e)
        paging.value?.complete(false)
    }
}
</script>

<style scoped lang="scss">
.relation-container {
    min-height: 100vh;
    background-color: #f5f5f5;
    padding-top: 10px;
}

.level-tabs {
    display: flex;
    background-color: #fff;
    padding: 15px 0;
    margin-bottom: 10px;
    border-radius: 8px;

    .tab-item {
        flex: 1;
        text-align: center;
        font-size: 14px;
        color: #666;
        position: relative;
        padding-bottom: 5px;

        &.active {
            color: #2196f3;
            font-weight: bold;

            &::after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 20px;
                height: 2px;
                background-color: #2196f3;
                border-radius: 1px;
            }
        }
    }
}

.relation-list {
    padding: 0 10px;
}

.relation-item {
    background-color: #fff;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.user-info {
    display: flex;
    align-items: center;

    .avatar {
        width: 50px;
        height: 50px;
        border-radius: 25px;
        margin-right: 10px;
        background-color: #f0f0f0;
    }

    .user-detail {
        .username {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }

        .distributor-tag {
            display: inline-block;
            font-size: 12px;
            color: #fff;
            background-color: #2196f3;
            padding: 2px 6px;
            border-radius: 4px;
        }
    }
}

.commission-info {
    text-align: right;

    .commission-item {
        margin-bottom: 5px;

        &:last-child {
            margin-bottom: 0;
        }

        .label {
            font-size: 12px;
            color: #999;
            margin-right: 5px;
        }

        .value {
            font-size: 14px;
            font-weight: bold;

            &.income {
                color: #f44336;
            }

            &.outcome {
                color: #4caf50;
            }
        }
    }
}


</style>