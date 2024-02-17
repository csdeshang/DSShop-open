<?php

/**
 * 公共用户可以访问的类(不需要登录)
 */

namespace app\home\controller;
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
class BaseMall extends BaseHome {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'home/lang/'.config('lang.default_lang').'/basemall.lang.php');
        if(request()->isMobile() && config('ds_config.h5_force_redirect')){
            $this->isHomeUrl();
        }
        $this->template_dir = 'default/mall/'.  strtolower(request()->controller()).'/';
    }

    /**
     * 手机端访问自动跳转
     */
    protected function isHomeUrl(){
        $controller = request()->controller();//取控制器名
        $action = request()->action();//取方法名
        $input = request()->param();//取参数
        $param = http_build_query($input);//将参数转换成链接形式

        if ($controller == 'Goods' && $action == 'index'){//商品详情
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/goodsdetail/Goodsdetail?'.$param);
            exit;
        }elseif ($controller == 'Showgroupbuy' && $action == 'index'){//抢购列表
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/groupbuy/GroupBuyList');
            exit;
        }elseif ($controller == 'Search' && $action == 'index'){//搜索
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/goodslist/Goodslist');
            exit;
        }elseif ($controller == 'Showgroupbuy' && $action == 'groupbuy_detail'){//抢购详情
            $goods_id = model('groupbuy')->getGroupbuyOnlineInfo(array(array('groupbuy_id' ,'=', $input['group_id'])))['goods_id'];
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/goodsdetail/Goodsdetail?goods_id='.$goods_id);
            exit;
        }elseif ($controller == 'Store' && $action == 'goods_all'){//店铺商品列表
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/storegoodslist/Goodslist?'.$param);
            exit;
        }elseif ($controller == 'Category' && $action == 'goods'){//分类
            header('Location:'.config('ds_config.h5_site_url').'/pages/home/goodsclass/Goodsclass');
            exit;
        }else {
            header('Location:'.config('ds_config.h5_site_url'));exit;//其它页面跳转到首页
        }
    }
}

?>
