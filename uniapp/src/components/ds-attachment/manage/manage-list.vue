<template>
    <view class="manage-list-container">
        <!-- 文件列表 -->
        <z-paging ref="filesPaging" v-model="fileList" @query="queryFileList" class="file-list-container"
            :fixed="false" height="100%">
            <view class="file-list">
                <view v-for="file in fileList" :key="file.id" class="file-item"
                    :class="{ selected: selectedFiles.includes(file.id) }" @click="handleFileClick(file)">
                    <!-- 选择指示器 -->
                    <view v-if="isSelectMode" class="select-indicator" @click.stop="toggleSelect(file.id)">
                        <view class="select-circle" :class="{ selected: selectedFiles.includes(file.id) }">
                            <uni-icons v-if="selectedFiles.includes(file.id)" type="checkmark" size="12"
                                color="#fff"></uni-icons>
                        </view>
                    </view>

                    <!-- 文件预览 -->
                    <view class="file-preview">
                        <!-- 图片预览 -->
                        <image v-if="isImageFile(file.file_ext)" :src="formatImageUrl(file.file_url, ThumbnailPresets.medium)" mode="aspectFill"
                            class="preview-image" @click.stop="previewFile(file)"></image>
                        
                        <!-- 视频预览 -->
                        <view v-else-if="isVideoFile(file.file_ext)" class="video-preview" @click.stop="previewFile(file)">
                            <view class="video-icon-wrapper">
                                <uni-icons type="videocam-filled" size="32" color="#666"></uni-icons>
                            </view>
                            <!-- 播放图标覆盖层 -->
                            <view class="play-overlay">
                                <view class="play-icon">
                                    <uni-icons type="play-filled" size="20" color="#fff"></uni-icons>
                                </view>
                            </view>
                        </view>
                        
                        <!-- 其他文件预览 -->
                        <view v-else class="file-icon">
                            <uni-icons type="plus" size="32" color="#999"></uni-icons>
                        </view>
                    </view>

                    <!-- 文件信息 -->
                    <view class="file-info">
                        <text class="file-name">{{ file.file_name }}</text>
                        <text class="file-meta">{{ formatFileSize(file.file_size) }} · {{ file.create_at }}</text>
                    </view>
                </view>
            </view>
        </z-paging>

        <!-- 底部操作栏 -->
        <view v-if="isSelectMode && selectedFiles.length > 0" class="bottom-bar">
            <view class="action-btns">
                <view class="action-btn danger" @click="handleDelete">
                    <uni-icons type="trash" size="24" color="#ff3b30"></uni-icons>
                    <text>删除</text>
                </view>
            </view>
        </view>

        <!-- 上传按钮 -->
        <manage-list-upload v-if="!isSelectMode" :search-params="searchParams" @complete="handleUploadComplete"
            @error="handleUploadError"></manage-list-upload>
            
        <!-- 视频预览组件 -->
        <ds-video-preview ref="videoPreviewRef"></ds-video-preview>
    </view>
</template>

<script setup lang="ts" name="ManageList">
import { ref, watch } from 'vue'
import { formatFileUrl } from '@/utils/util'
import { formatImageUrl, ThumbnailPresets } from '@/utils/image'
import { getAttachmentFilePages, deleteAttachmentFile } from '@/api/attachment'

// 直接导入组件
import ManageListUpload from './manage-list-upload.vue'
import DsVideoPreview from '../../ds-video-preview/index.vue'

// 让根节点直接参与父级 flex 布局（小程序高度链关键）
defineOptions({
    options: {
        virtualHost: true
    }
})

// 定义文件接口
interface AttachmentFile {
    id: number;
    file_name: string;
    file_url: string;
    file_ext: string;
    file_size: number;
    create_at: string;
    [key: string]: any;
}

const props = defineProps({
    isSelectMode: {
        type: Boolean,
        default: false
    },
    selectedFiles: {
        type: Array as () => number[],
        default: () => []
    },
    searchParams: {
        type: Object,
        required: true,
        default: () => ({
            type: '',
            cid: 0,
            keywords: ''
        })
    }
})

const emit = defineEmits(['select', 'toggleSelect', 'delete', 'upload', 'selected-files-change'])

// 文件列表
const filesPaging = ref<any>(null)
const fileList = ref<AttachmentFile[]>([])
// 视频预览组件引用
const videoPreviewRef = ref()

// 监听搜索参数变化
watch(
    () => props.searchParams,
    () => {
        reloadFiles()
    },
    { deep: true }
)

// 重新加载文件列表
const reloadFiles = () => {
    if (filesPaging.value) {
        filesPaging.value.reload()
    }
}

// 查询文件列表
const queryFileList = async (pageNo: number, pageSize: number) => {
    try {
        const res = await getAttachmentFilePages({
            page_current: pageNo,
            page_size: pageSize,
            ...props.searchParams
        })

        // 处理接口返回的数据，确保字段映射正确
        const mappedData = (res.data.data || []).map((item: any) => {
            return {
                id: item.id,
                file_name: item.name || '',
                file_url: item.path || '',
                file_ext: item.path ? item.path.split('.').pop() : '',
                file_size: item.size || 0,
                file_type: item.type || '',
                cid: item.cid || 0,
                create_at: item.create_at || '',
                // 其他需要的字段映射
            }
        })

        if (filesPaging.value) {
            filesPaging.value.complete(mappedData)
        }
    } catch (error) {
        console.error('获取文件列表失败', error)
        if (filesPaging.value) {
            filesPaging.value.complete(false)
        }
    }
}

// 文件点击处理
const handleFileClick = (file: AttachmentFile) => {
    if (props.isSelectMode) {
        toggleSelect(file.id)
    } else {
        // 选择文件并返回
        emit('select', file)
    }
}

// 文件预览处理
const previewFile = (file: AttachmentFile) => {
    if (props.isSelectMode) {
        toggleSelect(file.id)
        return
    }
    
    if (isImageFile(file.file_ext)) {
        // 图片预览
        const imageUrls = fileList.value
            .filter(f => isImageFile(f.file_ext))
            .map(f => formatImageUrl(f.file_url, ThumbnailPresets.original))
        
        uni.previewImage({
            urls: imageUrls,
            current: formatImageUrl(file.file_url, ThumbnailPresets.original)
        })
    } else if (isVideoFile(file.file_ext)) {
        // 视频预览
        videoPreviewRef.value?.show({
            url: formatFileUrl(file.file_url),
            title: file.file_name
        })
    } else {
        // 其他文件类型暂不支持预览
        uni.showToast({
            title: '该文件类型暂不支持预览',
            icon: 'none'
        })
    }
}

// 切换选择
const toggleSelect = (fileId: number) => {
    emit('toggleSelect', fileId)

    // 同时更新内部选择状态 - 可选实现，取决于组件使用方式
    const newSelectedFiles = [...props.selectedFiles]
    const index = newSelectedFiles.indexOf(fileId)

    if (index === -1) {
        newSelectedFiles.push(fileId)
    } else {
        newSelectedFiles.splice(index, 1)
    }

    emit('selected-files-change', newSelectedFiles)
}

// 处理删除按钮点击
const handleDelete = () => {
    if (props.selectedFiles.length === 0) return
    confirmDeleteFiles(props.selectedFiles)
}

// 确认删除文件
const confirmDeleteFiles = (fileIds: number[], callback?: Function) => {
    if (fileIds.length === 0) return

    uni.showModal({
        title: '提示',
        content: `确定要删除选中的${fileIds.length}个文件吗？`,
        success: async (res) => {
            if (res.confirm) {
                try {
                    await deleteAttachmentFile({ ids: fileIds })
                    uni.showToast({
                        title: '删除成功',
                        icon: 'success'
                    })

                    if (callback) {
                        callback()
                    }

                    // 删除成功后触发删除事件，让父组件清空选中状态
                    emit('delete')

                    // 清空选中文件
                    emit('selected-files-change', [])

                    reloadFiles()
                } catch (error) {
                    console.error('删除失败', error)
                    uni.showToast({
                        title: '删除失败',
                        icon: 'none'
                    })
                }
            }
        }
    })
}

// 工具函数：判断是否为图片文件
const isImageFile = (ext: string): boolean => {
    if (!ext) return false
    const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']
    return imageExts.includes(ext.toLowerCase())
}

// 工具函数：判断是否为视频文件
const isVideoFile = (ext: string): boolean => {
    if (!ext) return false
    const videoExts = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm', '3gp']
    return videoExts.includes(ext.toLowerCase())
}

// 工具函数：格式化文件大小
const formatFileSize = (size: number): string => {
    if (!size) return '0 B'

    const units = ['B', 'KB', 'MB', 'GB', 'TB']
    let i = 0
    while (size >= 1024 && i < units.length - 1) {
        size /= 1024
        i++
    }

    return `${size.toFixed(2)} ${units[i]}`
}

// 处理上传完成
const handleUploadComplete = () => {
    // 刷新文件列表
    reloadFiles();
}

// 处理上传错误
const handleUploadError = (error: any) => {
    console.error('上传出错:', error);
}

</script>

<style lang="scss" scoped>
.manage-list-container {
    display: flex;
    flex-direction: column;
    width: 100%;
    height: 100%;
    min-height: 0;
    box-sizing: border-box;
}

.file-list-container {
    flex: 1;
    min-height: 0;
    height: 100%;
    background-color: #fff;
    overflow: hidden;
}

.file-list {
    padding: 0 30rpx;

    .file-item {
        display: flex;
        align-items: center;
        padding: 20rpx 0;
        border-bottom: 1px solid #f5f5f5;

        .select-indicator {
            width: 50rpx;
            display: flex;
            justify-content: center;
            align-items: center;

            .select-circle {
                width: 26rpx;
                height: 26rpx;
                border-radius: 50%;
                border: 1px solid #ccc;
                display: flex;
                align-items: center;
                justify-content: center;

                &.selected {
                    background-color: #007aff;
                    border-color: #007aff;
                }
            }
        }

        .file-preview {
            width: 80rpx;
            height: 80rpx;
            border-radius: 10rpx;
            overflow: hidden;
            margin-right: 20rpx;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;

            .preview-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            
            .video-preview {
                width: 100%;
                height: 100%;
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                background-color: #f5f5f5;
                
                .video-icon-wrapper {
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .play-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: rgba(0, 0, 0, 0.3);
                    
                    .play-icon {
                        width: 28rpx;
                        height: 28rpx;
                        border-radius: 50%;
                        background: rgba(0, 0, 0, 0.6);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    }
                }
            }
        }

        .file-info {
            flex: 1;
            overflow: hidden;

            .file-name {
                font-size: 28rpx;
                color: #333;
                margin-bottom: 6rpx;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: block;
            }

            .file-meta {
                font-size: 24rpx;
                color: #999;
            }
        }
    }
}

// 底部操作栏样式
.bottom-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100rpx;
    background-color: #fff;
    box-shadow: 0 -2rpx 10rpx rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    padding: 0 30rpx;
    z-index: 100;

    .action-btns {
        display: flex;
        justify-content: flex-end;
        width: 100%;

        .action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 30rpx;

            text {
                font-size: 24rpx;
                margin-top: 6rpx;
            }

            &.danger {
                color: #ff3b30;
            }
        }
    }
}
</style>
