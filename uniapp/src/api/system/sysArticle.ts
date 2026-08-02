import request, { API_BASE_URLS } from '@/utils/request'


// 获取系统文章分页列表
export function getSysArticlePages(params: any) {
  return request.get(`${API_BASE_URLS.USER}/system/articles/pages`, params)
}

// 获取系统文章
export function getSysArticleList(params: any) {
  return request.get(`${API_BASE_URLS.USER}/system/articles/list`, params)
}

// 获取系统文章详情
export function getSysArticleDetail(id: number) {
  return request.get(`${API_BASE_URLS.USER}/system/articles/${id}`)
}


// 获取文章分类树形列表
export function getSysArticleCategoryTree(params: any) {
  return request.get(`${API_BASE_URLS.USER}/system/article-categories/tree`, params)
}




