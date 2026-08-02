<template>
    <view class="upload-container">
        <view class="upload-btn" @click="handleChooseFile">
            <uni-icons type="plusempty" size="24" color="#fff"></uni-icons>
        </view>
        
        <!-- 进度显示 - 独立的悬浮进度条 -->
        <view v-if="uploading" class="upload-progress">
            <view class="progress-content">
                <text class="progress-text">{{ Math.round(overallProgress) }}%</text>
                <view class="progress-bar">
                    <view class="progress-fill" :style="{ width: overallProgress + '%' }"></view>
                </view>
            </view>
        </view>
    </view>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { getToken } from '@/utils/auth';

// 确保组件正确导出
defineOptions({
    name: 'ManageListUpload'
});

const props = defineProps({
    searchParams: {
        type: Object,
        required: true,
        default: () => ({})
    }
});

const emit = defineEmits(['complete', 'error']);

// 添加进度相关的响应式数据
const uploading = ref(false);
const uploadingFiles = ref<Array<{ progress: number }>>([]);

// 计算整体进度
const overallProgress = computed(() => {
    if (uploadingFiles.value.length === 0) return 0;
    const totalProgress = uploadingFiles.value.reduce((sum, file) => sum + file.progress, 0);
    return totalProgress / uploadingFiles.value.length;
});

// 处理选择文件
const handleChooseFile = () => {
    // 如果正在上传，忽略点击
    if (uploading.value) return;
    
    // 选择文件类型，根据searchParams.type决定
    const fileType = getFileTypeFromSearchType(props.searchParams.type);

    // 调用系统选择文件方法
    uni.chooseFile({
        count: 9, // 最多9个文件
        type: fileType,
        extension: getExtensionsByType(props.searchParams.type),
        success: (res) => {
            // 确保tempFiles是数组
            let tempFiles: any[] = [];

            if (res.tempFiles) {
                tempFiles = Array.isArray(res.tempFiles) ? res.tempFiles : [res.tempFiles];
            } else if (res.tempFilePaths) {
                // 兼容只返回路径的情况
                tempFiles = Array.isArray(res.tempFilePaths)
                    ? res.tempFilePaths.map((path: string) => ({ path, size: 0 }))
                    : [{ path: res.tempFilePaths, size: 0 }];
            }

            // console.log('选择的文件:', tempFiles);

            // 检查文件类型是否符合要求
            const validFiles = validateFiles(tempFiles, props.searchParams.type);

            if (validFiles.length) {
                // 逐个上传文件
                uploadFiles(validFiles, props.searchParams.cid);
            }
        },
        fail: (err) => {
            console.error('选择文件失败:', err);
            uni.showToast({
                title: '选择文件失败',
                icon: 'none'
            });
            emit('error', { message: '选择文件失败' });
        }
    });
};

// 根据搜索类型获取文件选择类型
const getFileTypeFromSearchType = (searchType: string): 'image' | 'video' | 'all' => {
    switch (searchType) {
        case 'image': return 'image';
        case 'video': return 'video';
        default: return 'all';
    }
};

// 获取文件扩展名列表
const getExtensionsByType = (searchType: string): string[] => {
    switch (searchType) {
        case 'image':
            return ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        case 'video':
            return ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];
        case 'audio':
            return ['mp3', 'wav', 'aac', 'ogg'];
        case 'file':
            return ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf', 'txt', 'zip', 'rar'];
        default:
            return []; // 空数组表示不限制扩展名
    }
};

// 验证文件
const validateFiles = (files: any[], type: string): any[] => {
    const maxSize = 50; // 默认最大50MB
    const validFiles = [];
    const allowedExts = getExtensionsByType(type);

    for (const file of files) {
        // 检查文件大小
        if (file.size > maxSize * 1024 * 1024) {
            uni.showToast({
                title: `文件大小不能超过${maxSize}MB`,
                icon: 'none'
            });
            continue;
        }

        // 如果指定了类型，检查文件扩展名
        if (allowedExts.length > 0) {
            let isValidType = false;
            
            // 首先尝试从文件名获取扩展名
            let ext = '';
            
            // 检查file对象是否有name属性
            if (file.name) {
                ext = getFileExtension(file.name);
            } 
            // 如果没有name属性或扩展名仍为空，尝试从path获取
            if (!ext && file.path) {
                ext = getFileExtension(file.path);
            }
            
            // 检查MIME类型
            if (file.type) {
                const mimeType = file.type.toLowerCase();
                
                // 根据MIME类型检查文件类型是否匹配
                if (type === 'image' && mimeType.startsWith('image/')) {
                    isValidType = true;
                } else if (type === 'video' && mimeType.startsWith('video/')) {
                    isValidType = true;
                } else if (type === 'audio' && mimeType.startsWith('audio/')) {
                    isValidType = true;
                } else if (ext && allowedExts.includes(ext)) {
                    // 如果有扩展名且在允许列表中
                    isValidType = true;
                }
            } else if (ext && allowedExts.includes(ext)) {
                // 如果有扩展名且在允许列表中
                isValidType = true;
            }
            
            // 如果所有检查都失败，而且是默认接受所有类型，则放行
            if (!isValidType && type === 'file' && allowedExts.length === 0) {
                isValidType = true;
            }
            
            // 如果类型无效，显示错误并跳过
            if (!isValidType) {
                console.log('文件类型无效:', file, '期望类型:', type, '允许扩展名:', allowedExts);
                uni.showToast({
                    title: `不支持的文件类型`,
                    icon: 'none'
                });
                continue;
            }
        }

        validFiles.push(file);
    }

    return validFiles;
};

// 获取文件扩展名的辅助函数，处理各种情况
const getFileExtension = (filePath: string): string => {
    if (!filePath) return '';
    
    // 处理可能的查询参数和哈希
    const pathWithoutParams = filePath.split('?')[0].split('#')[0];
    
    // 获取文件名部分
    const fileName = pathWithoutParams.split('/').pop() || '';
    
    // 如果文件名中有点号，提取扩展名
    const dotIndex = fileName.lastIndexOf('.');
    if (dotIndex > 0 && dotIndex < fileName.length - 1) {
        return fileName.substring(dotIndex + 1).toLowerCase();
    }
    
    return '';
};

// 上传文件
const uploadFiles = async (files: any[], cid: number) => {
    try {
        // 设置上传状态
        uploading.value = true;
        
        // 初始化进度数组
        uploadingFiles.value = files.map(() => ({ progress: 0 }));
        
        // 获取token
        const accessToken = getToken('access_token');

        // 根据文件类型获取上传地址
        let uploadUrl = '';
        switch (props.searchParams.type) {
            case 'image':
                uploadUrl = `${import.meta.env.VITE_APP_BASE_URL}/api/attachment/files/image`;
                break;
            case 'video':
                uploadUrl = `${import.meta.env.VITE_APP_BASE_URL}/api/attachment/files/video`;
                break;
            default:
                // 报错
                uni.showToast({
                    title: '不支持的文件类型',
                    icon: 'none'
                });
                return;
        }

        // 使用Promise.all并发上传多个文件
        const uploadPromises = files.map((file, index) => {
            return new Promise((resolve, reject) => {
                const uploadTask = uni.uploadFile({
                    url: uploadUrl,
                    filePath: file.path,
                    name: 'file',
                    formData: {
                        cid: cid || 0,
                    },
                    header: {
                        'access-token': accessToken || ''
                    },
                    success: (res) => {
                        // 解析响应数据
                        let result;
                        try {
                            result = JSON.parse(res.data);
                        } catch (e) {
                            reject(new Error('解析响应数据失败'));
                            return;
                        }

                        // 处理响应
                        if (result.code === 10000) {
                            // 设置完成进度
                            uploadingFiles.value[index].progress = 100;
                            resolve(result);
                        } else {
                            reject(new Error(result.message || '上传失败'));
                        }
                    },
                    fail: (err) => {
                        reject(err);
                    }
                });

                // 监听上传进度
                if (uploadTask && typeof uploadTask.onProgressUpdate === 'function') {
                    uploadTask.onProgressUpdate((res) => {
                        //console.log('上传进度:', res.progress);
                        // 更新对应文件的进度
                        uploadingFiles.value[index].progress = res.progress || 0;
                    });
                }
            });
        });

        // 等待所有上传完成
        await Promise.all(uploadPromises);

        // 上传成功提示
        uni.showToast({
            title: '上传成功',
            icon: 'success'
        });

        // 通知父组件上传完成
        emit('complete');
    } catch (error: any) {
        console.error('上传失败:', error);
        uni.showToast({
            title: error.message || '上传失败',
            icon: 'none'
        });
        emit('error', error);
    } finally {
        // 重置状态
        setTimeout(() => {
            uploading.value = false;
            uploadingFiles.value = [];
        }, 500); // 500毫秒后重置，让用户看到完成状态
    }
};
</script>

<style lang="scss" scoped>
// 容器样式
.upload-container {
    position: relative;
}

// 上传按钮样式
.upload-btn {
    position: fixed;
    right: 30rpx;
    bottom: calc(30rpx + env(safe-area-inset-bottom));
    width: 100rpx;
    height: 100rpx;
    border-radius: 50%;
    background-color: #007aff;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4rpx 10rpx rgba(0, 0, 0, 0.1);
    z-index: 100;
}

// 进度显示
.upload-progress {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 9999;
    
    .progress-content {
        padding: 30rpx 40rpx;
        background: rgba(0, 0, 0, 0.7);
        border-radius: 12rpx;
        display: flex;
        flex-direction: column;
        align-items: center;
        min-width: 200rpx;
        
        .progress-text {
            color: #fff;
            font-size: 32rpx;
            font-weight: bold;
            margin-bottom: 16rpx;
        }
        
        .progress-bar {
            width: 160rpx;
            height: 8rpx;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4rpx;
            overflow: hidden;
            
            .progress-fill {
                height: 100%;
                background: linear-gradient(90deg, #ff6b6b, #feca57);
                border-radius: 4rpx;
                transition: width 0.3s ease;
            }
        }
    }
}
</style>