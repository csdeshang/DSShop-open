import { ref } from 'vue'
import type { Ref } from 'vue'
import { getEditablePageInfo } from '@/home/api/editable/editable'

interface EditablePage {
    id: number
    platform: string
    type: string
    page_config: Record<string, any>
}

export function useEditable() {
    const editablePage: Ref<EditablePage | null> = ref(null)
    const loading = ref(false)


    // 加载配置
    const loadConfig = async (params: {
        id?: number
        type?: string
        platform?: string
        device_type?: string
    }) => {
        loading.value = true


        try {
            const response = await getEditablePageInfo(params)
            editablePage.value = {
                id: response.data.id,
                platform: response.data.platform,
                type: response.data.type,
                page_config: JSON.parse(response.data.page_config)
            }
        } catch (err) {

            throw err
        } finally {
            loading.value = false
        }
    }



    return {
        editablePage,
        loading,
        loadConfig,
    }
}