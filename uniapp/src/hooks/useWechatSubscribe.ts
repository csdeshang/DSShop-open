
import { ref } from 'vue'
import { 
    getWechatSubscribeTemplateId, 
    createWechatSubscribeRecord,
    getOfficialSubscribeUrl 
} from '@/api/wechat/wechatSubscribe'

/*
统一的微信订阅Hook
// 使用示例
import { useWechatSubscribe } from '@/hooks/useWechatSubscribe'
const { handleSubscribeOrder, handleOfficialCallback } = useWechatSubscribe()


// 订单订阅
await handleSubscribeOrder('house')

// 在页面中处理公众号回调
onMounted(() => {
    handleOfficialCallback()
})
*/

export function useWechatSubscribe() {
    const loading = ref(false)

    /**
     * 保存订阅记录（统一方法）
     */
    const saveSubscribeRecord = async (keys: string, type: string): Promise<void> => {
        try {
            await createWechatSubscribeRecord({ keys, type })
        } catch (error) {
            console.error('记录订阅结果失败:', error)
        }
    }

    /**
     * 小程序订阅
     */
    const miniSubscribe = async (keys: string): Promise<boolean> => {
        if (loading.value) return false
        loading.value = true

        try {
            // 1. 获取模板ID
            const templateRes = await getWechatSubscribeTemplateId({ keys, type: 'mini' })
            
            if (templateRes.code !== 10000 || !templateRes.data?.length) {
                throw new Error('获取订阅模板失败')
            }

            const templateIds = templateRes.data.slice(0, 3) // 最多3个

            // 2. 调用微信小程序订阅接口
            return new Promise<boolean>((resolve) => {
                uni.requestSubscribeMessage({
                    tmplIds: templateIds,
                    success: async (result) => {
                        // 检查是否有成功的订阅
                        const hasAccepted = templateIds.some((id: string) => (result as any)[id] === 'accept')
                        
                        if (hasAccepted) {
                            // 只有成功时才保存记录
                            await saveSubscribeRecord(keys, 'mini')
                            uni.showToast({ title: '订阅成功', icon: 'success' })
                            resolve(true)
                        } else {
                            uni.showToast({ title: '订阅失败或被拒绝', icon: 'none' })
                            resolve(false)
                        }
                    },
                    fail: (error) => {
                        console.error('小程序订阅失败:', error)
                        uni.showToast({ title: '小程序订阅失败', icon: 'none' })
                        resolve(false)
                    }
                })
            })

        } catch (error) {
            console.error('获取小程序模板失败:', error)
            uni.showToast({ title: '获取模板失败', icon: 'none' })
            return false
        } finally {
            loading.value = false
        }
    }

    /**
     * 公众号(服务号)订阅消息
     * 服务号消息  分为 模板消息 和 订阅消息  , 此处是预留了订阅消息的接口
     * 订阅消息 ： https://developers.weixin.qq.com/doc/service/api/notify/subscribe/api_templatesubscribe.html
     * 模板消息 ： https://developers.weixin.qq.com/doc/service/api/notify/template/api_sendtemplatemessage.html
     * 
     */
    const officialSubscribe = async (keys: string): Promise<boolean> => {
        if (loading.value) return false
        loading.value = true

        try {
            // #ifdef H5
            // 1. 先验证是否有可用的模板
            const templateRes = await getWechatSubscribeTemplateId({ keys, type: 'official' })

            if (templateRes.code !== 10000 || !templateRes.data?.length) {
                throw new Error('获取订阅模板失败')
            }

            // 2. 构建回调URL
            const currentUrl = window.location.href.split('?')[0]
            const redirectUrl = `${currentUrl}?wechat_callback=1&keys=${keys}`

            // 3. 获取授权URL
            const authRes = await getOfficialSubscribeUrl({
                keys,
                redirect_url: redirectUrl,
                scene: 1000
            })

            if (authRes.code !== 10000 || !authRes.data?.auth_url) {
                throw new Error('获取授权链接失败')
            }

            // 4. 跳转到微信授权页面
            uni.showModal({
                title: '订阅消息',
                content: '即将跳转到微信授权页面，请确认订阅消息通知',
                confirmText: '前往授权',
                cancelText: '取消',
                success: (modalRes) => {
                    if (modalRes.confirm) {
                        window.location.href = authRes.data.auth_url
                    }
                }
            })

            return true
            // #endif

            // #ifndef H5
            uni.showToast({ title: '当前环境不支持公众号订阅', icon: 'none' })
            return false
            // #endif

        } catch (error) {
            console.error('公众号订阅失败:', error)
            uni.showToast({ title: '公众号订阅失败', icon: 'none' })
            return false
        } finally {
            loading.value = false
        }
    }

    /**
     * 处理公众号订阅回调
     */
    const handleOfficialCallback = async (): Promise<boolean> => {
        // #ifdef H5
        const urlParams = new URLSearchParams(window.location.search)
        
        if (!urlParams.get('wechat_callback')) {
            return false
        }

        const action = urlParams.get('action') || ''
        const keys = urlParams.get('keys') || ''

        try {
            if (action === 'confirm' && keys) {
                // 用户确认订阅，保存记录
                await saveSubscribeRecord(keys, 'official')
                uni.showToast({ title: '订阅成功', icon: 'success' })
                
                // 清理URL参数
                const cleanUrl = window.location.href.split('?')[0]
                window.history.replaceState({}, '', cleanUrl)
                
                return true
            } else {
                uni.showToast({ title: '已取消订阅', icon: 'none' })
                return false
            }
        } catch (error) {
            console.error('处理订阅回调失败:', error)
            return false
        }
        // #endif

        // #ifndef H5
        return false
        // #endif
    }

    /**
     * 智能订阅
     */
    const smartSubscribe = async (keys: string): Promise<boolean> => {
        // #ifdef MP-WEIXIN
        return await miniSubscribe(keys)
        // #endif

        // #ifdef H5
        // 公众号消息  分为模板消息和订阅消息，此处是预留了订阅消息的接口
        // const ua = navigator.userAgent.toLowerCase()
        // if (ua.includes('micromessenger')) {
        //     return await officialSubscribe(keys)
        // }
        // #endif

        return false
    }

    /**
     * 处理订单订阅
     */
    const handleSubscribeOrder = async (platform: string): Promise<boolean> => {
        const platformKeys: Record<string, string> = {
            house: 'user_order_delivery',
            food: 'user_order_delivery', 
            mall: 'user_order_delivery'
        }

        const keys = platformKeys[platform]
        if (!keys) {
            console.log('不支持的订阅平台')
            return false
        }

        return await smartSubscribe(keys)
    }

    return {
        loading,
        miniSubscribe,
        officialSubscribe,
        smartSubscribe,
        handleOfficialCallback,
        handleSubscribeOrder
    }
} 