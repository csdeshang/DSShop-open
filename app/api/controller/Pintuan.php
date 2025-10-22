<?php

namespace app\api\controller;
/**
 * ============================================================================
 * DSShop单店铺商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 拼团控制器
 */
class Pintuan extends MobileMall {
    public function initialize() {
        parent::initialize();
    }
    
    /**
     * @api {POST} api/Pintuan/index 获取拼团列表
     * @apiVersion 3.0.6
     * @apiGroup Pintuan
     *
     * @apiHeader {String} X-DS-KEY 用户授权token
     * 
     * @apiParam {Int} page 页码
     * @apiParam {Int} per_page 每页数量
     *
     * @apiSuccess {String} code 返回码,10000为成功
     * @apiSuccess {String} message  返回消息
     * @apiSuccess {Object} result  返回数据
     * @apiSuccess {Object[]} result.pintuan_list  拼团列表 （返回字段参考ppintuan表）
     * @apiSuccess {Int} result.page_total  总页数
     * @apiSuccess {Boolean} result.hasmore  是否有更多 true是false否
     */
    public function index()
    {
        $ppintuan_model = model('ppintuan');
        $condition = array();
        $condition[]  = array('pintuan_state','=',1);
        $condition[]  = array('pintuan_starttime','<',TIMESTAMP);
        $condition[]  = array('pintuan_end_time','>',TIMESTAMP);
        $cache_key = 'api-pintuan' . md5(serialize($condition)) . '-' . intval(input('param.page'));
        $result = rcache($cache_key);
        if (empty($result)) {
            $pintuan_list = $ppintuan_model->getPintuanList($condition, 10, 'pintuan_state desc, pintuan_end_time desc');
            foreach ($pintuan_list as $key => $pintuan) {
                $pintuan_list[$key]['pintuan_image'] = goods_cthumb($pintuan['pintuan_image'], 240);
                $pintuan_list[$key]['pintuan_zhe_price'] = round($pintuan['pintuan_goods_price'] * $pintuan['pintuan_zhe'] / 10, 2);
            }
            $page_count = $ppintuan_model->page_info;
            $result = array_merge(array('pintuan_list' => $pintuan_list,), mobile_page($page_count));
            wcache($cache_key, $result);
        }
        ds_json_encode(10000, '', $result);
    }

}