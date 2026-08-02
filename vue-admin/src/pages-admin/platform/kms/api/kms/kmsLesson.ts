import request, { API_BASE_URLS } from '@/utils/request'

export function getKmsLessonList(params: Record<string, any>) {
    return request.get(`${API_BASE_URLS.ADMIN}/kms/lessons/list`, { params })
}

export function getKmsLessonDetail(lesson_id: number) {
    return request.get(`${API_BASE_URLS.ADMIN}/kms/lessons/${lesson_id}`)
}

export function createKmsLesson(params: Record<string, any>) {
    return request.post(`${API_BASE_URLS.ADMIN}/kms/lessons`, params)
}

export function updateKmsLesson(params: Record<string, any>) {
    return request.put(`${API_BASE_URLS.ADMIN}/kms/lessons/${params.id}`, params)
}

export function deleteKmsLesson(lesson_id: number) {
    return request.delete(`${API_BASE_URLS.ADMIN}/kms/lessons/${lesson_id}`)
}
