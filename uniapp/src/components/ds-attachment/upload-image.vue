<template>
  <view class="ds-upload-image">
    <!-- 已上传图片列表 -->
    <view class="image-list">
      <view 
        v-for="(image, index) in fileList" 
        :key="index" 
        class="image-item"
        :class="{'has-error': image.status === 'error'}"
      >
        <image 
          :src="formatImageUrl(image.url, ThumbnailPresets.small)" 
          mode="aspectFill" 
          class="preview-image"
          @tap="previewImage(index)"
        />
        
        <!-- 删除按钮 -->
        <text class="delete-btn" @tap.stop="deleteImage(index)">×</text>
        
        <!-- 上传进度 -->
        <view class="upload-progress-container" v-if="image.status === 'uploading'">
          <view class="progress-mask"></view>
          <view class="progress-info">
            <text class="progress-text">{{ image.progress || 0 }}%</text>
            <progress 
              :percent="image.progress || 0" 
              stroke-width="4" 
              activeColor="#FF6034"
              backgroundColor="rgba(255,255,255,0.3)"
              class="progress-bar"
            />
          </view>
        </view>
      </view>

      <!-- 上传按钮 -->
      <view 
        v-if="fileList.length < maxCount" 
        class="upload-button"
        @tap="handleChooseImage"
      >
        <view class="upload-button-inner">
          <uni-icons type="camera-filled" size="24" color="#bbb"></uni-icons>
          <text class="upload-text">{{ buttonText }}</text>
        </view>
      </view>
    </view>
  </view>
</template>

<script setup lang="ts">
import { ref, computed, watch, type PropType } from 'vue';
import { getToken } from '@/utils/auth'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'

// 定义图片类型
interface ImageItem {
  url: string;
  file?: any;
  status: 'uploading' | 'done' | 'error';
  progress?: number;
  response?: any;
}

// 简化组件属性
const props = defineProps({
  // 文件列表默认值
  modelValue: {
    type: Array as PropType<string[]>,
    default: () => []
  },
  // 最大上传数量
  maxCount: {
    type: Number,
    default: 1
  },
  // 单个文件大小限制（单位MB）
  maxSize: {
    type: Number,
    default: 5
  },
  // 上传按钮文本
  buttonText: {
    type: String,
    default: '上传图片'
  },
  // 上传地址
  uploadUrl: {
    type: String,
    default: '/attachment/files/image'
  },
  // 上传所需额外参数
  formData: {
    type: Object,
    default: () => ({})
  }
});

// 事件 - 只保留必要的三个
const emit = defineEmits([
  'update:modelValue', // 必须保留，用于v-model
  'success',          // 上传成功回调
  'error'             // 上传失败回调
]);

// 文件列表
const fileList = ref<ImageItem[]>([]);

// 添加一个标志，防止监听器在上传过程中重置fileList
const isUploading = ref(false);

// 监听modelValue变化
watch(() => props.modelValue, (newVal) => {
  // 如果正在上传中，不重置fileList
  if (isUploading.value) return;
  
  if (newVal && newVal.length) {
    fileList.value = newVal.map(url => ({
      url,
      status: 'done'
    }));
  } else {
    // 处理空数组的情况
    fileList.value = [];
  }
}, { immediate: true });

// 计算是否可以继续上传
const canUpload = computed(() => fileList.value.length < props.maxCount);

// 选择图片
const handleChooseImage = () => {
  if (!canUpload.value) {
    uni.showToast({
      title: `最多只能上传${props.maxCount}张图片`,
      icon: 'none'
    });
    return;
  }

  uni.chooseImage({
    count: props.maxCount - fileList.value.length,
    sizeType: ['original', 'compressed'],
    sourceType: ['album', 'camera'],
    success: (res) => {
      // 确保tempFiles是数组
      let tempFiles: any[] = [];
      
      if (res.tempFiles) {
        tempFiles = Array.isArray(res.tempFiles) ? res.tempFiles : [res.tempFiles];
      } else if (res.tempFilePaths) {
        // 兼容只返回路径的情况
        if (Array.isArray(res.tempFilePaths)) {
          tempFiles = res.tempFilePaths.map((path: string) => ({
            path,
            size: 0 // 无法获取大小时默认通过
          }));
        } else {
          tempFiles = [{
            path: res.tempFilePaths,
            size: 0
          }];
        }
      }
      
      // 检查文件大小
      const validFiles = [];
      for (let i = 0; i < tempFiles.length; i++) {
        const file = tempFiles[i];
        if (file.size <= props.maxSize * 1024 * 1024) {
          validFiles.push(file);
        } else {
          uni.showToast({
            title: `图片大小不能超过${props.maxSize}MB`,
            icon: 'none'
          });
        }
      }

      // 处理有效文件
      if (validFiles.length) {
        const newImages: ImageItem[] = [];
        const startIndex = fileList.value.length;
        
        // 先创建图片项并添加到列表
        validFiles.forEach((file) => {
          const newImage: ImageItem = {
            url: file.path,
            file: file,
            status: 'uploading',
            progress: 0
          };
          newImages.push(newImage);
          fileList.value.push(newImage);
        });
        
        // 设置上传中标志
        isUploading.value = true;
        
        // 自动上传
        setTimeout(() => {
          for (let i = 0; i < newImages.length; i++) {
            uploadImage(startIndex + i);
          }
        }, 100);
      }
    },
    fail: (err) => {
      console.error('Choose image failed:', err);
      uni.showToast({
        title: '选择图片失败',
        icon: 'none'
      });
    }
  });
};

// 上传图片
const uploadImage = async (index: number) => {
  // 检查索引是否有效
  if (index < 0 || index >= fileList.value.length) {
    return;
  }
  
  const image = fileList.value[index];
  if (!image || !image.url || image.status === 'done') return;

  // 获取token
  const accessToken = getToken('access_token');
  
  try {
    // 创建上传任务
    const uploadTask = uni.uploadFile({
      url: (import.meta.env.VITE_APP_BASE_URL +'/api' || '') + props.uploadUrl,
      filePath: image.url,
      name: 'file',
      formData: props.formData,
      header: {
        'access-token': accessToken || ''
      },
      success: (res) => {
        // 解析响应数据
        let result;
        try {
          result = JSON.parse(res.data);
        } catch (e) {
          throw new Error('解析响应数据失败');
        }

        // 处理响应
        if (result.code === 10000) {
          // 更新文件状态
          const currentIndex = fileList.value.findIndex(item => item === image);
          if (currentIndex !== -1) {
            fileList.value[currentIndex].status = 'done';
            fileList.value[currentIndex].response = result;
            
            if (result.data && result.data.path) {
              fileList.value[currentIndex].url = result.data.path;
            }
            
            // 不在这里调用emitChange，而是在所有图片上传完成后统一调用
            // 检查是否所有图片都已上传完成
            checkAllUploadsComplete();
          }
        } else {
          throw new Error(result.message || '上传失败');
        }
      },
      fail: (err) => {
        throw err;
      }
    });

    // 监听上传进度
    if (uploadTask && typeof uploadTask.onProgressUpdate === 'function') {
      uploadTask.onProgressUpdate((res) => {
        const currentIndex = fileList.value.findIndex(item => item === image);
        if (currentIndex !== -1) {
          fileList.value[currentIndex].progress = res.progress;
        }
      });
    }
  } catch (error: any) {
    console.error('Upload failed:', error);
    
    // 更新文件状态为错误
    const currentIndex = fileList.value.findIndex(item => item === image);
    if (currentIndex !== -1) {
      fileList.value[currentIndex].status = 'error';
      
      emit('error', {
        error,
        index: currentIndex,
        file: image.file
      });
    }
    
    // 显示错误提示
    uni.showToast({
      title: error.message || '上传失败',
      icon: 'none'
    });
    
    // 检查是否所有图片都已上传完成或失败
    checkAllUploadsComplete();
  }
};

// 检查所有上传是否完成
const checkAllUploadsComplete = () => {
  const allComplete = fileList.value.every(item => 
    item.status === 'done' || item.status === 'error'
  );
  
  if (allComplete) {
    // 所有上传都已完成，可以安全地发出更新事件
    isUploading.value = false;
    emitChange();
  }
};

// 删除图片
const deleteImage = (index: number) => {
  // 检查索引是否有效
  if (index < 0 || index >= fileList.value.length) {
    return;
  }
  
  uni.showModal({
    title: '提示',
    content: '确定要删除这张图片吗？',
    success: (res) => {
      if (res.confirm) {
        fileList.value.splice(index, 1);
        
        // 发出更新事件
        emitChange();
      }
    }
  });
};

// 预览图片
const previewImage = (index: number) => {
  // 检查索引是否有效
  if (index < 0 || index >= fileList.value.length) {
    return;
  }
  
  const urls = fileList.value.map(item => formatImageUrl(item.url, ThumbnailPresets.original));
  uni.previewImage({
    urls,
    current: index
  });
};

// 获取已上传图片URLs
const getUrls = () => {
  return fileList.value
    .filter(item => item.status === 'done')
    .map(item => item.url);
};

// 发出更新事件
const emitChange = () => {
  const urls = getUrls();
  emit('update:modelValue', urls);
};

// 清空图片
const clear = () => {
  fileList.value = [];
  emitChange();
};

// 对外暴露方法
defineExpose({
  upload: () => {
    fileList.value.forEach((image, index) => {
      if (image.status === 'uploading' && image.progress === 0) {
        uploadImage(index);
      }
    });
  },
  clear,
  getUrls,
  fileList
});
</script>

<style lang="scss" scoped>
.ds-upload-image {
  // width: 100%;
  
  .image-list {
    display: flex;
    flex-wrap: wrap;
    
    .image-item, .upload-button {
      position: relative;
      width: 150rpx;
      height: 150rpx;
      margin-right: 16rpx;
      margin-bottom: 16rpx;
      border-radius: 8rpx;

      box-shadow: 0 2rpx 6rpx rgba(0, 0, 0, 0.08);
    }
    
    .image-item {
      border: 1px solid #f0f0f0;
      background-color: #fff;
      
      &.has-error {
        border-color: #ff4d4f;
      }
      
      .preview-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }
      
      // 删除按钮样式
      .delete-btn {
        position: absolute;
        right: -10rpx;
        top: -10rpx;
        width: 40rpx;
        height: 40rpx;
        line-height: 36rpx;
        text-align: center;
        background: rgba(0, 0, 0, 0.6);
        color: #fff;
        border-radius: 50%;
        font-size: 32rpx;
        font-weight: bold;
        z-index: 10;
      }
      
      // 上传进度显示
      .upload-progress-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        
        .progress-mask {
          position: absolute;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.4);
        }
        
        .progress-info {
          position: relative;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          width: 80%;
          z-index: 1;
          
          .progress-text {
            color: white;
            font-size: 22rpx;
            margin-bottom: 8rpx;
            font-weight: bold;
          }
          
          .progress-bar {
            width: 100%;
            height: 6rpx;
            border-radius: 6rpx;
            overflow: hidden;
          }
        }
      }
    }
    
    // 上传按钮
    .upload-button {
      border: 2rpx dashed #dddddd;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #fafafa;
      
      .upload-button-inner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        
        .upload-text {
          font-size: 22rpx;
          color: #999;
          margin-top: 8rpx;
        }
      }
      
      &:active {
        border-color: #FF6034;
        background-color: #fff5f2;
      }
    }
  }
}
</style>
