<template>
    <view class="lesson-page">
        <!-- 头部播放区域 -->
        <LessonPlayer :lesson-detail="lessonDetail" />
        
        <!-- 课时信息 -->
        <LessonInfo :lesson-detail="lessonDetail" />
        
        <!-- 课时列表 -->
        <LessonList 
            :lesson-list="lessonList" 
            :current-lesson-id="currentLessonId"
            @lesson-change="handleLessonChange"
        />
        
        <!-- 安全区域 -->
        <view class="safe-area-inset-bottom"></view>
    </view>
</template>

<script setup lang="ts">
import { onLoad } from "@dcloudio/uni-app";
import { ref } from "vue";
import { goToPage } from '@/utils/util';

// 修复导入路径
import { getKmsLessonList, getKmsLessonDetail } from '@/home/platform/kms/api/kms/kmsLesson'

import LessonPlayer from './components/lesson-player.vue';
import LessonInfo from './components/lesson-info.vue';
import LessonList from './components/lesson-list.vue';

const loading = ref(false);
const lessonDetail = ref<any>({});
const lessonList = ref<any[]>([]);
const currentLessonId = ref<number>(0);
const goodsId = ref<number>(0);

// 加载课程列表
const loadLessonList = async (goodsId: number) => {
    if (!goodsId) return;
    
    loading.value = true;
    try {
        const res = await getKmsLessonList({ goods_id: goodsId });
        lessonList.value = res.data || [];
        
        // 如果没有指定课程ID，默认显示第一节课
        if (!currentLessonId.value && lessonList.value.length > 0) {
            currentLessonId.value = lessonList.value[0].id;
            await loadLessonDetail(currentLessonId.value);
        }
    } catch (error) {
        uni.showToast({
            title: '加载课程列表失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

// 加载课程详情
const loadLessonDetail = async (lessonId: number) => {
    if (!lessonId) return;
    
    loading.value = true;
    try {
        const res = await getKmsLessonDetail(lessonId);
        lessonDetail.value = res.data || {};
    } catch (error) {
        uni.showToast({
            title: '加载课程详情失败',
            icon: 'none'
        });
    } finally {
        loading.value = false;
    }
};

// 处理课时切换
const handleLessonChange = async (lessonId: number) => {
    currentLessonId.value = lessonId;

    await loadLessonDetail(lessonId);

    // 判断是否可以观看
    if (!lessonDetail.value.is_can_watch) {

        
        uni.showModal({
            title: '提示',
            content: '请先购买课程',
            showCancel: true,
            success: (res) => {
                if (res.confirm) {
                    // 跳转商品详情页
                    goToPage({ url: `/home/platform/kms/pages/goods/detail/index?goods_id=${goodsId.value}` })
                }
            }
        });
    }
};

onLoad((options) => {
    // 获取商品ID和课程ID
    goodsId.value = options?.goods_id ? Number(options.goods_id) : 0;
    currentLessonId.value = options?.id ? Number(options.id) : 0;
    
    if (goodsId.value) {
        loadLessonList(goodsId.value);
        
        // 如果指定了课程ID，直接加载课程详情
        if (currentLessonId.value) {
            loadLessonDetail(currentLessonId.value);
        }
    }
});
</script>

<style lang="scss" scoped>
.lesson-page {
    min-height: 100vh;
    background-color: #f8f9fa;
    padding-bottom: env(safe-area-inset-bottom);
}

.safe-area-inset-bottom {
    height: 50rpx;
}
</style>



