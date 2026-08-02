<template>
  <view>
    <view v-if="loading" class="loading-wrap"></view>
    <view v-if="!loading && editablePage && editablePage.page_config">
      <view
        v-for="(config, index) in editablePage.page_config.elementsConfig"
        :key="config.id || index"
      >
        <DiyList :config="config" />
      </view>
    </view>
  </view>
</template>

<script setup>
import { onLoad } from '@dcloudio/uni-app'
import { useEditable } from '@/enums/useEditable'
import DiyList from './editable/diy-list.vue'

const { loading, loadConfig, editablePage } = useEditable()

onLoad((options) => {
  const id = options.id
  if (!id) {
    uni.showToast({ title: '缺少页面参数', icon: 'none' })
    return
  }
  loadConfig({
    id: Number(id),
    device_type: 'mobile'
  }).then(() => {
    if (editablePage.value?.page_config?.globalSettings?.title) {
      uni.setNavigationBarTitle({
        title: editablePage.value.page_config.globalSettings.title
      })
    }
  }).catch(() => {
    uni.showToast({ title: '页面加载失败', icon: 'none' })
  })
})
</script>

<style lang="scss" scoped>
.loading-wrap {
  min-height: 200px;
}
</style>
