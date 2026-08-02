<template>
  <view class="attachment-picker">
    <!-- 已选择文件列表 -->
    <view class="picker-list">
      <!-- 已选择的文件项 -->
      <view v-for="(item, index) in filesList.data" :key="index" class="picker-item"
        :style="{ width: props.width, height: props.height }">
        <!-- 图片预览 -->
        <image v-if="isImageFile(item)" :src="formatImageUrl(item, ThumbnailPresets.medium)" :style="{ width: props.width, height: props.height }"
          mode="aspectFill" class="preview-image" @click="previewFile(index)"></image>

        <!-- 视频预览 -->
        <view v-else-if="isVideoFile(item)" class="video-preview" @click="previewFile(index)">
          <view class="video-icon-wrapper">
            <uni-icons type="videocam-filled" size="30" color="#999"></uni-icons>
          </view>
        </view>

        <!-- 其他文件预览 -->
        <view v-else class="file-preview" @click="previewFile(index)">
          <uni-icons type="plus" size="30" color="#999"></uni-icons>
          <text class="file-ext">{{ getFileExt(item).toUpperCase() }}</text>
        </view>

        <!-- 删除按钮 -->
        <view class="delete-icon" @click.stop="removeFile(index)">
          <uni-icons type="close" size="20" color="#000"></uni-icons>
        </view>
      </view>


      <!-- 添加按钮 -->
      <view v-if="filesList.data.length < props.limit" class="picker-add"
        :style="{ width: props.width, height: props.height }" @click="openFileList">
        <uni-icons 
          :type="props.type === 'video' ? 'videocam' : 'camera'" 
          size="30" 
          color="#ddd"
        ></uni-icons>
        <text class="add-text">{{ props.type === 'video' ? '添加视频' : '添加图片' }}</text>
      </view>
    </view>


    <!-- 文件管理弹窗 -->
    <ds-attachment-index ref="fileListRef" @select="onFileSelected" :type="props.type"></ds-attachment-index>
    
    <!-- 视频预览组件 -->
    <ds-video-preview ref="videoPreviewRef"></ds-video-preview>
  </view>
</template>

<script setup lang="ts">
import { ref, reactive, watch } from 'vue'
import { formatFileUrl } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// 直接导入组件
import DsAttachmentIndex from './manage/index.vue'
import DsVideoPreview from '../ds-video-preview/index.vue'

const props = defineProps({
  modelValue: {
    type: [String, Array],
    default: () => []
  },
  limit: {
    type: Number,
    default: 1
  },
  width: {
    type: String,
    default: '200rpx'
  },
  height: {
    type: String,
    default: '200rpx'
  },
  type: {
    type: String,
    default: 'image'
  }
})

const emit = defineEmits(['update:modelValue', 'change'])

// 文件列表弹窗引用
const fileListRef = ref()
// 视频预览组件引用
const videoPreviewRef = ref()

// 内部文件列表数据
const filesList = reactive<{ data: string[] }>({
  data: []
})

// 监听modelValue变化，同步到内部数据
watch(() => props.modelValue, () => {
  if (Array.isArray(props.modelValue)) {
    filesList.data = props.modelValue.map(item => String(item))
  } else {
    filesList.data = props.modelValue === '' ? [] : [String(props.modelValue || '')]
  }
}, { immediate: true })

// 打开文件列表
const openFileList = () => {
  fileListRef.value.open()
}

// 文件选择回调
const onFileSelected = (file: any) => {

  // 检查是否已经选择了此文件
  if (filesList.data.includes(file.file_url)) {
    uni.showToast({
      title: '已经选择了该文件',
      icon: 'none'
    })
    return
  }

  // 检查文件类型是否符合要求
  if (props.type === 'image' && !isFileOfType(getFileExt(file.file_url), 'image')) {
    uni.showToast({
      title: '请选择图片文件',
      icon: 'none'
    })
    return
  }

  if (props.type === 'video' && !isFileOfType(getFileExt(file.file_url), 'video')) {
    uni.showToast({
      title: '请选择视频文件',
      icon: 'none'
    })
    return
  }

  // 添加文件
  if (props.limit === 1) {
    filesList.data = [file.file_url]
  } else {
    // 添加新文件，如果超过限制则移除最早的
    filesList.data.push(file.file_url)
    if (filesList.data.length > props.limit) {
      filesList.data = filesList.data.slice(-props.limit)
    }
  }

  handleChange()
}

// 移除文件
const removeFile = (index: number) => {
  filesList.data.splice(index, 1)
  handleChange()
}

// 更新modelValue
const handleChange = () => {
  const value = props.limit === 1 ? (filesList.data[0] || '') : filesList.data
  emit('update:modelValue', value)
  emit('change', value)
}

// 获取文件扩展名
const getFileExt = (filePath: string): string => {
  if (!filePath) return ''
  const fileName = filePath.split('/').pop() || ''
  return fileName.includes('.') ? fileName.split('.').pop() || '' : ''
}

// 预览文件
const previewFile = (index: number) => {
  const filePath = filesList.data[index]

  if (isImageFile(filePath)) {
    // 图片预览 - 修复map的类型错误
    const imageUrls = filesList.data
      .filter(path => isImageFile(path))
      .map((path) => formatImageUrl(path, ThumbnailPresets.original))

    uni.previewImage({
      urls: imageUrls,
      current: formatImageUrl(filePath, ThumbnailPresets.original)
    })
  } else if (isVideoFile(filePath)) {
    // 视频预览 - 使用视频预览组件
    const fileName = filePath.split('/').pop() || ''
    videoPreviewRef.value?.show({
      url: formatFileUrl(filePath),
      title: fileName
    })
  } else {
    // 其他文件，可以根据需求处理
    uni.showToast({
      title: '该文件类型暂不支持预览',
      icon: 'none'
    })
  }
}

// 判断文件是否为特定类型
const isFileOfType = (ext: string, type: 'image' | 'video'): boolean => {
  if (!ext) return false
  ext = ext.toLowerCase()

  const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']
  const videoExts = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv']

  return type === 'image' ? imageExts.includes(ext) : videoExts.includes(ext)
}

// 工具函数：判断是否为图片文件
const isImageFile = (filePath: string): boolean => {
  if (!filePath) return false
  return isFileOfType(getFileExt(filePath), 'image')
}

// 工具函数：判断是否为视频文件
const isVideoFile = (filePath: string): boolean => {
  if (!filePath) return false
  return isFileOfType(getFileExt(filePath), 'video')
}


</script>

<style lang="scss" scoped>
.attachment-picker {
  .picker-list {
    display: flex;
    flex-wrap: wrap;
    gap: 20rpx;
    padding: 20rpx;

    .picker-item {
      position: relative;
      border-radius: 8rpx;
      overflow: hidden;
      background-color: #f5f5f5;

      .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .video-preview {
        width: 100%;
        height: 100%;
        position: relative;

        &::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0, 0, 0, 0.2);
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .video-icon-wrapper {
          width: 100%;
          height: 100%;
          display: flex;
          align-items: center;
          justify-content: center;
          background-color: #f5f5f5;
        }
      }

      .file-preview {
        width: 100%;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        background-color: #f5f5f5;

        .file-ext {
          font-size: 20rpx;
          color: #666;
          margin-top: 8rpx;
        }
      }

      .delete-icon {
        position: absolute;
        top: 0;
        right: 0;
        width: 36rpx;
        height: 36rpx;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom-left-radius: 8rpx;
        z-index: 2;
      }
    }

    .picker-add {
      border: 2rpx dashed #ddd;
      border-radius: 8rpx;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background-color: #fff;
      
      .add-text {
        font-size: 24rpx;
        color: #999;
        margin-top: 8rpx;
        text-align: center;
      }
    }
  }
}
</style>
