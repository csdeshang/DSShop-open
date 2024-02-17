<?php
namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
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
 * 控制器
 */
class Showpintuan extends BaseMall
{
    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/pintuan.lang.php');
    }
    
    /**
     * 拼团列表页
     */
    public function index()
    {
        $ppintuan_model = model('ppintuan');
        $condition = array();
        $condition[] = array('pintuan_state','=', 1);
        $condition[] = array('pintuan_starttime','<', TIMESTAMP);
        $condition[] = array('pintuan_end_time','>', TIMESTAMP);
        $cache_key = 'pintuan' . md5(serialize($condition)) . '-' . intval(input('param.page'));
        $result = rcache($cache_key);
        if (empty($result)) {
            $pintuan_list = $ppintuan_model->getPintuanList($condition, 10, 'pintuan_state desc, pintuan_end_time desc');
            foreach ($pintuan_list as $key => $pintuan) {
                $pintuan_list[$key]['pintuan_image'] = goods_cthumb($pintuan['pintuan_image'], 240);
                $pintuan_list[$key]['pintuan_zhe_price'] = round($pintuan['pintuan_goods_price']*$pintuan['pintuan_zhe']/10,2);
                $pintuan_list[$key]['pintuan_url'] = urlencode(config('ds_config.h5_site_url')."/pages/home/storegoodslist/Goodslist?goods_id=".$pintuan['pintuan_goods_id']."&pintuan_id=".$pintuan['pintuan_id']);
            }
            $result['pintuan_list'] = $pintuan_list;
            $result['show_page'] = $ppintuan_model->page_info->render();
            wcache($cache_key, $result);
        }
//        halt($result['pintuan_list']);
        View::assign('pintuan_list', $result['pintuan_list']);
        View::assign('show_page', $result['show_page']);
        // 当前位置导航
        View::assign('nav_link_list', array(array('title' => lang('homepage'), 'link' => url('home/Index/index')),array('title'=>lang('pintuan_list'))));
        //SEO 设置
        $seo = array(
            'html_title'=>config('ds_config.site_name').'-'.lang('pintuan_list'),
            'seo_keywords'=>lang('pintuan_list'),
            'seo_description'=>lang('pintuan_list'),
        );
        $this->_assign_seo($seo);
        
        return View::fetch($this->template_dir.'index');
    }
    
    
    
}