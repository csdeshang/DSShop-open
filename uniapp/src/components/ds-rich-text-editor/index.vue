<template>
  <view class="rich-text-editor">
    <!-- 编辑器 -->
    <editor
      id="editor"
      class="editor"
      :style="{ minHeight: height }"
      @input="handleInput"
      @ready="handleReady"
      :placeholder="placeholder"
      :show-img-size="true"
      :show-img-toolbar="true"
      :show-img-resize="true"
    />

    <!-- 工具栏 -->
    <view class="editor-toolbar">
      <button class="toolbar-btn" @click="insertImage">
        <uni-icons type="image" size="16" color="#fff"></uni-icons>
        <text>插入图片</text>
      </button>
    </view>

    <!-- 文件选择弹窗 -->
    <ds-attachment-index 
      ref="attachmentRef" 
      @select="handleFileSelected" 
      :type="fileType"
    />
  </view>
</template>

<script setup lang="ts">
import { ref, watch, computed, getCurrentInstance } from 'vue'
import { formatFileUrl } from '@/utils/util'
import DsAttachmentIndex from '@/components/ds-attachment/manage/index.vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '请输入内容'
  },
  height: {
    type: [String, Number],
    default: 800
  }
})

const emit = defineEmits(['update:modelValue'])

const content = ref(props.modelValue)
const editorContext = ref<any>(null)
const attachmentRef = ref()
const fileType = ref<'image'>('image')
const instance = getCurrentInstance()
const isEditorReady = ref(false)

// 计算高度
const height = computed(() => {
  if (typeof props.height === 'number') {
    return `${props.height}rpx`
  }
  return props.height
})

// 设置编辑器内容的辅助方法
const setEditorContent = (html: string) => {
  if (!editorContext.value || !isEditorReady.value) return
  
  try {
    editorContext.value.setContents({
      html: html || ''
    })
    content.value = html || ''
  } catch (error) {
    console.error('设置编辑器内容失败:', error)
  }
}

// 监听外部值变化（用于从后端加载数据后更新编辑器）
watch(() => props.modelValue, (newValue) => {
  const newHtml = newValue || ''
  if (newHtml !== content.value) {
    content.value = newHtml
    // 如果编辑器已ready，立即设置内容
    if (isEditorReady.value) {
      setTimeout(() => {
        setEditorContent(newHtml)
      }, 100)
    }
  }
}, { immediate: true })

// 编辑器准备就绪
const handleReady = () => {
  if (!instance) return
  
  // 根据官方文档，使用 createSelectorQuery 获取编辑器上下文
  uni.createSelectorQuery()
    .in(instance)
    .select('#editor')
    .context((res: any) => {
      if (res && res.context) {
        editorContext.value = res.context
        isEditorReady.value = true
        
        // 编辑器ready后，设置初始内容
        if (content.value) {
          // 延迟设置，确保编辑器完全初始化
          setTimeout(() => {
            setEditorContent(content.value)
          }, 300)
        }
      }
    })
    .exec()
}

// 编辑器内容变化
const handleInput = (e: any) => {
  content.value = e.detail.html || ''
  emit('update:modelValue', content.value)
}

// 插入图片
const insertImage = () => {
  fileType.value = 'image'
  attachmentRef.value?.open()
}

// 文件选择回调
const handleFileSelected = (file: any) => {
  if (!editorContext.value || !file || !file.file_url) return
  
  const fileUrl = formatFileUrl(file.file_url)
  
  // 插入图片（UniApp editor 组件仅支持 insertImage）
  editorContext.value.insertImage({
    src: fileUrl,
    alt: '图片',
    width: '100%',
    height: 'auto'
  })
}

// 暴露方法
defineExpose({
  getHtml: () => content.value,
  setHtml: (html: string) => {
    content.value = html
    if (editorContext.value) {
      editorContext.value.setContents({ html })
    }
  },
  clear: () => {
    content.value = ''
    if (editorContext.value) {
      editorContext.value.clear()
    }
  }
})
</script>

<style scoped lang="scss">
.rich-text-editor {
  width: 100%;
  background: #f9f9f9;
  border-radius: 8rpx;
  overflow: hidden;
  margin-top: 20rpx;

  .editor-toolbar {
    display: flex;
    justify-content: flex-end;
    gap: 20rpx;
    padding: 20rpx;
    border-top: 1rpx solid #f0f0f0;
    background: #fafafa;

    .toolbar-btn {
      display: flex;
      align-items: center;
      gap: 6rpx;
      padding: 10rpx 20rpx;
      background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%);
      border: none;
      border-radius: 8rpx;
      font-size: 24rpx;
      color: #fff;
      box-shadow: 0 2rpx 8rpx rgba(74, 144, 226, 0.25);
      transition: all 0.3s ease;
      font-weight: 500;

      &:active {
        transform: scale(0.98);
        box-shadow: 0 1rpx 4rpx rgba(74, 144, 226, 0.3);
      }

      text {
        font-size: 24rpx;
        color: #fff;
        font-weight: 500;
      }

      ::v-deep .uni-icons {
        color: #fff !important;
      }
    }
  }

  .editor {
    width: 100%;
    padding: 20rpx;
    background: #fff;
  }
}
</style>

