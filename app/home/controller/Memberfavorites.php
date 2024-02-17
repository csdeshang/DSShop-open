<?php

namespace app\home\controller;
use think\facade\View;
use think\facade\Lang;
use think\facade\Db;
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
class Memberfavorites extends BaseMember
{

    public function initialize()
    {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/' . config('lang.default_lang') . '/memberfavorites.lang.php');
    }

    /**
     * 增加商品收藏
     */
    public function favoritesgoods()
    {
        $fav_id = intval(input('param.fid'));
        if ($fav_id <= 0) {
            echo json_encode(array('done' => false, 'msg' => lang('favorite_collect_fail')));
            die;
        }
        $favorites_model = model('favorites');
        //判断是否已经收藏
        $favorites_info = $favorites_model->getOneFavorites(array(
            'fav_id' => "$fav_id",
            'member_id' => session('member_id')
        ));
        if (!empty($favorites_info)) {
            echo json_encode(array(
                'done' => false, 'msg' => lang('favorite_already_favorite_goods')
            ));
            die;
        }
        //判断商品是否为当前会员所有
        $goods_model = model('goods');
        $goods_info = $goods_model->getGoodsInfoByID($fav_id);
        //添加收藏
        $insert_arr = array();
        $insert_arr['member_id'] = session('member_id');
        $insert_arr['member_name'] = session('member_name');
        $insert_arr['fav_id'] = $fav_id;
        $insert_arr['fav_time'] = TIMESTAMP;
        $result = $favorites_model->addFavorites($insert_arr);
        if ($result) {
            //增加收藏数量
            $goods_model->editGoodsById(array('goods_collect' => Db::raw('goods_collect+1')), $fav_id);
            echo json_encode(array('done' => true, 'msg' => lang('favorite_collect_success')));
            die;
        } else {
            echo json_encode(array('done' => false, 'msg' => lang('favorite_collect_fail')));
            die;
        }
    }


    /**
     * 商品收藏列表
     *
     * @param
     * @return
     */
    public function fglist()
    {
        $favorites_model = model('favorites');
        $show_type = 'favorites_goods_picshowlist'; //默认为图片横向显示
        $show = input('param.show');
        $store_array = array(
            'list' => 'favorites_goods_index', 'pic' => 'favorites_goods_picshowlist'
        );
        if (array_key_exists($show, $store_array))
            $show_type = $store_array[$show];

        $favorites_list = $favorites_model->getGoodsFavoritesList(array('member_id' => session('member_id')), '*', 20);
        View::assign('show_page', $favorites_model->page_info->render());
        $collection_goods_list = array(); //店铺为分组的商品
        if (!empty($favorites_list) && is_array($favorites_list)) {
            $favorites_id = array(); //收藏的商品编号
            foreach ($favorites_list as $key => $favorites) {
                $fav_id = $favorites['fav_id'];
                $favorites_id[] = $favorites['fav_id'];
                $favorites_key[$fav_id] = $key;
            }
            $goods_model = model('goods');
            $field = 'goods_id,goods_name,goods_image,goods_price,evaluation_count,goods_salenum,goods_collect';
            $goods_list = $goods_model->getGoodsList(array(array('goods_id','in', $favorites_id)), $field);
            if (!empty($goods_list) && is_array($goods_list)) {
                foreach ($goods_list as $key => $fav) {
                    $fav_id = $fav['goods_id'];
                    $key = $favorites_key[$fav_id];
                    $favorites_list[$key]['goods'] = $fav;
                }
            }
        }
        $this->setMemberCurMenu('member_favorites');
        $this->setMemberCurItem('fav_goods');

        View::assign('favorites_list', $favorites_list);
        View::assign('collection_goods_list', $collection_goods_list);
        return View::fetch($this->template_dir . $show_type);
    }


    /**
     * 删除收藏
     *
     * @param
     * @return
     */
    public function delfavorites()
    {
        if (!input('param.fav_id') || !input('param.type')) {
            ds_json_encode(10001, lang('param_error'));
        }
        if (!preg_match_all('/^[0-9,]+$/', input('param.fav_id'), $matches)) {
            ds_json_encode(10001, lang('param_error'));
        }
        $fav_id = trim(input('param.fav_id'), ',');
        if (!in_array(input('param.type'), array('goods'))) {
            ds_json_encode(10001, lang('param_error'));
        }
        $favorites_model = model('favorites');
        $fav_arr = explode(',', $fav_id);
        if (!empty($fav_arr) && is_array($fav_arr)) {
            $condition = array();
            $condition[] = array('fav_id','in',$fav_arr);
            $condition[] = array('member_id','=',session('member_id'));
            $favorites_list = $favorites_model->getFavoritesList($condition);
            if (!empty($favorites_list) && is_array($favorites_list)) {
                $fav_arr = array();
                foreach ($favorites_list as $k => $v) {
                    $fav_arr[] = $v['fav_id'];
                }
                $condition = array();
                $condition[] = array('fav_id','in',$fav_arr);
                $condition[] = array('member_id','=',session('member_id'));
                $result = $favorites_model->delFavorites($condition);
                if (!empty($fav_arr) && $result) {
                    //更新收藏数量
                    $goods_model = model('goods');
                    $goods_model->editGoodsById(array('goods_collect' => Db::raw('goods_collect-1')), $fav_arr);
                    ds_json_encode(10000, lang('ds_common_del_succ'));
                }
            } else {
                ds_json_encode(10001, lang('ds_common_del_fail'));
            }
        } else {
            ds_json_encode(10001, lang('ds_common_del_fail'));
        }
    }


    /**
     * 用户中心右边，小导航
     *
     * @param string $menu_type 导航类型
     * @param string $menu_key 当前导航的menu_key
     * @return
     */
    protected function getMemberItemList()
    {
        $menu_array = array(
            array(
                'name' => 'fav_goods', 'text' => lang('ds_member_path_collect_list'),
                'url' => url('Memberfavorites/fglist')
            ),
        );
        return $menu_array;
    }

}
