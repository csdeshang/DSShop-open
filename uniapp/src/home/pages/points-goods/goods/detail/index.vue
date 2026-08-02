<template>
    <view class="points-goods-detail-page">
        <!-- 轮播图 -->
        <PointsGoodsSwiper :goods-detail="goodsDetail" />

        <!-- 商品信息 -->
        <PointsGoodsInfo :goods-detail="goodsDetail" />

        <!-- 商品评价 -->
        <PointsGoodsEvaluate :goods-detail="goodsDetail" />
        
        <!-- 商品详情 -->
        <PointsGoodsBody :goods-detail="goodsDetail" />


        <!-- 底部操作栏 -->
        <PointsGoodsFooter :goods-detail="goodsDetail" @exchange="handleExchange" />
    </view>
</template>

<script lang="ts" setup>
import { getPointsGoodsDetail } from '@/home/api/points-goods/pointsGoods'
import { onLoad } from '@dcloudio/uni-app'
import { ref } from 'vue'

// 积分商品详情组件
import PointsGoodsSwiper from './component/points-goods-swiper.vue'
import PointsGoodsInfo from './component/points-goods-info.vue'
import PointsGoodsBody from './component/points-goods-body.vue'
import PointsGoodsEvaluate from './component/points-goods-evaluate.vue'
import PointsGoodsFooter from './component/points-goods-footer.vue'

const loading = ref(false)
const goodsDetail = ref<any>({})

// 加载积分商品详情
const loadPointsGoodsDetail = async (id: number) => {
    loading.value = true
    try {
        const res = await getPointsGoodsDetail(id)
        goodsDetail.value = res.data
    } catch (error) {
        console.error('加载积分商品详情失败:', error)
        uni.showToast({
            title: '加载失败',
            icon: 'none'
        })
    } finally {
        loading.value = false
    }
}

// 处理积分兑换
const handleExchange = () => {
    if (!goodsDetail.value.id) {
        uni.showToast({
            title: '商品信息错误',
            icon: 'none'
        })
        return
    }
    
    uni.navigateTo({
        url: `/home/pages/user/points-goods/order/checkout?id=${goodsDetail.value.id}`
    })
}

onLoad((options: any) => {
    const id = options?.id ? Number(options.id) : undefined
    if (id) {
        loadPointsGoodsDetail(id)
    } else {
        uni.showToast({
            title: '商品ID不能为空',
            icon: 'none'
        })
    }
})
</script>

<style lang="scss" scoped>
.points-goods-detail-page {
    position: relative;
    min-height: 100vh;
    background-color: #f6f7f9;
    padding-bottom: 120rpx; // 为底部操作栏留出空间
}
</style>
