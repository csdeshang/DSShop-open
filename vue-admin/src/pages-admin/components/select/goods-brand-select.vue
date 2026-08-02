<template>
    <el-cascader v-model="computedValue" :options="options"
        :props="{ multiple: props.multiple, checkStrictly: true, emitPath: false, value: 'id', label: 'name' }" clearable
        filterable />
</template>

<script lang="ts" setup>
import { ref, onMounted, computed, watch } from 'vue'
import { getTblGoodsBrandTree } from '@/pages-admin/main/api/tbl-goods/tblGoodsBrand'

const props = defineProps({
    platform: {
        type: String,
        default: ''
    },
    modelValue: {
        type: [String, Number, Array],
        default: 0
    },
    multiple: {
        type: Boolean,
        default: false
    },
})

const options = ref<{ id: string, name: string }[]>([])

const fetchBrandList = async () => {
    try {
        const response = await getTblGoodsBrandTree({
            platform: props.platform,
        })
        options.value = response.data
    } catch (error) {
        console.error('获取品牌列表失败:', error)
    }
}

const emit = defineEmits(['update:modelValue'])

const computedValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
})

watch(() => props.modelValue, (newValue) => {
    if (newValue === null || newValue === 0) {
        computedValue.value = 0
    }
})

onMounted(() => {
    fetchBrandList()
})
</script>
