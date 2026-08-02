import request, { API_BASE_URLS } from '@/utils/request'



export function getKmsLessonList(params: any) {
    return request.get(`${API_BASE_URLS.USER}/kms/lessons`, params)
}

export function getKmsLessonDetail(lessonId: number) {
    return request.get(`${API_BASE_URLS.USER}/kms/lessons/${lessonId}`)
}