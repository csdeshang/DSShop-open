<template>
    <view class="location-reporter">
        <!-- 调试信息 -->
        <view v-if="DEBUG_MODE" style="display:none;">
            <text>上报状态: {{isRunning ? '运行中' : '已停止'}}</text>
            <text>上报次数: {{reportCount}}</text>
            <text>最后上报: {{lastReportTime ? new Date(lastReportTime).toLocaleString() : '未上报'}}</text>
        </view>
    </view>


</template>

<script setup lang="ts">
import { ref, watch, onMounted, onUnmounted, computed, nextTick } from 'vue';
import { useLbsStore } from '@/stores/modules/lbs';
import useUserStore from '@/stores/user';
import { onShow, onHide } from '@dcloudio/uni-app';
import { reportRiderTrack } from '@/rider/api/riderTrack';

// 内部常量：位置上报功能是否启用
const LOCATION_REPORTING_ENABLED = true; // 后续可根据需要改为false
// 内部常量：是否开启调试模式
const DEBUG_MODE = true; // 开发时可设置为true，生产环境设为false


// 定义属性
const props = defineProps({
    // 上报时间间隔(毫秒)
    interval: {
        type: Number,
        default: 10 * 60 * 1000 // 默认10分钟
    },
    // 是否在后台继续上报
    reportInBackground: {
        type: Boolean,
        default: true
    }
});

// 定义事件
const emit = defineEmits(['report-success', 'report-error', 'status-change']);

const lbsStore = useLbsStore();
const userStore = useUserStore();

// 状态变量
const isRunning = ref(false);
const timer = ref<ReturnType<typeof setTimeout> | null>(null);
const lastReportTime = ref(0);
const reportCount = ref(0);
const inBackground = ref(false);

// 计算骑手是否在线
const isRiderOnline = computed(() => {
    return userStore.userInfo?.rider?.status == '1';
});

// 计算是否应该启用位置上报
const shouldReportLocation = computed(() => {
    return LOCATION_REPORTING_ENABLED && isRiderOnline.value;
});

// 日志记录
const log = (message: string) => {
    if (DEBUG_MODE) {
        console.log(`[LocationReporter ${new Date().toLocaleTimeString()}] ${message}`);
    }
};

// 启动位置上报服务
const startReporting = () => {
    if (isRunning.value) {
        log('位置上报服务已在运行中');
        return;
    }

    if (!shouldReportLocation.value) {
        log('骑手未上线或位置上报功能已禁用，不启动位置上报');
        return;
    }

    log('启动位置上报服务');
    isRunning.value = true;
    emit('status-change', true);

    // 清除可能存在的定时器
    clearTimerIfExists();
    
    // 立即执行一次上报，然后设置递归的定时器
    executeReportCycle();
};

// 执行上报周期
const executeReportCycle = async () => {
    if (!isRunning.value) {
        log('上报服务已停止，取消执行上报周期');
        return;
    }
    
    try {
        // 执行上报
        await reportLocation();
    } catch (error) {
        log(`上报周期执行出错: ${error}`);
    }
    
    // 如果仍在运行状态，设置下一次定时
    if (isRunning.value) {
        clearTimerIfExists(); // 确保没有重复的定时器
        
        log(`设置下一次上报时间: ${new Date(Date.now() + props.interval).toLocaleTimeString()}`);
        timer.value = setTimeout(() => {
            executeReportCycle();
        }, props.interval);
    }
};

// 清除已存在的定时器
const clearTimerIfExists = () => {
    if (timer.value) {
        clearTimeout(timer.value);
        timer.value = null;
    }
};

// 停止位置上报服务
const stopReporting = () => {
    if (!isRunning.value) {
        log('位置上报服务未在运行');
        return;
    }

    log('停止位置上报服务');
    clearTimerIfExists();
    
    isRunning.value = false;
    emit('status-change', false);
};

// 上报位置信息
const reportLocation = async () => {
    try {
        log('开始上报位置');

        // 获取骑手信息，检查是否已登录和是否为骑手
        const userInfo = userStore.userInfo;
        if (!userInfo || !userInfo.rider || !userInfo.rider.id) {
            log('用户未登录或非骑手账号，取消上报');
            stopReporting(); // 如果用户未登录，停止服务
            return false;
        }

        // 检查骑手是否在线
        if (!isRiderOnline.value) {
            log('骑手当前未上线，取消上报');
            stopReporting(); // 如果骑手未上线，停止服务
            return false;
        }

        // 如果在后台且不允许后台上报，则跳过
        if (inBackground.value && !props.reportInBackground) {
            log('App在后台运行，且设置为不在后台上报，取消上报');
            return false;
        }

        log('获取位置信息中...');
        try {
            // 获取当前位置信息 (强制刷新)
            // await lbsStore.getLocation(false);
            // 获取位置信息，并设置过期时间为1分钟 (防止刷新调用)
            await lbsStore.getLocation(true, 1 * 60);
        } catch (locError) {
            log(`获取位置出错: ${locError}`);
            emit('report-error', { error: '获取位置失败' });
            // 位置获取失败不停止整个上报服务，返回false表示本次上报失败
            return false;
        }

        const { latitude, longitude, address } = lbsStore;
        if (!latitude || !longitude) {
            log('获取位置失败，取消当次上报');
            emit('report-error', { error: '获取位置失败' });
            // 位置获取失败不停止整个上报服务，返回false表示本次上报失败
            return false;
        }

        log(`获取到位置: ${latitude}, ${longitude}, 开始上报...`);
        try {
            // 上报位置到服务器
            const response = await reportRiderTrack({
                latitude,
                longitude,
                address,
            });

            // 更新上报时间和计数
            lastReportTime.value = Date.now();
            reportCount.value++;

            if (response.code === 10000) {
                log(`位置上报成功，已累计上报${reportCount.value}次`);
                emit('report-success', { 
                    latitude, 
                    longitude, 
                    time: lastReportTime.value,
                    count: reportCount.value 
                });
                return true;
            } else {
                log(`位置上报失败: ${response.message || '未知原因'}`);
                emit('report-error', { error: response.message || '上报失败' });
                return false;
            }
        } catch (apiError: any) {
            log(`API调用出错: ${apiError?.message || String(apiError)}`);
            emit('report-error', { error: apiError?.message || '未知错误' });
            return false;
        }
    } catch (error: any) {
        log(`位置上报出错: ${error?.message || String(error)}`);
        emit('report-error', { error: error?.message || '未知错误' });
        return false;
    }
};

// 监听骑手在线状态变化
watch(isRiderOnline, (newValue) => {
    log(`骑手在线状态变化: ${newValue ? '在线' : '离线'}`);
    if (newValue && LOCATION_REPORTING_ENABLED) {
        if (!isRunning.value) {
            log('骑手已上线，启动位置上报');
            startReporting();
        }
    } else {
        if (isRunning.value) {
            log('骑手已下线，停止位置上报');
            stopReporting();
        }
    }
});

// 监听App前后台切换
onShow(() => {
    inBackground.value = false;
    log('App进入前台');

    // 如果需要上报且服务未运行，重新启动
    if (shouldReportLocation.value) {
        if (!isRunning.value) {
            log('App进入前台，需要启动位置上报');
            nextTick(() => {
                startReporting();
            });
        } else {
            log('App进入前台，位置上报服务已在运行');
        }
    } else {
        log('骑手未上线或位置上报功能已禁用，不启动位置上报');
    }
});

onHide(() => {
    inBackground.value = true;
    log('App进入后台');

    // 如果不允许后台上报，则停止服务
    if (!props.reportInBackground && isRunning.value) {
        log('App进入后台且不允许后台上报，停止位置上报');
        stopReporting();
    } else if (isRunning.value) {
        log('App进入后台，允许后台上报，位置上报服务继续运行');
    }
});

// 组件挂载和卸载
onMounted(async () => {
    log('位置上报组件开始挂载');
    try {
        // 先获取用户信息
        await userStore.getUserInfoWithRoles();
        log('用户信息获取成功');
        
        // 确保DOM已更新
        nextTick(() => {
            if (shouldReportLocation.value) {
                log('骑手已上线，组件挂载后启动位置上报');
                startReporting();
            } else {
                if (!LOCATION_REPORTING_ENABLED) {
                    log('位置上报功能已禁用，不启动位置上报');
                } else if (!isRiderOnline.value) {
                    log('骑手未上线，不启动位置上报');
                } else {
                    log('未能启动位置上报，检查其他条件');
                }
            }
        });
    } catch (error) {
        log(`组件挂载获取用户信息出错: ${error}`);
    }
});

onUnmounted(() => {
    log('位置上报组件准备卸载');
    // 停止所有定时器和上报服务
    if (isRunning.value) {
        log('组件卸载，停止位置上报服务');
        stopReporting();
    }
    // 确保所有定时器被清除
    clearTimerIfExists();
    log('位置上报组件已完全卸载');
});
</script>

<style lang="scss" scoped>
.location-reporter {
    /* 不需要任何样式 */
}
</style>

<script lang="ts">
export default {
    name: 'LocationReporter'
}
</script>
