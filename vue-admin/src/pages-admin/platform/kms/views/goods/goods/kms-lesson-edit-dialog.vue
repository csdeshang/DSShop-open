<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="70%" :destroy-on-close="true">
        <el-form :model="formData" :rules="rules" ref="formRef" label-width="120px" v-loading="loading">
            <el-form-item label="商品ID" prop="goods_id">
                <el-input v-model.trim="formData.goods_id" placeholder="请输入商品ID" readonly />
            </el-form-item>

            <el-form-item label="课时名称" prop="lesson_name">
                <el-input v-model.trim="formData.lesson_name" placeholder="请输入课时名称" />
            </el-form-item>

            <el-form-item label="课时类型" prop="lesson_type">
                <el-select v-model="formData.lesson_type" placeholder="请选择课时类型" @change="handleLessonTypeChange">
                    <el-option v-for="item in lesson_type_options" :key="item.value" :label="item.label" :value="item.value" />
                </el-select>
            </el-form-item>

            <el-form-item label="是否免费" prop="is_free">
                <el-radio-group v-model="formData.is_free">
                    <el-radio v-for="item in is_free_options" :key="item.value" :value="item.value">{{ item.label }}</el-radio>
                </el-radio-group>
            </el-form-item>

            <el-form-item label="排序" prop="sort">
                <el-input-number v-model="formData.sort" :min="0" :max="999" placeholder="排序值越小越靠前" />
            </el-form-item>

            <el-form-item label="课时描述" prop="lesson_desc">
                <el-input v-model="formData.lesson_desc" type="textarea" :rows="3" placeholder="请输入课时描述" />
            </el-form-item>

            <template v-if="formData.lesson_type === 1">
                <el-divider content-position="left">视频内容设置</el-divider>
                <el-form-item label="视频URL" prop="video_url">
                    <PickerImage v-model="formData.video_url" :limit="1" type="video" width="200px" height="200px" />
                    <el-input v-model.trim="formData.video_url" placeholder="请输入视频URL" disabled/>
                </el-form-item>
            </template>

            <template v-if="formData.lesson_type === 2">
                <el-divider content-position="left">图文内容设置</el-divider>
                <el-form-item label="图文内容" prop="content">
                    <RichTextEditor v-model="formData.content" />
                </el-form-item>
            </template>

            <template v-if="formData.lesson_type === 3">
                <el-divider content-position="left">音频内容设置</el-divider>
                <el-form-item label="音频URL" prop="audio_url">
                    <PickerImage v-model="formData.audio_url" :limit="1" type="audio" width="200px" height="200px" />
                </el-form-item>
            </template>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" :loading="loading" @click="handleSubmit">确认</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import type { FormInstance } from 'element-plus'
import { reactive, ref, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { createKmsLesson, updateKmsLesson, getKmsLessonDetail } from '@/pages-admin/platform/kms/api/kms/kmsLesson'
import PickerImage from '@/components/attachment/picker-image.vue'
import RichTextEditor from '@/components/editor/index.vue'
import { useEnum } from '@/hooks/useEnum'

const { options: lesson_type_options } = useEnum('default.kms_lesson.lesson_type')
const { options: is_free_options } = useEnum('default.kms_lesson.is_free')

const dialogVisible = ref(false)
const loading = ref(false)
let popTitle: string = ''

const initialFormData = {
    id: '',
    goods_id: '',
    lesson_name: '',
    lesson_desc: '',
    lesson_type: 1,
    video_url: '',
    audio_url: '',
    content: '',
    sort: 1,
    is_free: 0
}

const formData: Record<string, any> = reactive({ ...initialFormData })
const formRef = ref<FormInstance>()

const rules = {
    lesson_name: [
        { required: true, message: '请输入课时名称', trigger: 'blur' },
        { min: 1, max: 255, message: '课时名称长度在1到255个字符', trigger: 'blur' }
    ],
    lesson_type: [
        { required: true, message: '请选择课时类型', trigger: 'change' }
    ],
    lesson_desc: [
        { max: 1000, message: '课时描述不能超过1000个字符', trigger: 'blur' }
    ],
    video_url: [
        {
            required: true,
            message: '请输入视频URL',
            trigger: 'blur',
            validator: (rule: any, value: string, callback: any) => {
                if (formData.lesson_type === 1 && !value) {
                    callback(new Error('视频类型必须输入视频URL'))
                } else {
                    callback()
                }
            }
        }
    ],
    audio_url: [
        {
            required: true,
            message: '请输入音频URL',
            trigger: 'blur',
            validator: (rule: any, value: string, callback: any) => {
                if (formData.lesson_type === 3 && !value) {
                    callback(new Error('音频类型必须输入音频URL'))
                } else {
                    callback()
                }
            }
        }
    ],
    content: [
        {
            required: true,
            message: '请输入图文内容',
            trigger: 'blur',
            validator: (rule: any, value: string, callback: any) => {
                if (formData.lesson_type === 2 && !value) {
                    callback(new Error('图文类型必须输入内容'))
                } else {
                    callback()
                }
            }
        }
    ]
}

const emit = defineEmits(['complete'])

const handleLessonTypeChange = (type: number) => {
    if (type === 1) {
        formData.audio_url = ''
        formData.content = ''
    } else if (type === 2) {
        formData.video_url = ''
        formData.audio_url = ''
    } else if (type === 3) {
        formData.video_url = ''
        formData.content = ''
    }
}

const handleSubmit = async () => {
    if (loading.value || !formRef.value) return

    try {
        await formRef.value.validate()

        const requestFun = formData.id ? updateKmsLesson : createKmsLesson
        loading.value = true

        await requestFun(formData)

        loading.value = false
        dialogVisible.value = false
        emit('complete')
    } catch (error) {
        loading.value = false
        console.error('保存课时失败:', error)
        ElMessage.error('保存失败')
    }
}

const setDialogData = async (row: any = null, goodsId: number = 0) => {
    loading.value = true

    Object.assign(formData, initialFormData)
    popTitle = '添加课时'
    formData.goods_id = goodsId

    if (row) {
        popTitle = '更新课时'
        const data = await (await getKmsLessonDetail(row.id)).data
        Object.keys(formData).forEach((key: string) => {
            if (data[key] !== undefined) formData[key] = data[key]
        })
    }

    loading.value = false
}

watch(dialogVisible, (newVal) => {
    if (!newVal) {
        Object.assign(formData, initialFormData)
        if (formRef.value) {
            formRef.value.clearValidate()
        }
    }
})

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})
</script>

<style scoped>
.dialog-footer {
    text-align: right;
}

.el-divider {
    margin: 20px 0;
}

.el-form-item {
    margin-bottom: 20px;
}
</style>
