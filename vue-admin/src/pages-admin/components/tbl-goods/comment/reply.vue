<template>
    <el-dialog v-model="dialogVisible" :title="popTitle" width="500px" :destroy-on-close="true">
        <el-form :model="formData" label-width="90px" ref="formRef" :rules="formRules" v-loading="loading">
            <el-form-item label="评论内容" prop="content">
                {{ commentInfo.content }}
            </el-form-item>

            <el-form-item label="回复内容" prop="reply_content">
                <el-input v-model="formData.reply_content" type="textarea" :rows="4" placeholder="请输入回复内容"
                    maxlength="500" show-word-limit class="input-width" />
            </el-form-item>
        </el-form>

        <template #footer>
            <span class="dialog-footer">
                <el-button @click="dialogVisible = false">取消</el-button>
                <el-button type="primary" :loading="loading" @click="handleSubmit">确认回复</el-button>
            </span>
        </template>
    </el-dialog>
</template>

<script lang="ts" setup>
import type { FormInstance } from 'element-plus'
import { computed, reactive, ref } from 'vue'
import { ElMessage } from 'element-plus'
import { replyTblGoodsComment } from '@/pages-admin/main/api/tbl-goods/tblGoodsComment'

const dialogVisible = ref(false)
const loading = ref(false)
const popTitle = '回复评论'

const commentInfo = reactive({
    content: '',
    id: ''
})

const initialFormData = {
    id: '',
    reply_content: '',
}
const formData = reactive({ ...initialFormData })

const formRef = ref<FormInstance>()

const formRules = computed(() => {
    return {
        reply_content: [
            { required: true, message: '请输入回复内容', trigger: 'blur' },
            { min: 2, max: 500, message: '回复内容长度在2到500个字符之间', trigger: 'blur' }
        ]
    }
})

const emit = defineEmits(['complete'])

const handleSubmit = async () => {
    if (loading.value || !formRef.value) return

    try {
        await formRef.value.validate()
        loading.value = true

        const response = await replyTblGoodsComment(formData)

        if (response.code === 10000) {
            ElMessage.success('回复成功')
            dialogVisible.value = false
            emit('complete')
        }
    } catch (error) {
        console.error('回复评论失败:', error)
    } finally {
        loading.value = false
    }
}

const setDialogData = async (row: any = null) => {
    if (!row) return

    loading.value = true

    try {
        Object.assign(formData, initialFormData)
        formData.id = row.id
        commentInfo.content = row.content
        commentInfo.id = row.id
    } finally {
        loading.value = false
    }
}

defineExpose({
    openDialog: () => {
        dialogVisible.value = true
    },
    setDialogData
})
</script>

<style scoped>
.input-width {
    width: 100%;
}
</style>
