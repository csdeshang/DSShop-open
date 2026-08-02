<template>
    <el-cascader v-model="computedValue" :options="options"
        :props="{ multiple: true, checkStrictly: true, emitPath: false, value: 'id', label: 'name' }" clearable
        filterable @change="handleChange" />
    <div class="text-gray-500 ml-2 mt-2">
        已选择 {{ computedValue.length }}/{{ goodsCategorySelectLimit }} 个分类
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed, watch } from 'vue'
import { ElMessage } from 'element-plus'
import { getTblGoodsCategoryTree } from '@/pages-admin/main/api/tbl-goods/tblGoodsCategory'
import { useConfigStore } from '@/stores/modules/config'

const props = defineProps({
    platform: {
        type: String,
        default: ''
    },
    modelValue: {
        type: Array,
        default: () => []
    }
})

const options = ref<{ id: string, name: string }[]>([])

const fetchTblGoodsCategoryTree = async () => {
    try {
        const response = await getTblGoodsCategoryTree({
            platform: props.platform,
        })
        options.value = response.data
    } catch (error) {
        console.error('获取商品分类失败:', error)
    }
}

const emit = defineEmits(['update:modelValue'])

const computedValue = computed({
    get: () => props.modelValue,
    set: (value) => {
        if (value.length <= goodsCategorySelectLimit.value) {
            emit('update:modelValue', value)
        }
    }
})

const configStore = useConfigStore()
const goodsCategorySelectLimit = ref<number>(10)

async function fetchGoodsCategorySelectLimit() {
    const limit = await configStore.fetchConfig('goods:goods_category_select_limit')
    goodsCategorySelectLimit.value = limit ? Number(limit) : 10
}

const handleChange = (value: number[]) => {
    if (value.length > goodsCategorySelectLimit.value) {
        ElMessage.warning(`最多只能选择${goodsCategorySelectLimit.value}个分类`)
        const limitedValue = value.slice(0, goodsCategorySelectLimit.value)
        emit('update:modelValue', limitedValue)
    }
}

watch(() => props.modelValue, (newVal) => {
    if (newVal && newVal.length > goodsCategorySelectLimit.value) {
        ElMessage.warning(`最多只能选择${goodsCategorySelectLimit.value}个分类`)
        const limitedValue = newVal.slice(0, goodsCategorySelectLimit.value)
        emit('update:modelValue', limitedValue)
    }
}, { immediate: true })

onMounted(() => {
    fetchTblGoodsCategoryTree()
    fetchGoodsCategorySelectLimit()
})
</script>
