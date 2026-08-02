<template>
    <el-drawer v-model="dialogVisible" :title="popTitle" size="80%">
        <el-scrollbar style="height: 100%;">
            <div class="lesson-management">
                <el-card shadow="never">
                    <template #header>
                        <div class="card-header">
                            <span>课时管理</span>
                            <el-button type="primary" @click="handleAddLesson">添加课时</el-button>
                        </div>
                    </template>

                    <el-table :data="lessonList" border v-loading="loading">
                        <el-table-column label="序号" type="index" width="60" />
                        <el-table-column label="课时名称" prop="lesson_name" min-width="200">
                            <template #default="{ row }">
                                <span class="font-medium">{{ row.lesson_name }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column label="课时类型" prop="lesson_type_desc" width="120" />
                        <el-table-column label="是否免费" prop="is_free_desc" width="100" />
                        <el-table-column label="排序" prop="sort" width="80" />
                        <el-table-column label="创建时间" prop="create_at" width="160" />
                        <el-table-column label="操作" width="200" fixed="right">
                            <template #default="{ row }">
                                <el-button type="primary" size="small" @click="handleEditLesson(row)">
                                    编辑
                                </el-button>
                                <el-button type="danger" size="small" @click="handleDeleteLesson(row.id)">
                                    删除
                                </el-button>
                            </template>
                        </el-table-column>
                    </el-table>

                    <el-empty v-if="lessonList.length === 0" description="暂无课时数据" />
                </el-card>
            </div>

            <div class="flex justify-center mt-[20px]">
                <el-button @click="dialogVisible = false">关闭</el-button>
            </div>
        </el-scrollbar>

        <kms-lesson-edit-dialog ref="lessonEditDialogRef" @complete="handleLessonComplete" />
    </el-drawer>
</template>

<script lang="ts" setup>
import { ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import KmsLessonEditDialog from './kms-lesson-edit-dialog.vue'
import { getKmsLessonList, deleteKmsLesson } from '@/pages-admin/platform/kms/api/kms/kmsLesson'

interface LessonData {
    id: number
    goods_id: number
    lesson_name: string
    lesson_desc: string
    lesson_type: number
    video_url: string
    audio_url: string
    content: string
    sort: number
    is_free: number
    create_at: number
    update_at: number
    lesson_type_desc: string
    is_free_desc: string
}

const dialogVisible = ref(false)
const loading = ref(false)
const popTitle = ref('课时管理')
const lessonList = ref<LessonData[]>([])
const currentGoodsId = ref(0)
const lessonEditDialogRef = ref()

const fetchLessonList = async (goodsId: number) => {
    if (!goodsId) return

    loading.value = true
    try {
        const result = await getKmsLessonList({ goods_id: goodsId })
        lessonList.value = result.data || []
    } catch (error) {
        console.error('获取课时列表失败:', error)
        ElMessage.error('获取课时列表失败')
    } finally {
        loading.value = false
    }
}

const setDialogData = async (row: any = null) => {
    if (row?.id) {
        currentGoodsId.value = row.id
        await fetchLessonList(row.id)
        dialogVisible.value = true
    }
}

const handleAddLesson = () => {
    lessonEditDialogRef.value.setDialogData(null, currentGoodsId.value)
    lessonEditDialogRef.value.openDialog()
}

const handleEditLesson = (lesson: LessonData) => {
    lessonEditDialogRef.value.setDialogData(lesson, currentGoodsId.value)
    lessonEditDialogRef.value.openDialog()
}

const handleDeleteLesson = async (lessonId: number) => {
    try {
        await ElMessageBox.confirm('确定要删除这个课时吗？删除后无法恢复！', '提示', {
            confirmButtonText: '确定',
            cancelButtonText: '取消',
            type: 'warning'
        })

        await deleteKmsLesson(lessonId)

        const index = lessonList.value.findIndex(lesson => lesson.id === lessonId)
        if (index !== -1) {
            lessonList.value.splice(index, 1)
        }

        ElMessage.success('删除成功')
    } catch (error) {
        if (error !== 'cancel') {
            console.error('删除课时失败:', error)
            ElMessage.error('删除课时失败')
        }
    }
}

const handleLessonComplete = () => {
    if (currentGoodsId.value) {
        fetchLessonList(currentGoodsId.value)
    }
    ElMessage.success('操作成功')
}

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})
</script>

<style scoped>
.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.el-card {
    margin-bottom: 20px;
}

.lesson-management {
    border: 1px solid #ebeef5;
    border-radius: 4px;
}

.font-medium {
    font-weight: 500;
}
</style>
