<template>
  <div>
    <el-card shadow="never" class="mb-[10px]">
      <el-form :model="searchParams" inline>
        <el-form-item label="打印机名称">
          <el-input v-model="searchParams.printer_name" placeholder="打印机名称" clearable />
        </el-form-item>
        <el-form-item label="打印机编号">
          <el-input v-model="searchParams.printer_sn" placeholder="打印机编号" clearable />
        </el-form-item>
        <el-form-item label="服务商">
          <el-select v-model="searchParams.printer_provider" placeholder="选择服务商" clearable style="width: 120px">
            <el-option v-for="item in printer_provider_options" :key="item.value" :label="item.label" :value="item.value" />
          </el-select>
        </el-form-item>
        <el-form-item>
          <el-button type="primary" @click="resetPage">查询</el-button>
          <el-button @click="resetSearchParams">重置</el-button>
        </el-form-item>
      </el-form>
      <el-button type="primary" @click="handleAdd">添加打印机</el-button>
      <el-button type="success" @click="handlePrintOrder">打印订单</el-button>
    </el-card>

    <el-card shadow="never">
      <el-table :data="tableData.data" size="large" v-loading="tableData.loading">
        <el-table-column label="ID" prop="id" min-width="60" />
        <el-table-column label="打印机名称" prop="printer_name" min-width="120" />
        <el-table-column label="打印机编号" prop="printer_sn" min-width="120" />
        <el-table-column label="服务商" prop="printer_provider_desc" min-width="80" />
        <el-table-column label="绑定状态" prop="printer_status" min-width="90">
          <template #default="{ row }">
            <el-tag :type="row.printer_status === 1 ? 'success' : 'danger'">
              {{ row.printer_status === 1 ? '已绑定' : '未绑定' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="启用状态" prop="is_enabled" min-width="90">
          <template #default="{ row }">
            <el-tag :type="row.is_enabled === 1 ? 'success' : 'info'">
              {{ row.is_enabled === 1 ? '启用' : '禁用' }}
            </el-tag>
          </template>
        </el-table-column>
        <el-table-column label="创建时间" prop="create_at" min-width="160" />
        <el-table-column label="操作" align="right" fixed="right" width="380">
          <template #default="{ row }">
            <el-button type="success" link v-if="row.printer_status !== 1" @click="handleBind(row)">绑定</el-button>
            <el-button type="warning" link v-if="row.printer_status === 1" @click="handleUnbind(row)">解绑</el-button>
            <el-button type="info" link @click="handleCheckStatus(row)" :loading="row.statusLoading">检测状态</el-button>
            <el-button type="primary" link @click="handleEdit(row)">编辑</el-button>
            <el-button type="danger" link @click="handleDelete(row)">删除</el-button>
            <el-button type="warning" link @click="handleLogs(row)">打印记录</el-button>
          </template>
        </el-table-column>
      </el-table>

      <div class="flex justify-end mt-[20px]">
        <el-pagination
          v-model:current-page="tableData.page_current"
          v-model:page-size="tableData.page_size"
          layout="total, sizes, prev, pager, next, jumper"
          :total="tableData.total"
          @size-change="getTableList()"
          @current-change="getTableList"
        />
      </div>
    </el-card>

    <PrinterEdit ref="printerEditRef" @complete="getTableList()" />
    <PrintOrderDialog ref="printOrderRef" @complete="getTableList()" />
    <PrinterLogsDialog ref="logsDialogRef" @complete="getTableList()" />
  </div>
</template>

<script lang="ts" setup>
import { reactive, ref } from 'vue'
import { ElMessage, ElMessageBox } from 'element-plus'

import { useEnum } from '@/hooks/useEnum'
import { usePagination } from '@/hooks/usePagination'
import {
  bindTblStorePrinter,
  deleteTblStorePrinter,
  getTblStorePrinterPages,
  getTblStorePrinterStatus,
  unbindTblStorePrinter
} from '@/pages-admin/main/api/tbl-store/tblStorePrinter'

import PrinterEdit from './edit.vue'
import PrintOrderDialog from './print-order.vue'
import PrinterLogsDialog from './logs-dialog.vue'

defineOptions({
  name: 'PrinterManagement'
})

const searchParams = reactive({
  printer_name: '',
  printer_sn: '',
  printer_provider: ''
})

const {
  tableData,
  getTableList,
  resetSearchParams,
  resetPage
} = usePagination({
  page_current: 1,
  page_size: 10,
  requestFun: getTblStorePrinterPages,
  searchParams: searchParams
})

getTableList()

const { options: printer_provider_options } = useEnum('default.tbl_store_printer.printer_provider')

const printerEditRef = ref()

const handleAdd = () => {
  printerEditRef.value?.setDialogData({})
  printerEditRef.value?.openDialog()
}

const handleEdit = (row: any) => {
  printerEditRef.value?.setDialogData(row)
  printerEditRef.value?.openDialog()
}

const printOrderRef = ref()

const handlePrintOrder = () => {
  printOrderRef.value?.openDialog()
}

const logsDialogRef = ref()

const handleBind = async (row: any) => {
  try {
    await ElMessageBox.confirm('确定要绑定这台打印机吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await bindTblStorePrinter(row.id)
    ElMessage.success('绑定成功')
    getTableList()
  } catch (error) {
    console.error('绑定失败:', error)
  }
}

const handleUnbind = async (row: any) => {
  try {
    await ElMessageBox.confirm('确定要解绑这台打印机吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await unbindTblStorePrinter(row.id)
    ElMessage.success('解绑成功')
    getTableList()
  } catch (error) {
    console.error('解绑失败:', error)
  }
}

const handleDelete = async (row: any) => {
  try {
    await ElMessageBox.confirm('确定要删除这个打印机吗？', '提示', {
      confirmButtonText: '确定',
      cancelButtonText: '取消',
      type: 'warning'
    })
    await deleteTblStorePrinter(row.id)
    ElMessage.success('删除成功')
    getTableList()
  } catch (error) {
    console.error('删除失败:', error)
  }
}

const handleCheckStatus = async (row: any) => {
  try {
    row.statusLoading = true
    const res = await getTblStorePrinterStatus(row.id)
    if (res.code === 10000) {
      ElMessage.success(`打印机状态: ${res.data.message}`)
    }
  } catch (error) {
    console.error('查询状态失败:', error)
  } finally {
    row.statusLoading = false
  }
}

const handleLogs = (row: any) => {
  logsDialogRef.value?.setDialogData({ printer_id: row.id, printer_name: row.printer_name })
  logsDialogRef.value?.openDialog()
}
</script>
