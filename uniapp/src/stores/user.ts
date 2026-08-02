
import { defineStore } from 'pinia'
import { getToken,setToken,removeToken } from '@/utils/auth'
import { getUserInfoWithRoles } from '@/home/api/user/user'
import { logout } from '@/shared/api/login/login'


interface UserSate {
    userInfo: Record<string, any>
    access_token: string | null
    refresh_token: string | null
    temToken: string | null
}


const useUserStore = defineStore('user', {
    state: (): UserSate => ({
        userInfo: {},
        access_token: getToken('access_token') || null,
        refresh_token: getToken('refresh_token') || null,
        temToken: null
    }),
    getters: {
        isLogin: (state) => !!state.access_token
    },
    actions: {
        getUserInfoWithRoles(isRefresh: boolean = false) {
            if (this.userInfo.id && !isRefresh) {
                return
            }

            // 获取用户信息以及角色信息
            getUserInfoWithRoles().then((res: any) => {
                this.userInfo = res.data;
            }).catch(() => {
                this.logout()
            })
        },
        loginAfter(data: any) {

            this.access_token = data.access_token
            this.refresh_token = data.refresh_token
            this.userInfo = data.userinfo
            
            setToken(data.access_token, 'access_token')
            setToken(data.refresh_token, 'refresh_token')
        },
        logout() {
            // 获取refresh_token用于后端Token失效处理
            const refreshTokenValue = getToken('refresh_token')
            const logoutParams = refreshTokenValue ? { refresh_token: refreshTokenValue } : undefined
            
            // 调用退出登录API
            logout(logoutParams).then(() => {
                // 重置状态
                this.access_token = ''
                this.refresh_token = ''
                this.userInfo = {}
                removeToken('access_token')
                removeToken('refresh_token')
            }).catch(() => {
                // 即使API调用失败也要清理本地状态
                this.access_token = ''
                this.refresh_token = ''
                this.userInfo = {}
                removeToken('access_token')
                removeToken('refresh_token')
            })
        }
    }

})


export default useUserStore