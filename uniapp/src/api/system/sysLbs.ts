import request, { API_BASE_URLS } from '@/utils/request'


// 通过经纬度获取地址详细信息
export function getAddressByLngLat(params: any) {
    return request.get(`${API_BASE_URLS.USER}/system/lbs/address-by-lng-lat`, params);
}

// 通过IP获取经纬度
export function getCoordsByIp() {
    return request.get(`${API_BASE_URLS.USER}/system/lbs/coords-by-ip`);
}


// 获取周边地址列表
export function getAroundAddressList(params: any) {
    return request.post(`${API_BASE_URLS.USER}/system/lbs/around-address-list`, params);
}


// 搜索提示地址列表(关键词提示地址)
// export function getSuggAddressList(params: any) {
//     return request.post(`${API_BASE_URLS.USER}/system/lbs/getSuggAddressList`, params);
// }

// 搜索城市地址列表
export function getCityAddressList(params: any) {
    return request.post(`${API_BASE_URLS.USER}/system/lbs/city-address-list`, params);
}




// 获取城市列表
export function getCityList() {
    return request.get(`${API_BASE_URLS.USER}/system/lbs/cities`);
}


// 根据关键词获取城市列表
export function getCityListByKeyword(params: any) {
    return request.get(`${API_BASE_URLS.USER}/system/lbs/city-search`, params);
}
