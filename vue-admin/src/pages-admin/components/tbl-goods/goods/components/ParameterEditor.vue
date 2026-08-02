<template>
  <div class="parameter-editor-container">
    <el-table 
      :data="parameterList" 
      style="width: 100%" 
      border
      :header-cell-style="{ background: '#f5f7fa', color: '#606266' }"
      size="default"
      v-if="parameterList.length > 0"
    >
      <el-table-column label="参数名称" min-width="120">
        <template #default="scope">
          <el-input 
            v-model="scope.row.name" 
            @blur="updateParameter" 
            placeholder="请输入参数名称" 
            clearable 
            maxlength="50"
            :class="{ 'is-error': scope.row.showNameError }"
          />
          <div v-if="scope.row.showNameError" class="error-tip">参数名称不能为空</div>
        </template>
      </el-table-column>

      <el-table-column label="参数值" min-width="180">
        <template #default="scope">
          <el-input 
            v-model="scope.row.value" 
            @blur="updateParameter" 
            placeholder="请输入参数值" 
            clearable 
            maxlength="200" 
          />
        </template>
      </el-table-column>

      <el-table-column label="排序" width="150">
        <template #default="scope">
          <el-input-number 
            v-model="scope.row.sort" 
            @change="updateParameter" 
            :min="0" 
            :max="999"
            controls-position="right" 
            size="small" 
          />
        </template>
      </el-table-column>

      <el-table-column label="操作" width="150" align="center">
        <template #default="scope">
          <div class="operation-buttons">
            <el-button 
              type="danger" 
              link 
              @click="deleteParameter(scope.$index)" 
              icon="Delete"
            >
              删除
            </el-button>
          </div>
        </template>
      </el-table-column>
    </el-table>

    <div class="empty-tip" v-if="parameterList.length === 0">
      <el-empty description="暂无参数" />
    </div>

    <div class="action-bar mt-4 flex justify-between">
      <el-button type="primary" @click="addParameter" icon="Plus">
        添加参数
      </el-button>

      <div>
        <el-button @click="sortParameters" v-if="parameterList.length > 1" icon="Sort">
          按排序值排序
        </el-button>
        <el-button @click="batchClear" v-if="hasContent" icon="Delete" type="warning">
          清空全部
        </el-button>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import { ref, computed } from 'vue'
import { ElMessageBox, ElMessage } from 'element-plus'

interface Parameter {
  name: string
  value: string
  sort: number
  showNameError?: boolean
}

const props = defineProps({
  modelValue: {
    type: Array as () => Parameter[],
    default: () => []
  }
})

const emit = defineEmits(['update:modelValue'])

const parameterList = computed({
  get: () => {
    if (!props.modelValue) {
      return []
    }

    return [...props.modelValue].sort((a, b) => {
      const sortA = Number(a.sort) || 0
      const sortB = Number(b.sort) || 0
      return sortA - sortB
    }).map(param => ({
      ...param,
      showNameError: false
    } as Parameter))
  },
  set: (value) => {
    const cleanValue = value.map(({ name, value, sort }) => ({ name, value, sort }))
    emit('update:modelValue', cleanValue)
  }
})

const hasContent = computed(() => {
  return parameterList.value.length > 0 && parameterList.value.some(param => param.name || param.value)
})

const createEmptyParameter = (sort = 0): Parameter => {
  return { name: '', value: '', sort, showNameError: false }
}

const addParameter = () => {
  const maxSort = parameterList.value.length > 0 
    ? Math.max(0, ...parameterList.value.map(p => Number(p.sort) || 0)) 
    : 0
  const newParameter = createEmptyParameter(maxSort + 10)
  parameterList.value = [...parameterList.value, newParameter]
}

const updateParameter = () => {
  const updatedParameters = parameterList.value.map(param => ({
    name: param.name || '',
    value: param.value || '',
    sort: Number(param.sort) || 0
  }))

  emit('update:modelValue', updatedParameters)
}

const deleteParameter = (index: number) => {
  const updatedParameters = [...parameterList.value]
  updatedParameters.splice(index, 1)
  parameterList.value = updatedParameters
}

const batchClear = () => {
  ElMessageBox.confirm('确定要清空所有参数吗?', '提示', {
    confirmButtonText: '确定',
    cancelButtonText: '取消',
    type: 'warning'
  }).then(() => {
    parameterList.value = []
    ElMessage.success('已清空所有参数')
  }).catch(() => {})
}

const sortParameters = () => {
  parameterList.value = [...parameterList.value].sort((a, b) => {
    const sortA = Number(a.sort) || 0
    const sortB = Number(b.sort) || 0
    return sortA - sortB
  })
  ElMessage.success('已按排序值重新排列')
}
</script>

<style scoped>
.parameter-editor-container {
  width: 100%;
}

.error-tip {
  color: #F56C6C;
  font-size: 12px;
  line-height: 1;
  padding-top: 4px;
}

.is-error {
  border-color: #F56C6C;
}

.action-bar {
  margin-top: 16px;
}

.operation-buttons {
  display: flex;
  justify-content: center;
}

.empty-tip {
  padding: 30px 0;
  text-align: center;
}
</style>
