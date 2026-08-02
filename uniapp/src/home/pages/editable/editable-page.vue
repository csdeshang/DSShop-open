<template>
  <view>

    <view v-if="loading">

    </view>



    <view v-if="!loading && editablePage && editablePage.page_config">
      <view v-for="config in editablePage.page_config.elementsConfig" :key="config.id">
        <!-- 组件列表 -->
        <DiyList :config="config" />
      </view>
    </view>



  </view>
</template>

<script setup>
import { ref } from 'vue';
import { useEditable } from '@/enums/useEditable'; // 引入 useEditable
import DiyList from './diy-list.vue';
import { onLoad } from '@dcloudio/uni-app';

// 接收 props
const props = defineProps({
  type: {
    type: String,
    required: true
  },
  platform: {
    type: String,
    required: true
  },
  deviceType: {
    type: String,
    default: 'mobile' // 默认手机端
  }
});

// 响应式变量
const id = ref('');
const { loading, loadConfig, editablePage } = useEditable(); // 使用 useEditable


onLoad((options) => {
  id.value = options.id; // 从页面选项中获取 ID

  // 加载页面配置，只有在 ID 存在时才传递
  const params = { 
    type: props.type, 
    platform: props.platform,
    device_type: props.deviceType || 'mobile' // 传递设备类型，默认手机端
  };
  if (id.value) {
    params.id = Number(id.value); // 只有在 ID 存在时才添加
  }
  loadConfig(params); // 加载配置
});



</script>

<style lang="scss" scoped>
/* 添加样式 */
</style>