<template>
    <uni-popup ref="popupRef" type="bottom">
        <view class="area-picker">
            <view class="ds-popup-header">
                <view class="cancel" @click="close">取消</view>
                <view class="title">请选择地区</view>
                <view class="confirm" @click="confirm">确定</view>
            </view>

            <view class="selected-area">
                <view class="item" v-for="(item, index) in selectedAreas" :key="index">
                    <view class="name" @click="fetchSysAreaList(item.pid)">{{ item.name }}</view>
                    <uni-icons type="right" size="12" color="#4e545d"></uni-icons>
                </view>
            </view>

            <scroll-view scroll-y="true" class="area-list">
                <view class="item" v-for="item in areaList" :key="item.id" @click="selectArea(item)">
                    <view class="area-name">{{ item.name }}</view>
                </view>
            </scroll-view>

        </view>
    </uni-popup>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { getSysAreaList } from '@/api/system/sysArea';

const props = defineProps({
    address: {
        type: Object,
        default: () => ({})
    }
});

// 定义 AreaItem 接口
interface AreaItem {
    id: number; // 或 string，取决于您的实际数据类型
    name: string;
    deep: number; // 深度索引
}

const popupRef = ref();
const areaList = ref();
const selected_area_id = ref();

const selectedAreas = reactive<{ [key: number]: AreaItem }>({});

const fetchSysAreaList = (pid: number) => {
    getSysAreaList({ pid: pid }).then((res) => {
        if (res.data.length > 0) {
            areaList.value = res.data;
        } else {
            //没有下级自动确认
            confirm();
        }
    });
};

const selectArea = (item: any) => {
    selected_area_id.value = item.id;
    // 根据深度索引存储选中的地区
    selectedAreas[item.deep] = item;
    // 获取下级地区
    fetchSysAreaList(item.id)
};

// 初始化选中的地区
const initSelectedAreas = () => {
    // 在打开时重新初始化 selectedAreas
    Object.keys(selectedAreas).forEach(key => {
        delete selectedAreas[key]; // 清空对象中的所有属性
    });
    //也可通过接口 通过 area_id 获取父级数组 赋值
}

const loadSysAreaData = () => {
    fetchSysAreaList(0)
};

const emit = defineEmits(['confirm']);
// 确认选择
const confirm = () => {
    const area = {
        province_id: selectedAreas[1]?.id || null,
        city_id: selectedAreas[2]?.id || null,
        district_id: selectedAreas[3]?.id || null,
        area_id: selected_area_id.value || null,
        area_info: Object.values(selectedAreas).map(item => item.name).join(' '),
    }
    emit('confirm', area);
    close();
};

// 打开弹窗
const open = () => {
    popupRef.value.open();
    // initSelectedAreas();
    loadSysAreaData();
};

// 关闭弹窗
const close = () => {
    popupRef.value.close();
};

defineExpose({
    open,
    close
});
</script>


<style lang="scss" scoped>
.area-picker {
    background-color: #fff;
}



.selected-area {
    height: 50px;
    line-height: 50px;
    padding: 0 10px;
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    border-bottom: 1px solid #f0f2f5;

    .item {
        display: flex;

        font-size: $ds-font-sm;

        .name {
            padding: 0 10px;
        }
    }

}

.area-list {
    height: 400px;

    .item {
        padding: 0 25px;
        line-height: 36px;
        font-size: $ds-font-sm;
        color: #4e545d;
    }
}
</style>
