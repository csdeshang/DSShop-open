<template>
    <view class="distributor-apply">
        <view class="header">
            <text class="title">分销员申请</text>
            <text class="subtitle">成为分销员，开启您的创业之路</text>
        </view>
        
        <view class="benefits-section">
            <view class="section-title">
                <uni-icons type="star-filled" size="20" color="#007AFF"></uni-icons>
                <text>分销员权益</text>
            </view>
            <view class="benefits-grid">
                <view class="benefit-item">
                    <uni-icons type="wallet" size="30" color="#007AFF"></uni-icons>
                    <text class="benefit-title">佣金收益</text>
                    <text class="benefit-desc">获得商品销售佣金</text>
                </view>
                <view class="benefit-item">
                    <uni-icons type="gift" size="30" color="#007AFF"></uni-icons>
                    <text class="benefit-title">专属优惠</text>
                    <text class="benefit-desc">享受分销员专属折扣</text>
                </view>
            </view>
        </view>

        <view v-if="distributorConfig.distributor_conditions !== 'none'" class="apply-container">
            <view class="section-title">
                <uni-icons type="checkbox" size="20" color="#007AFF"></uni-icons>
                <text>申请条件</text>
            </view>
            <view class="condition-content">
                <view v-if="distributorConfig.distributor_conditions === 'amount'" class="condition-item">
                    <text class="label">消费金额</text>
                    <text class="value">满 {{ distributorConfig.distributor_apply_amount }} 元</text>
                </view>
                <view v-else-if="distributorConfig.distributor_conditions === 'count'" class="condition-item">
                    <text class="label">消费次数</text>
                    <text class="value">满 {{ distributorConfig.distributor_apply_count }} 次</text>
                </view>
            </view>

            <view class="apply-steps">
                <view class="section-title">
                    <uni-icons type="list" size="20" color="#007AFF"></uni-icons>
                    <text>申请流程</text>
                </view>
                <view class="steps-list">
                    <view class="step-item">
                        <text class="step-num">1</text>
                        <text class="step-text">提交申请</text>
                    </view>
                    <view class="step-item">
                        <text class="step-num">2</text>
                        <text class="step-text">等待审核</text>
                    </view>
                    <view class="step-item">
                        <text class="step-num">3</text>
                        <text class="step-text">审核通过</text>
                    </view>
                    <view class="step-item">
                        <text class="step-num">4</text>
                        <text class="step-text">开始分销</text>
                    </view>
                </view>
            </view>
        </view>

        <view class="tips-section">
            <view class="tips-title">
                <uni-icons type="info" size="20" color="#007AFF"></uni-icons>
                <text>温馨提示</text>
            </view>
            <view class="tips-content">
                <text class="tip-item">• 成为分销员后，您将获得专属推广码</text>
                <text class="tip-item">• 通过您的推广码购买商品，您将获得相应佣金</text>
                <text class="tip-item">• 佣金将在订单完成后自动结算到您的账户</text>
            </view>
        </view>


        <!-- 分销员申请资格 -->
        <view v-if="distributorApplyPermission.allow" class="permission-section">
            <view class="section-title">
                <uni-icons type="checkbox-filled" size="20" color="#19be6b"></uni-icons>
                <text>申请资格</text>
            </view>
            <view class="permission-content">
                <view class="permission-icon">
                    <uni-icons type="checkmarkempty" size="40" color="#19be6b"></uni-icons>
                </view>
                <view class="permission-info">
                    <text class="permission-title">您已满足申请条件</text>
                    <text class="permission-desc">{{ distributorApplyPermission.message }}</text>
                </view>
            </view>
        </view>



        <!-- 申请状态信息 -->
        <view v-if="distributorApplyInfo.id" class="status-section">
            <view class="section-title">
                <uni-icons type="info-circle" size="20" color="#007AFF"></uni-icons>
                <text>申请状态</text>
            </view>
            <view class="status-content">
                <view class="status-item">
                    <text class="label">申请时间：</text>
                    <text class="value">{{ distributorApplyInfo.apply_time }}</text>
                </view>
                <view class="status-item">
                    <text class="label">申请状态：</text>
                    <text class="value">
                        {{ distributorApplyInfo.apply_status == '0' ? '待审核' : 
                           distributorApplyInfo.apply_status == '1' ? '已通过' : 
                           distributorApplyInfo.apply_status == '2' ? '已拒绝' : '未知状态' }}
                    </text>
                </view>
                <view class="status-item" v-if="distributorApplyInfo.apply_remark">
                    <text class="label">申请备注：</text>
                    <text class="value">{{ distributorApplyInfo.apply_remark }}</text>
                </view>
                <view class="status-item" v-if="distributorApplyInfo.audit_remark">
                    <text class="label">审核备注：</text>
                    <text class="value reject-reason">{{ distributorApplyInfo.audit_remark }}</text>
                </view>
                <view class="status-item" v-if="distributorApplyInfo.audit_time">
                    <text class="label">审核时间：</text>
                    <text class="value">{{ distributorApplyInfo.audit_time }}</text>
                </view>
            </view>
        </view>

        <button v-if="!distributorApplyInfo.id || distributorApplyInfo.apply_status === 2" 
                type="primary"
                @click="applyDistributor">立即申请成为分销员</button>
        <button v-if="distributorApplyInfo.id && distributorApplyInfo.apply_status === 1" type="primary" @click="goToPage({ url: '/home/pages/distributor/index' })">进入分销中心</button>

    </view>
</template>


<script setup lang="ts">
import { ref } from 'vue';
import { useConfigStore } from '@/stores/modules/config';
import { onLoad } from '@dcloudio/uni-app';
import { getDistributorApplyInfo, getDistributorApplyPermission, createDistributorApply } from '@/home/api/distributor/distributorApply';
import { goToPage } from '@/utils/util';
const configStore = useConfigStore();
// 获取分销配置
const distributorConfig = ref<Record<string, string>>({});
async function fetchDistributorConfig() {
    distributorConfig.value = await configStore.fetchConfigsByType('distributor');
}


// 获取分销员申请信息
const distributorApplyInfo = ref({
    id: '',
    user_id: '',
    apply_time: '',
    apply_status: '',
    apply_remark: '',
    audit_time: '',
    audit_remark: '',
});
async function fetchDistributorApplyInfo() {
    const res = await getDistributorApplyInfo();
    if (res.code === 10000) {
        distributorApplyInfo.value = res.data;
    }
}

// 获取当前用户分销员申请资格
const distributorApplyPermission = ref({
    allow: false,
    reason: '',
    message: '',
});
async function fetchDistributorApplyPermission() {
    const res = await getDistributorApplyPermission();
    if (res.code === 10000) {
        distributorApplyPermission.value = res.data;
    }
}

// 申请成为分销员
async function applyDistributor() {
    const res = await createDistributorApply();
    if (res.code === 10000) {
        if (res.data.is_distributor) {
            uni.showToast({ title: res.data.notice, icon: 'none' });
        } else {
            uni.showToast({ title: res.data.notice, icon: 'success' });
        }
        goToPage({ url: '/home/pages/user/index/index' })
    } else {
        uni.showToast({ title: res.message, icon: 'none' });
    }
}




onLoad(() => {
    // 获取分销配置
    fetchDistributorConfig();
    // 获取分销员申请信息
    fetchDistributorApplyInfo();
    // 获取当前用户分销员申请资格
    fetchDistributorApplyPermission();
})

</script>




<style lang="scss" scoped>
.distributor-apply {
    min-height: 100vh;
    background-color: #f5f5f5;
    padding: 20rpx;
    
    .header {
        padding: 40rpx 0;
        text-align: center;
        
        .title {
            font-size: 40rpx;
            font-weight: bold;
            color: #333;
            display: block;
        }
        
        .subtitle {
            font-size: 28rpx;
            color: #666;
            margin-top: 16rpx;
            display: block;
        }
    }

    .section-title {
        display: flex;
        align-items: center;
        font-size: 32rpx;
        font-weight: bold;
        color: #333;
        margin-bottom: 24rpx;

        .uni-icons {
            margin-right: 12rpx;
        }
    }
    
    .benefits-section {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .benefits-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20rpx;

            .benefit-item {
                background: #f8f9fa;
                padding: 24rpx;
                border-radius: 12rpx;
                text-align: center;

                .uni-icons {
                    margin-bottom: 12rpx;
                }

                .benefit-title {
                    font-size: 28rpx;
                    font-weight: bold;
                    color: #333;
                    display: block;
                    margin-bottom: 8rpx;
                }

                .benefit-desc {
                    font-size: 24rpx;
                    color: #666;
                }
            }
        }
    }
    
    .apply-container {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);
        
        .condition-content {
            background: #f8f9fa;
            padding: 24rpx;
            border-radius: 12rpx;
            
            .condition-item {
                display: flex;
                align-items: center;
                margin-bottom: 16rpx;
                
                .label {
                    color: #666;
                    font-size: 28rpx;
                    margin-right: 20rpx;
                }
                
                .value {
                    color: #333;
                    font-size: 28rpx;
                    font-weight: 500;
                }
            }
        }

        .apply-steps {
            margin-top: 40rpx;

            .steps-list {
                display: flex;
                justify-content: space-between;
                padding: 20rpx 0;

                .step-item {
                    text-align: center;
                    position: relative;
                    flex: 1;

                    &::after {
                        content: '';
                        position: absolute;
                        top: 20rpx;
                        right: -50%;
                        width: 100%;
                        height: 2rpx;
                        background: #e0e0e0;
                        z-index: 1;
                    }

                    &:last-child::after {
                        display: none;
                    }

                    .step-num {
                        width: 40rpx;
                        height: 40rpx;
                        background: #007AFF;
                        color: #fff;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        margin: 0 auto 12rpx;
                        font-size: 24rpx;
                        position: relative;
                        z-index: 2;
                    }

                    .step-text {
                        font-size: 24rpx;
                        color: #666;
                    }
                }
            }
        }
    }

    .tips-section {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .tips-content {
            .tip-item {
                font-size: 26rpx;
                color: #666;
                line-height: 1.8;
                display: block;
            }
        }
    }


    .status-section {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .status-content {
            background: #f8f9fa;
            padding: 24rpx;
            border-radius: 12rpx;

            .status-item {
                display: flex;
                margin-bottom: 16rpx;
                
                &:last-child {
                    margin-bottom: 0;
                }

                .label {
                    color: #666;
                    font-size: 28rpx;
                    width: 160rpx;
                    flex-shrink: 0;
                }

                .value {
                    color: #333;
                    font-size: 28rpx;
                    flex: 1;


                }
            }
        }
    }

    .permission-section {
        background-color: #fff;
        border-radius: 16rpx;
        padding: 30rpx;
        margin-bottom: 20rpx;
        box-shadow: 0 2rpx 12rpx rgba(0, 0, 0, 0.05);

        .permission-content {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            padding: 30rpx;
            border-radius: 12rpx;
            margin-top: 20rpx;

            .permission-icon {
                width: 80rpx;
                height: 80rpx;
                background: rgba(25, 190, 107, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 24rpx;
            }

            .permission-info {
                flex: 1;

                .permission-title {
                    font-size: 32rpx;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 8rpx;
                    display: block;
                }

                .permission-desc {
                    font-size: 26rpx;
                    color: #666;
                    display: block;
                }
            }
        }
    }
}
</style>