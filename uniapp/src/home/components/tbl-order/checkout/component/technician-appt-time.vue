<template>
    <view class="appointment-container">
        <view class="appointment-field" @click="openPopup">
            <view class="field-label">
                <uni-icons type="calendar" size="20" color="#FF4C4C"></uni-icons>
                <text class="label-text">预约时间</text>
            </view>
            <view class="field-value">
                <text v-if="hasAppointmentTime">{{ formattedAppointmentTime }}</text>
                <text v-else class="placeholder">请选择预约时间</text>
                <uni-icons type="right" size="16" color="#999"></uni-icons>
            </view>
        </view>

        <!-- 弹窗 -->
        <uni-popup ref="popup" type="bottom">
            <view class="popup-content">
                <view class="popup-header">
                    <text class="popup-title">选择预约时间</text>
                    <view class="popup-close" @click="closePopup">
                        <uni-icons type="closeempty" size="24" color="#999"></uni-icons>
                    </view>
                </view>
                <!-- 日期选择 -->
                <view class="date-section">
                    <scroll-view scroll-x class="date-scroll">
                        <view v-for="(date, index) in availableDates" :key="index" class="date-item"
                            :class="{ active: selectedDate === date.value }" @click="selectDate(date.value)">
                            <text class="date-day">{{ date.day }}</text>
                            <text class="date-name">{{ date.name }}</text>
                        </view>
                    </scroll-view>
                </view>

                <!-- 状态说明 -->
                <view class="status-legend">
                    <view class="legend-item">
                        <view class="legend-color available"></view>
                        <text class="legend-text">可预约</text>
                    </view>
                    <view class="legend-item">
                        <view class="legend-color unavailable"></view>
                        <text class="legend-text">不可预约</text>
                    </view>
                </view>
                <scroll-view scroll-y>
                    <!-- 时间选择区域 -->
                    <view class="time-container">
                        <view class="time-slots">
                            <view v-for="(time, index) in availableTimes" :key="index" class="time-slot" :class="{
                                active: selectedTime === time.value,
                                unavailable: isTimeUnavailable(time.value)
                            }" @click="selectTime(time.value, isTimeUnavailable(time.value))">
                                {{ time.label }}
                            </view>
                        </view>
                    </view>
                </scroll-view>
                <!-- 确认按钮 -->
                <view class="confirm-section">
                    <button class="confirm-button" :disabled="!canConfirm" @click="confirmSelection">
                        确认
                    </button>
                </view>
            </view>
        </uni-popup>
    </view>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue';

// 预设的时间类型常量
const NORMAL_TIME = 'normal';
const FULL_DAY_TIME = 'fullDay';
const WORK_TIME = 'work';
const CUSTOM_TIME = 'custom';

// 时间类型
type TimeType = 'normal' | 'fullDay' | 'work' | 'custom';

// 时间点接口
interface TimePoint {
    time: string; // 格式为 "HH:MM"
}

// 简化后的接口，不再区分时间段
interface TimeList {
    times: TimePoint[];
}

// 生成半小时间隔的时间点
const generateTimePoints = (startHour: number, endHour: number): TimePoint[] => {
    const points: TimePoint[] = [];

    for (let hour = startHour; hour < endHour; hour++) {
        const hourStr = hour < 10 ? `0${hour}` : `${hour}`;

        // 整点
        points.push({ time: `${hourStr}:00` });

        // 半点
        points.push({ time: `${hourStr}:30` });
    }

    // 添加结束小时的整点（如果需要，且不是24点）
    if (endHour < 24 && endHour > startHour) {
        const hourStr = endHour < 10 ? `0${endHour}` : `${endHour}`;
        points.push({ time: `${hourStr}:00` });
    }

    return points;
};

// 预设时间范围
const PRESET_TIME_RANGES: Record<TimeType, TimeList> = {
    // 正常作息时间 (9:00-21:00)
    [NORMAL_TIME]: {
        times: generateTimePoints(9, 21)
    },
    // 全天24小时
    [FULL_DAY_TIME]: {
        times: generateTimePoints(0, 24)
    },
    // 工作时间 (9:00-18:00)
    [WORK_TIME]: {
        times: generateTimePoints(9, 18)
    },
    // 自定义时间 (默认为空，需要通过timePoints属性提供)
    [CUSTOM_TIME]: {
        times: []
    }
};

const props = defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    // 时间类型
    timeType: {
        type: String as () => TimeType,
        default: FULL_DAY_TIME,
        validator: (value: string) => [NORMAL_TIME, FULL_DAY_TIME, WORK_TIME, CUSTOM_TIME].includes(value)
    },
    // 自定义时间点 (当timeType为CUSTOM时使用)
    timePoints: {
        type: Array as () => TimePoint[],
        default: () => []
    },
    // 未来可预约的天数
    daysInAdvance: {
        type: Number,
        default: 7
    },
    // 不可用时间，格式为 {'2023-11-01 09:00': false}
    unavailableTimes: {
        type: Object as () => Record<string, boolean>,
        default: () => ({})
    }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

// 获取当前使用的时间范围
const currentTimePoints = computed<TimePoint[]>(() => {
    // 如果是自定义类型，直接使用timePoints属性
    if (props.timeType === CUSTOM_TIME) {
        return props.timePoints;
    }

    // 否则使用预设的时间范围
    return PRESET_TIME_RANGES[props.timeType].times;
});

// 弹窗引用
const popup = ref<any>(null);

// 选中的日期和时间
const selectedDate = ref('');
const selectedTime = ref('');

// 解析已选择的预约时间
const parseAppointmentTime = () => {
    if (!props.modelValue) return { date: '', time: '' };

    const parts = props.modelValue.split(' ');
    if (parts.length === 2) {
        return {
            date: parts[0],
            time: parts[1]
        };
    }

    return { date: '', time: '' };
};

// 日期和时间是否已选择
const hasAppointmentTime = computed(() => {
    return !!props.modelValue;
});

// 格式化显示的预约时间
const formattedAppointmentTime = computed(() => {
    if (!hasAppointmentTime.value) return '';

    const { date, time } = parseAppointmentTime();
    if (!date || !time) return '';

    const dateObj = new Date(date);
    const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];
    const weekday = weekdays[dateObj.getDay()];

    const month = dateObj.getMonth() + 1;
    const day = dateObj.getDate();
    const monthStr = month < 10 ? `0${month}` : month;
    const dayStr = day < 10 ? `0${day}` : day;

    return `${monthStr}月${dayStr}日 ${weekday} ${time}`;
});

// 是否可以确认选择
const canConfirm = computed(() => {
    return selectedDate.value && selectedTime.value;
});

// 可选日期（今天和未来指定天数）
const availableDates = computed(() => {
    const dates = [];
    const weekdays = ['周日', '周一', '周二', '周三', '周四', '周五', '周六'];

    const today = new Date();

    for (let i = 0; i < props.daysInAdvance; i++) {
        const date = new Date();
        date.setDate(today.getDate() + i);

        const day = date.getDate();
        const month = date.getMonth() + 1;
        const dayStr = day < 10 ? `0${day}` : day;
        const monthStr = month < 10 ? `0${month}` : month;

        const dateValue = `${date.getFullYear()}-${monthStr}-${dayStr}`;
        const weekday = weekdays[date.getDay()];

        let name = '';
        if (i === 0) {
            name = '今天';
        } else if (i === 1) {
            name = '明天';
        } else {
            name = weekday;
        }

        dates.push({
            day: dayStr,
            name: name,
            value: dateValue
        });
    }

    return dates;
});

// 检查时间是否不可用
const isTimeUnavailable = (time: string) => {
    if (!selectedDate.value) return false;

    const fullTime = `${selectedDate.value} ${time}`;
    return props.unavailableTimes[fullTime] === false;
};

// 可选时间
const availableTimes = computed(() => {
    const now = new Date();
    const isToday = selectedDate.value === availableDates.value[0].value;
    const currentHour = now.getHours();
    const currentMinute = now.getMinutes();

    // 获取所有时间点
    const allTimePoints = [...currentTimePoints.value];

    // 过滤可用的时间点
    let availablePoints = allTimePoints;
    if (isToday) {
        availablePoints = allTimePoints.filter(point => {
            const [hour, minute] = point.time.split(':').map(Number);
            // 如果时间晚于当前时间，则可用
            return (hour > currentHour) ||
                (hour === currentHour && minute > currentMinute);
        });
    }

    // 格式化时间点
    return availablePoints.map(point => ({
        label: point.time,
        value: point.time
    }));
});

// 打开弹窗
const openPopup = () => {
    // 初始化选中状态
    if (hasAppointmentTime.value) {
        const { date, time } = parseAppointmentTime();
        selectedDate.value = date;
        selectedTime.value = time;
    } else {
        // 默认选中第一个可用日期
        selectedDate.value = availableDates.value[0].value;

        // 默认不选中时间
        selectedTime.value = '';
    }

    popup.value?.open('bottom');
};

// 关闭弹窗
const closePopup = () => {
    popup.value?.close();
};

// 选择日期
const selectDate = (date: string) => {
    selectedDate.value = date;

    // 重置时间选择
    selectedTime.value = '';
};

// 选择时间
const selectTime = (time: string, isUnavailable: boolean) => {
    // 如果时间不可用，则不选择
    if (isUnavailable) return;

    selectedTime.value = time;
};

// 确认选择
const confirmSelection = () => {
    if (!canConfirm.value) return;

    // 合并日期和时间为一个字符串
    const appointmentTime = `${selectedDate.value} ${selectedTime.value}`;

    emit('update:modelValue', appointmentTime);
    emit('confirm', appointmentTime);

    closePopup();
};
</script>

<style lang="scss" scoped>
.appointment-container {
    background-color: #ffffff;
    margin-bottom: 20rpx;
    border-radius: 12rpx;
    overflow: hidden;
    box-shadow: 0 2rpx 10rpx rgba(0, 0, 0, 0.05);

    .appointment-field {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30rpx 20rpx;

        &:active {
            background-color: #f9f9f9;
        }

        .field-label {
            display: flex;
            align-items: center;

            .label-text {
                margin-left: 10rpx;
                font-size: 28rpx;
                color: #333;
                font-weight: 500;
            }
        }

        .field-value {
            display: flex;
            align-items: center;

            text {
                font-size: 26rpx;
                color: #333;
                margin-right: 10rpx;
            }

            .placeholder {
                color: #999;
            }
        }
    }
}

.popup-content {
    background-color: #fff;
    border-top-left-radius: 24rpx;
    border-top-right-radius: 24rpx;
    min-height: 60vh;

    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 30rpx;
        border-bottom: 1rpx solid #f5f5f5;

        .popup-title {
            font-size: 32rpx;
            color: #333;
            font-weight: 500;
        }

        .popup-close {
            padding: 10rpx;
        }
    }

    .date-section {
        padding: 20rpx 30rpx;
        border-bottom: 10rpx solid #f5f5f5;
        margin-bottom: 20rpx;

        .date-scroll {
            white-space: nowrap;
            padding: 10rpx 0;

            .date-item {
                display: inline-block;
                width: 120rpx;
                height: 120rpx;
                margin-right: 20rpx;
                border-radius: 12rpx;
                background-color: #f8f8f8;
                text-align: center;
                padding: 20rpx 0;
                box-sizing: border-box;

                &.active {
                    background-color: #FF4C4C;

                    .date-day,
                    .date-name {
                        color: #fff;
                    }
                }

                .date-day {
                    display: block;
                    font-size: 32rpx;
                    font-weight: bold;
                    color: #333;
                    margin-bottom: 10rpx;
                }

                .date-name {
                    display: block;
                    font-size: 24rpx;
                    color: #666;
                }
            }
        }
    }

    .status-legend {
        display: flex;
        padding: 0 30rpx 20rpx;
        border-bottom: 1px solid #f5f5f5;
        margin-bottom: 20rpx;

        .legend-item {
            display: flex;
            align-items: center;
            margin-right: 30rpx;

            .legend-color {
                width: 20rpx;
                height: 20rpx;
                border-radius: 4rpx;
                margin-right: 10rpx;

                &.available {
                    background-color: #f8f8f8;
                    border: 1px solid #e0e0e0;
                }

                &.unavailable {
                    background-color: #f0f0f0;
                    position: relative;

                    &::after {
                        content: '';
                        position: absolute;
                        top: 8rpx;
                        left: 0;
                        width: 100%;
                        height: 2rpx;
                        background-color: #ccc;
                        transform: rotate(45deg);
                    }
                }
            }

            .legend-text {
                font-size: 24rpx;
                color: #999;
            }
        }
    }





    .time-container {
        padding: 20rpx 30rpx;
        height: 650rpx;
    }

    .time-slots {
        display: flex;
        flex-wrap: wrap;
        margin: 0 -10rpx;

        .time-slot {
            width: calc(25% - 20rpx);
            margin: 10rpx;
            height: 70rpx;
            line-height: 70rpx;
            text-align: center;
            background-color: #f8f8f8;
            border-radius: 8rpx;
            font-size: 28rpx;
            color: #333;

            &.unavailable {
                background-color: #f0f0f0;
                color: #ccc;
                position: relative;

                &::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 0;
                    width: 100%;
                    height: 2rpx;
                    background-color: #ccc;
                    transform: rotate(45deg);
                }
            }

            &.active {
                background-color: #FF4C4C;
                color: #fff;
            }
        }
    }



    .confirm-section {

        padding: 20rpx 30rpx;
        border-top: 1rpx solid #f5f5f5;

        .confirm-button {
            background-color: #FF4C4C;
            color: #fff;
            border-radius: 40rpx;
            font-size: 28rpx;
            height: 80rpx;
            line-height: 80rpx;

            &:disabled {
                background-color: #cccccc;
            }
        }
    }
}
</style>