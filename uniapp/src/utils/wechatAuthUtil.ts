import { wechatAuth, getOfficialAuthUrl } from '@/shared/api/login/wechatLogin'
import useUserStore from '@/stores/user'

/**
 * 微信授权工具类
 * 处理各种环境下的微信授权逻辑
 */
export default {
    /**
     * 执行微信授权
     * @param {Object} options 授权选项
     * @param {String} options.mode 授权模式: login/bind/getOpenid
     * @param {String} options.scene 授权场景: wechat_official/wechat_mini/wechat_app
     * @param {String} options.redirectUrl 授权成功后的重定向URL
     */
    authorize(options: {
        mode: 'login' | 'bind' | 'getOpenid',
        scene: 'wechat_official' | 'wechat_mini' | 'wechat_app',
        redirect_url?: string
    }) {


        // 根据不同场景处理授权
        if (options.scene === 'wechat_mini') {
            this.miniAppAuth(options)
        } else if (options.scene === 'wechat_official') {
            // 微信浏览器公众号授权 需要跳转授权页面
            this.officialAuth(options)
        } else if (options.scene === 'wechat_app') {
            this.appAuth(options)
        } else {
            // 不支持的环境
            uni.showToast({
                title: '当前环境不支持微信授权',
                icon: 'none'
            });
        }
    },

    /**
     * 小程序授权
     */
    miniAppAuth(options: any) {
        uni.login({
            provider: 'weixin',
            success: async (loginRes: any) => {
                try {
                    const res = await wechatAuth({
                        code: loginRes.code,
                        scene: 'wechat_mini',
                        mode: options.mode,
                    })

                    if (res.code !== 10000) {
                        uni.showToast({
                            title: res.message,
                            icon: 'none'
                        });
                        return res
                    }

                    // 登录模式
                    if (options.mode === 'login') {
                        // 在方法内部获取 store
                        const userStore = useUserStore();
                        userStore.loginAfter(res.data);
                        
                        // 显示登录成功提示
                        uni.showToast({
                            title: '登录成功',
                            icon: 'success'
                        });

                        // 登录成功后跳转到首页
                        setTimeout(() => {
                            uni.reLaunch({
                                url: options.redirect_url
                            });
                        }, 1500);
                    }

                    return res

                } catch (error) {
                    uni.showToast({
                        title: '小程序登录失败',
                        icon: 'none'
                    });
                }
            },
            fail: (err: any) => {
                uni.showToast({
                    title: '小程序登录失败',
                    icon: 'none'
                });
            }
        })
    },

    /**
     * 公众号授权
     */
    async officialAuth(options: any) {



        // 直接通过接口获取公众号网页授权URL（仅用于wechat_official场景）
        const res = await getOfficialAuthUrl({
            mode: options.mode,
            redirect_url: options.redirect_url
        })

        if (res.code === 10000) {
            // 跳转到微信授权页

            window.location.href = res.data
        } else {
            uni.showToast({
                title: res.message,
                icon: 'none'
            });
        }



    },

    /**
     * APP授权
     */
    appAuth(options: any) {
        uni.login({
            provider: 'weixin',
            success: async (loginRes: any) => {
                try {
                    const res = await wechatAuth({
                        code: loginRes.code,
                        scene: 'wechat_app',
                        mode: options.mode,
                    })

                    if (res.code !== 10000) {
                        uni.showToast({
                            title: res.message,
                            icon: 'none'
                        });
                        return res
                    }

                    // 登录模式
                    if (options.mode === 'login') {
                        // 在方法内部获取 store
                        const userStore = useUserStore();
                        userStore.loginAfter(res.data);
                    }

                    return res

                } catch (error) {
                    uni.showToast({
                        title: 'APP登录失败',
                        icon: 'none'
                    });
                }
            },
            fail: (err: any) => {
                uni.showToast({
                    title: 'APP登录失败',
                    icon: 'none'
                });
            }
        })
    },

    /**
     * 处理微信授权回调
     * @param {Object} options 回调参数 (code, state等)
     */
    async handleOfficialCallback(options: {
        code: string,
        mode: 'login' | 'bind' | 'getOpenid',
    }) {
        if (options.code) {
            const res = await wechatAuth({
                code: options.code,
                scene: 'wechat_official',
                mode: options.mode,
            })

            if (res.code !== 10000) {
                uni.showToast({
                    title: res.message,
                    icon: 'none'
                });
                return res
            }

            // 登录模式
            if (options.mode === 'login') {
                // 在方法内部获取 store
                const userStore = useUserStore();
                userStore.loginAfter(res.data);
            }

            // 其他模式返回结果  bind  getOpenid
            return res

        } else {
            uni.showToast({
                title: '微信授权失败',
                icon: 'none'
            });
        }
    }
}
