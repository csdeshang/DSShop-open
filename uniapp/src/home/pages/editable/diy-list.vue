<template>
  <view>
    <!-- 可视化组件列表 -->
    <!-- 富文本 -->
    <template v-if="config.diyName == 'diy-rich-text'">
        <DiyRichText :config="config" />
    </template>

    <!-- 行空白 -->
    <template v-if="config.diyName == 'diy-row-blank'">
        <DiyRowBlank :config="config" />
    </template>

    <!-- 行线条 -->
    <template v-if="config.diyName == 'diy-row-line'">
        <DiyRowLine :config="config" />
    </template>

    <!-- 行标题 -->
    <template v-if="config.diyName == 'diy-row-title'">
        <DiyRowTitle :config="config" />
    </template>

    <!-- 图片魔方 -->
    <template v-if="config.diyName == 'diy-image-cube'">
        <DiyImageCube :config="config" />
    </template>

    <!-- 搜索 -->
    <template v-if="config.diyName == 'diy-search'">
        <DiySearch :config="config" />
    </template>

    <!-- 搜索轮播图 -->
    <template v-if="config.diyName == 'diy-search-swiper'">
        <DiySearchSwiper :config="config" />
    </template>

    <!-- 导航栏 -->
    <template v-if="config.diyName == 'diy-nav-bar'">
        <DiyNavBar :config="config" />
    </template>

    <!-- 轮播图 -->
    <template v-if="config.diyName == 'diy-swiper'">
        <DiySwiper :config="config" />
    </template>

    <!-- 新闻列表 -->
    <template v-if="config.diyName == 'diy-news'">
        <DiyNews :config="config" />
    </template>

    <!-- 商品列表 -->
    <template v-if="config.diyName == 'diy-goods-list'">
        <DiyGoodsList :config="config" />
    </template>

    <!-- 店铺优惠券 -->
    <template v-if="config.diyName == 'diy-store-coupon'">
        <DiyStoreCoupon :config="config" />
    </template>

    <!-- 系统文章 -->
    <template v-if="config.diyName == 'diy-sys-article'">
        <DiySysArticle :config="config" />
    </template>

    <!-- 师傅列表 -->
    <template v-if="config.diyName == 'diy-technician-list'">
        <DiyTechnicianList :config="config" />
    </template>
  </view>
</template>



<script setup>
import DiyRichText from './components/diy-rich-text.vue';
import DiyRowBlank from './components/diy-row-blank.vue';
import DiyRowLine from './components/diy-row-line.vue';
import DiyRowTitle from './components/diy-row-title.vue';
import DiyNavBar from './components/diy-nav-bar.vue';
import DiySearch from './components/diy-search.vue';
import DiySwiper from './components/diy-swiper.vue';
import DiyNews from './components/diy-news.vue';
import DiyGoodsList from './components/diy-goods-list.vue';
import DiyStoreCoupon from './components/diy-store-coupon.vue';
import DiySysArticle from './components/diy-sys-article.vue';
import DiyImageCube from './components/diy-image-cube.vue';
import DiySearchSwiper from './components/diy-search-swiper.vue';
import DiyTechnicianList from './components/diy-technician-list.vue';

const props = defineProps({
    config: {
        type: Object,
        required: true
    }
});





</script>
