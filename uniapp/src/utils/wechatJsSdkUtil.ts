//https://developers.weixin.qq.com/doc/offiaccount/OA_Web_Apps/JS-SDK.html#2

import weixin from 'weixin-js-sdk'


import { getJsSdkConfig } from '@/shared/api/login/wechatLogin'

/**
 * 微信JSSDK工具类
 * 处理微信公众号中JSSDK的初始化与API调用
 */
export default {
    /**
     * 初始化微信JSSDK
     * @param {String} url 当前页面URL，默认为当前页面地址
     * @param {Array} jsApiList 需要使用的JS接口列表
     * @returns {Promise} 返回初始化结果
     */
    async init(options: {
        url?: string,
        jsApiList?: string[],
    }) {
        try {
            const defaultApiList = [
                'updateAppMessageShareData', // 自定义"分享给朋友"及"分享到QQ"按钮的分享内容
                'updateTimelineShareData', // 自定义"分享到朋友圈"及"分享到QQ空间"按钮的分享内容
                'onMenuShareTimeline', // 旧版分享到朋友圈
                'onMenuShareAppMessage', // 旧版分享给朋友
                'chooseWXPay', // 微信支付
                'scanQRCode', // 扫一扫
                'openLocation', // 使用微信内置地图查看位置
                'getLocation', // 获取地理位置
                'chooseImage', // 拍照或从手机相册中选图
                'previewImage' // 预览图片
            ]

            const currentUrl = options.url ||  (uni.getSystemInfoSync().platform == 'ios' ? uni.getStorageSync('initUrl') : window.location.href.split('#')[0]);

            // 获取JSSDK配置
            const res = await getJsSdkConfig({
                url: currentUrl,
            })

            if (res.code === 10000) {
                const config = res.data
                
                // 配置JSSDK
                weixin.config({
                    debug: false, // 关闭调试模式
                    appId: config.appId,
                    timestamp: config.timestamp,
                    nonceStr: config.nonceStr,
                    signature: config.signature,
                    jsApiList: options.jsApiList || defaultApiList
                })

                // 返回成功初始化的Promise
                return new Promise((resolve) => {
                    weixin.ready(() => {
                        resolve({
                            code: 10000,
                            message: 'JSSDK初始化成功'
                        })
                    })
                })
            } else {
                uni.showToast({
                    title: res.message || 'JSSDK配置获取失败',
                    icon: 'none'
                })
                return {
                    code: res.code,
                    message: res.message
                }
            }
        } catch (error) {
            uni.showToast({
                title: '微信JSSDK初始化失败',
                icon: 'none'
            })
            return {
                code: -1,
                message: '微信JSSDK初始化失败'
            }
        }
    },

    /**
     * 分享到朋友圈
     * @param {Object} options 分享选项
     */
    shareToTimeline(options: {
        title: string,
        link?: string,
        imgUrl: string,
        success?: Function,
        cancel?: Function
    }) {
        const link = options.link || window.location.href
        
        // 使用新版接口
        weixin.updateTimelineShareData({
            title: options.title,
            link: link,
            imgUrl: options.imgUrl,
            success: function() {
                options.success && options.success()
            },
            cancel: function() {
                options.cancel && options.cancel()
            }
        })
        
        // 兼容旧版
        weixin.onMenuShareTimeline({
            title: options.title,
            link: link,
            imgUrl: options.imgUrl,
            success: function() {
                options.success && options.success()
            },
            cancel: function() {
                options.cancel && options.cancel()
            }
        })
    },

    /**
     * 分享给朋友
     * @param {Object} options 分享选项
     */
    shareToFriend(options: {
        title: string,
        desc: string,
        link?: string,
        imgUrl: string,
        success?: Function,
        cancel?: Function
    }) {
        const link = options.link || window.location.href
        
        // 使用新版接口
        weixin.updateAppMessageShareData({
            title: options.title,
            desc: options.desc,
            link: link,
            imgUrl: options.imgUrl,
            success: function() {
                options.success && options.success()
            },
            cancel: function() {
                options.cancel && options.cancel()
            }
        })
        
        // 兼容旧版
        weixin.onMenuShareAppMessage({
            title: options.title,
            desc: options.desc,
            link: link,
            imgUrl: options.imgUrl,
            success: function() {
                options.success && options.success()
            },
            cancel: function() {
                options.cancel && options.cancel()
            }
        })
    },

    /**
     * 扫描二维码
     * @param {Object} options 扫码选项
     */
    scanQRCode(options: {
        needResult?: number, // 0不返回结果，1返回结果
        scanType?: string[], // 可以指定扫二维码还是条形码，默认二者都有
        success?: Function,
        cancel?: Function,
        fail?: Function
    }) {
        weixin.scanQRCode({
            needResult: options.needResult || 1,
            scanType: options.scanType || ['qrCode', 'barCode'],
            success: function(res: any) {
                options.success && options.success(res)
            },
            cancel: function() {
                options.cancel && options.cancel()
            },
            fail: function(res: any) {
                options.fail && options.fail(res)
            }
        })
    },

    /**
     * 微信支付
     * @param {Object} payParams 微信支付参数
     * @param {Function} callback 支付回调
     */
    wxPay(payParams: any, callback: Function) {
        if (typeof weixin.chooseWXPay === 'undefined') {
            uni.showToast({
                title: '当前环境不支持微信支付',
                icon: 'none'
            })
            return
        }
        
        weixin.chooseWXPay({
            timestamp: payParams.timestamp,
            nonceStr: payParams.nonceStr,
            package: payParams.package,
            signType: payParams.signType,
            paySign: payParams.paySign,
            success: function(res: any) {
                callback && callback({
                    code: 10000,
                    message: '支付成功',
                    data: res
                })
            },
            cancel: function() {
                callback && callback({
                    code: 20000,
                    message: '支付取消'
                })
            },
            fail: function(res: any) {
                callback && callback({
                    code: 30000,
                    message: '支付失败',
                    data: res
                })
            }
        })
    }
}
