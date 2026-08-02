import request, { API_BASE_URLS } from '@/utils/request'

/** 获取当前管理员菜单（动态路由） */
export function getCurrentUserMenus() {
    return request.get(`${API_BASE_URLS.ADMIN}/admin/current/menus`)
}

/** 获取当前管理员信息 */
export function getCurrentUserInfo() {
    return request.get(`${API_BASE_URLS.ADMIN}/admin/current/info`)
}

/** 修改当前管理员密码 */
export function editCurrentUserPassword(data: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/admin/current/password`, data)
}

/** 获取枚举字典（公共接口） */
export function getEnumData(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.USER}/enums/data`, { params })
}
