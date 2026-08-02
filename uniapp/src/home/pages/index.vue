<template>
  <view class="content">
    <!-- 系统首页-->
    <EditablePage type="home" platform="system" />
    <DsBottomNav configKey="website:h5_system_bottom_nav" />
  </view>


</template>

<script setup lang="ts">

import EditablePage from '@/home/pages/editable/editable-page.vue'; // 引入公共组件
import DsBottomNav from '@/components/ds-bottom-nav/index.vue' // 引入 Footer 组件

</script>


