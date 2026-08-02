<!-- 抽屉内为详情；对话框为驳回 / 完成开票 / 作废表单 -->
<template>
  <div>
  <el-drawer v-model="dialogVisible" title="开票申请详情" size="60%">
    <div v-loading="loading" class="ds-detail">
      <div class="section-hd">
        <div class="section-hd-top">
          <div class="section-hd-top-right">
            <el-button
              v-if="info.store_invoice_actions?.includes('mark_processing')"
              type="primary"
              @click="onMarkProcessing"
            >
              标记处理中
            </el-button>
            <el-button v-if="info.store_invoice_actions?.includes('reject')" type="danger" @click="rejectOpen = true">
              驳回
            </el-button>
            <el-button v-if="info.store_invoice_actions?.includes('issue')" type="success" @click="issueOpen = true">
              完成开票
            </el-button>
            <el-button v-if="info.store_invoice_actions?.includes('void')" type="warning" @click="voidOpen = true">
              作废
            </el-button>
          </div>
        </div>
        <div class="section-hd-center style-1">
          <el-row :gutter="10">
            <el-col :span="6">
              <div class="item">
                <div class="item-title">申请ID</div>
                <div class="item-content">{{ info.id }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="item">
                <div class="item-title">订单号</div>
                <div class="item-content">{{ info.order_sn }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="item">
                <div class="item-title">状态</div>
                <div class="item-content">{{ info.invoice_status_desc }}</div>
              </div>
            </el-col>
            <el-col :span="6">
              <div class="item">
                <div class="item-title">金额</div>
                <div class="item-content">¥{{ info.invoice_amount }}</div>
              </div>
            </el-col>
          </el-row>
        </div>
      </div>

      <div class="section-bd mt-4">
        <el-tabs v-model="tab">
          <el-tab-pane label="基本信息" name="base">
            <el-descriptions :column="2" border>
              <el-descriptions-item label="抬头类型">{{ info.invoice_type === 2 ? '企业' : '个人' }}</el-descriptions-item>
              <el-descriptions-item label="发票种类">{{ info.invoice_kind === 2 ? '增值税专票' : '普通发票' }}</el-descriptions-item>
              <el-descriptions-item label="发票抬头" :span="2">{{ info.invoice_title }}</el-descriptions-item>
              <el-descriptions-item label="税号" v-if="info.tax_number">{{ info.tax_number }}</el-descriptions-item>
              <el-descriptions-item label="收票邮箱" v-if="info.receiver_email">{{ info.receiver_email }}</el-descriptions-item>
              <el-descriptions-item label="收票手机" v-if="info.receiver_mobile">{{ info.receiver_mobile }}</el-descriptions-item>
              <el-descriptions-item label="申请说明" :span="2" v-if="info.apply_remark">{{ info.apply_remark }}</el-descriptions-item>
              <el-descriptions-item label="发票号码" v-if="info.invoice_no">{{ info.invoice_no }}</el-descriptions-item>
              <el-descriptions-item label="外部流水号" v-if="info.out_invoice_no">{{ info.out_invoice_no }}</el-descriptions-item>
              <el-descriptions-item label="下载地址" :span="2" v-if="info.invoice_file_url">{{ info.invoice_file_url }}</el-descriptions-item>
              <el-descriptions-item label="完成说明" :span="2" v-if="info.issue_remark">{{ info.issue_remark }}</el-descriptions-item>
              <el-descriptions-item label="开票完成时间" v-if="info.issue_time">{{ info.issue_time }}</el-descriptions-item>
              <el-descriptions-item label="驳回原因" :span="2" v-if="info.reject_reason">{{ info.reject_reason }}</el-descriptions-item>
              <el-descriptions-item label="驳回时间" v-if="info.reject_time">{{ info.reject_time }}</el-descriptions-item>
              <el-descriptions-item label="作废原因" :span="2" v-if="info.void_reason">{{ info.void_reason }}</el-descriptions-item>
              <el-descriptions-item label="作废时间" v-if="info.void_time">{{ info.void_time }}</el-descriptions-item>
            </el-descriptions>
          </el-tab-pane>
          <el-tab-pane label="处理记录" name="log">
            <el-timeline v-if="(info.orderInvoiceLogList || []).length">
              <el-timeline-item v-for="(log, idx) in info.orderInvoiceLogList" :key="idx" :timestamp="log.create_at || ''">
                <div>{{ log.invoice_status_desc }}</div>
                <div class="text-gray-500 text-sm">{{ log.message }}</div>
              </el-timeline-item>
            </el-timeline>
            <el-empty v-else description="暂无记录" />
          </el-tab-pane>
        </el-tabs>
      </div>
    </div>
  </el-drawer>

  <el-dialog v-model="rejectOpen" title="驳回开票申请" width="480px" @closed="rejectReason = ''">
    <el-input v-model="rejectReason" type="textarea" :rows="4" maxlength="255" show-word-limit placeholder="请填写驳回原因" />
    <template #footer>
      <el-button @click="rejectOpen = false">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="submitReject">确定</el-button>
    </template>
  </el-dialog>

  <el-dialog v-model="voidOpen" title="作废开票" width="480px" @closed="voidReason = ''">
    <p class="text-gray-500 text-sm mb-2">仅「已开票」可作废；作废后订单开票状态为已作废，买家可再次提交开票申请。</p>
    <el-input v-model="voidReason" type="textarea" :rows="4" maxlength="255" show-word-limit placeholder="请填写作废原因" />
    <template #footer>
      <el-button @click="voidOpen = false">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="submitVoid">确定</el-button>
    </template>
  </el-dialog>

  <el-dialog v-model="issueOpen" title="完成开票" width="520px" @closed="resetIssueForm">
    <el-form label-width="100px">
      <el-form-item label="发票号码" required>
        <el-input v-model="issueForm.invoice_no" maxlength="64" show-word-limit placeholder="必填" />
      </el-form-item>
      <el-form-item label="外部流水号">
        <el-input v-model="issueForm.out_invoice_no" maxlength="64" placeholder="选填" />
      </el-form-item>
      <el-form-item label="下载地址">
        <el-input v-model="issueForm.invoice_file_url" maxlength="512" placeholder="电子票链接或路径，选填" />
      </el-form-item>
      <el-form-item label="完成说明">
        <el-input
          v-model="issueForm.issue_remark"
          type="textarea"
          :rows="3"
          maxlength="255"
          show-word-limit
          placeholder="选填，如：已发送至收票邮箱"
        />
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="issueOpen = false">取消</el-button>
      <el-button type="primary" :loading="submitting" @click="submitIssue">确定</el-button>
    </template>
  </el-dialog>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'
import {
  getTblOrderInvoiceInfo,
  issueTblOrderInvoice,
  markTblOrderInvoiceProcessing,
  rejectTblOrderInvoice,
  voidTblOrderInvoice
} from '@/pages-admin/main/api/tbl-order/tblOrderInvoice'

const emit = defineEmits<{ (e: 'success'): void }>()

const dialogVisible = ref(false)
const loading = ref(false)
const submitting = ref(false)
const tab = ref('base')
const rejectOpen = ref(false)
const voidOpen = ref(false)
const issueOpen = ref(false)
const rejectReason = ref('')
const voidReason = ref('')

const info = reactive<Record<string, any>>({
  id: 0,
  order_sn: '',
  invoice_status_desc: '',
  invoice_amount: '',
  store_invoice_actions: [],
  orderInvoiceLogList: []
})

const issueForm = reactive({
  invoice_no: '',
  out_invoice_no: '',
  invoice_file_url: '',
  issue_remark: ''
})

const load = async (id: number) => {
  if (!id) return
  loading.value = true
  try {
    const res = await getTblOrderInvoiceInfo(id)
    if (res.code === 10000 && res.data) {
      Object.assign(info, res.data)
    } else {
      ElMessage.error(res.message || '加载失败')
    }
  } finally {
    loading.value = false
  }
}

const setDialogData = async (row: { id: number }) => {
  await load(row.id)
}

const openDialog = () => {
  dialogVisible.value = true
}

defineExpose({ setDialogData, openDialog })

const reload = async () => {
  if (info.id) await load(info.id)
  emit('success')
}

const onMarkProcessing = async () => {
  try {
    await ElMessageBox.confirm('确认将本申请标记为「处理中」？', '提示', { type: 'warning' })
    submitting.value = true
    const res = await markTblOrderInvoiceProcessing(info.id)
    submitting.value = false
    if (res.code === 10000) {
      ElMessage.success('操作成功')
      await reload()
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } catch (e) {
    submitting.value = false
    if (e !== 'cancel') ElMessage.error('操作失败')
  }
}

const submitVoid = async () => {
  if (!voidReason.value.trim()) {
    ElMessage.warning('请填写作废原因')
    return
  }
  submitting.value = true
  try {
    const res = await voidTblOrderInvoice(info.id, { void_reason: voidReason.value.trim() })
    if (res.code === 10000) {
      ElMessage.success('已作废')
      voidOpen.value = false
      await reload()
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } finally {
    submitting.value = false
  }
}

const submitReject = async () => {
  if (!rejectReason.value.trim()) {
    ElMessage.warning('请填写驳回原因')
    return
  }
  submitting.value = true
  try {
    const res = await rejectTblOrderInvoice({ invoice_id: info.id, reject_reason: rejectReason.value.trim() })
    if (res.code === 10000) {
      ElMessage.success('已驳回')
      rejectOpen.value = false
      await reload()
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } finally {
    submitting.value = false
  }
}

const resetIssueForm = () => {
  issueForm.invoice_no = ''
  issueForm.out_invoice_no = ''
  issueForm.invoice_file_url = ''
  issueForm.issue_remark = ''
}

const submitIssue = async () => {
  if (!issueForm.invoice_no.trim()) {
    ElMessage.warning('请填写发票号码')
    return
  }
  submitting.value = true
  try {
    const res = await issueTblOrderInvoice(info.id, {
      invoice_no: issueForm.invoice_no.trim(),
      out_invoice_no: issueForm.out_invoice_no.trim() || undefined,
      invoice_file_url: issueForm.invoice_file_url.trim() || undefined,
      issue_remark: issueForm.issue_remark.trim() || undefined
    })
    if (res.code === 10000) {
      ElMessage.success('开票完成')
      issueOpen.value = false
      await reload()
    } else {
      ElMessage.error(res.message || '操作失败')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<style scoped>
.mt-4 {
  margin-top: 16px;
}
</style>
