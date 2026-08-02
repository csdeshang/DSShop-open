import request, { API_BASE_URLS } from '@/utils/request'


// 用户关注/取消关注博主
export function toggleBloggerFollow(bloggerId: number) {
    return request.patch(`${API_BASE_URLS.USER}/user/blogger-follows/${bloggerId}`)
}
