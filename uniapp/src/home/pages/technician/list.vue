<template>
    <view class="technician-list-container">

        <z-paging ref="paging" v-model="dataList" @query="queryList">

            <view v-for="item in dataList" :key="item.id" class="technician-item" @click="goToPage({url:'/home/pages/technician/detail/index',params:{id:item.id}})">
                <view class="technician-avatar">
                    <image :src="formatImageUrl(item.avatar, ThumbnailPresets.small, 'avatar')"
                        mode="aspectFill"></image>
                </view>
                <view class="technician-info">
                    <view class="technician-top">
                        <view class="technician-name">
                            {{ item.name }}
                            <text class="technician-gender"
                                :class="item.gender === 1 ? 'male' : (item.gender === 2 ? 'female' : '')">
                                {{ item.gender === 1 ? '♂' : (item.gender === 2 ? '♀' : '') }}
                            </text>
                        </view>
                        <view class="technician-years">{{ item.work_years || 0 }}年经验</view>
                    </view>

                    <view class="technician-score">
                        <text class="score-label">评分:</text>
                        <text class="score-value">{{ parseFloat(item.avg_score || '0').toFixed(1) }}</text>
                        <text class="visit-count">{{ item.service_count || 0 }}次预约</text>
                    </view>

                    <view class="technician-desc" v-if="item.description">{{ item.description }}</view>

                    <view class="technician-bottom">
                        <view class="status-tag" :class="item.technician_status === 1 ? 'active' : 'inactive'">
                            {{ item.technician_status === 1 ? '可预约' : '休息中' }}
                        </view>
                        <view class="distance-info" v-if="item.distance">
                            距离: {{ formatDistance(item.distance) }}
                        </view>
                    </view>
                </view>
            </view>


        </z-paging>
    </view>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { getTechnicianPages } from '@/home/api/technician/technician'
import { onLoad } from '@dcloudio/uni-app'
import { goToPage } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { useLbsStore } from '@/stores/modules/lbs'
import { useConfigStore } from '@/stores/modules/config';

interface Technician {
    id: number;
    user_id: number;
    name: string;
    mobile: string;
    avatar: string | null;
    gender: number;
    certificate_info: string;
    work_years: number;
    description: string;
    avg_score: string;
    visit_count: number;
    service_count: number;
    technician_status: number;
    technician_fee_rate: number;
    technician_latitude: string | null;
    technician_longitude: string | null;
    technician_loc_time: number;
    balance: number;
    balance_in: number;
    balance_out: number;
    is_enabled: number;
    create_at: string;
    update_at: string;
    apply_time: number;
    apply_status: number;
    apply_remark: string;
    audit_time: string | null;
    audit_remark: string;
    distance: number | null;
}

const paging = ref<any>(null)
const dataList = ref<Technician[]>([])

// LBS store
const lbsStore = useLbsStore()
const configStore = useConfigStore();

// 是否开启定位
const lbs_is_enabled = ref('0');
async function fetchLbsConfig() {
    const config = await configStore.fetchConfig('lbs:lbs_is_enabled');
    lbs_is_enabled.value = config || '0';
}

// 查询参数 
const params = ref({
    goods_id: '',
    longitude: 0,
    latitude: 0
});





// 格式化距离
const formatDistance = (distance: number) => {
    if (!distance) return '';
    if (distance < 1000) {
        return `${distance.toFixed(0)}m`;
    } else {
        return `${(distance / 1000).toFixed(1)}km`;
    }
};

// 获取用户位置
const getUserLocation = async () => {
    try {
        // 先判断是否开启经纬度功能
        await fetchLbsConfig();

        if (lbs_is_enabled.value == '1') {
            // 获取位置信息 设置为 30 天 ， 如果有经纬度主要是拉取本地存储的位置信息
            await lbsStore.getLocation(true, 24 * 60 * 60 * 30);
            
            // 如果获取到位置信息，更新参数
            if (lbsStore.latitude && lbsStore.longitude) {
                params.value.longitude = lbsStore.longitude;
                params.value.latitude = lbsStore.latitude;
            }
        }
    } catch (error) {
        console.log('获取位置失败:', error);
    }
};

const queryList = async (page_current: number, page_size: number) => {
    try {
        const result = await getTechnicianPages({
            page_current,
            page_size,
            ...params.value
        })
        if (paging.value) {
            paging.value.complete(result.data.data)
        }
    } catch (e) {
        console.log('下拉加载', e)
        if (paging.value) {
            paging.value.complete(false)
        }
    }
}

onLoad(async (options?: any) => {
    if (options) {
        if (options.goods_id) {
            params.value.goods_id = options.goods_id;
        }
    }
    
    // 获取用户位置
    await getUserLocation();
});
</script>

<style lang="scss" scoped>
.technician-list-container {
    min-height: 100vh;
    background-color: #f7f7f7;


    .technician-item {
        margin: 20rpx;
        padding: 30rpx;
        background-color: #ffffff;
        border-radius: 12rpx;
        display: flex;
        box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

        .technician-avatar {
            width: 120rpx;
            height: 120rpx;
            border-radius: 50%;
            overflow: hidden;
            margin-right: 20rpx;
            flex-shrink: 0;

            image {
                width: 100%;
                height: 100%;
            }
        }

        .technician-info {
            flex: 1;
            overflow: hidden;

            .technician-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10rpx;

                .technician-name {
                    font-size: 32rpx;
                    font-weight: bold;
                    color: #333;

                    .technician-gender {
                        font-size: 24rpx;
                        margin-left: 8rpx;

                        &.male {
                            color: #33a9ff;
                        }

                        &.female {
                            color: #ff6699;
                        }
                    }
                }

                .technician-years {
                    font-size: 24rpx;
                    color: #666;
                    background-color: #f5f5f5;
                    padding: 4rpx 12rpx;
                    border-radius: 20rpx;
                }
            }

            .technician-score {
                font-size: 26rpx;
                color: #666;
                margin-bottom: 10rpx;

                .score-label {
                    margin-right: 6rpx;
                }

                .score-value {
                    color: #ff9500;
                    font-weight: bold;
                    margin-right: 20rpx;
                }

                .visit-count {
                    color: #999;
                    font-size: 24rpx;
                }
            }

            .technician-desc {
                font-size: 26rpx;
                color: #666;
                margin-bottom: 10rpx;
                overflow: hidden;
                text-overflow: ellipsis;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
            }

            .technician-bottom {
                display: flex;
                justify-content: space-between;
                align-items: center;

                .status-tag {
                    font-size: 24rpx;
                    padding: 4rpx 12rpx;
                    border-radius: 6rpx;

                    &.active {
                        background-color: rgba(51, 169, 255, 0.1);
                        color: #33a9ff;
                    }

                    &.inactive {
                        background-color: rgba(153, 153, 153, 0.1);
                        color: #999;
                    }
                }

                .distance-info {
                    font-size: 24rpx;
                    color: #999;
                }
            }
        }


    }

    .empty-list {
        padding: 80rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;

        .empty-image {
            width: 200rpx;
            height: 200rpx;
            margin-bottom: 20rpx;
        }

        .empty-text {
            font-size: 28rpx;
            color: #999;
        }
    }
}
</style>