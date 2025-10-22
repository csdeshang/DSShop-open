<?php

namespace app\admin\controller;
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
class Account extends AdminControl {

    public function initialize() {
        parent::initialize();
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/account.lang.php');
        Lang::load(base_path() . 'admin/lang/'.config('lang.default_lang').'/config.lang.php');
    }

    /**
     * QQ互联
     */
    function qq() {
        $config_model = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);

            //输出子菜单
            $this->setAdminCurItem('qq');
            return View::fetch('qq');
        } else {
            $update_array = array();
            $update_array['qq_isuse'] = input('post.qq_isuse');
            $update_array['qq_appid'] = input('post.qq_appid');
            $update_array['qq_appkey'] = input('post.qq_appkey');

            $account_validate = ds_validate('account');
            if (!$account_validate->scene('qq')->check($update_array)){
                $this->error($account_validate->getError());
            }

            $result = $config_model->editConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('qq_settings'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('qq_settings'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * sina微博设置
     */
    public function sina() {
        $config_model = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);

            //输出子菜单
            $this->setAdminCurItem('sina');
            return View::fetch('sina');
        } else {
            $update_array = array();
            $update_array['sina_isuse'] = input('post.sina_isuse');
            $update_array['sina_wb_akey'] = input('post.sina_wb_akey');
            $update_array['sina_wb_skey'] = input('post.sina_wb_skey');
            //定义验证规则
            $account_validate = ds_validate('account');
            if (!$account_validate->scene('sina')->check($update_array)){
                $this->error($account_validate->getError());
            }
            $result = $config_model->editConfig($update_array);
            if ($result === true) {
                $this->log(lang('ds_edit').lang('sina_settings'), 1);
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->log(lang('ds_edit').lang('sina_settings'), 0);
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 微信登录设置
     */
    public function wx() {
        $config_model = model('config');
        if (!request()->isPost()) {
            $list_config = rkcache('config', true);
            View::assign('list_config', $list_config);
            //输出子菜单
            $this->setAdminCurItem('wx');
            return View::fetch('wx');
        } else {
            $update_array = array();
            $update_array['weixin_pc_isuse'] = input('post.weixin_pc_isuse');
            $update_array['weixin_pc_appid'] = input('post.weixin_pc_appid');
            $update_array['weixin_pc_secret'] = input('post.weixin_pc_secret');
            
            $update_array['weixin_h5_isuse'] = input('post.weixin_h5_isuse');
            $update_array['weixin_h5_appid'] = input('post.weixin_h5_appid');
            $update_array['weixin_h5_secret'] = input('post.weixin_h5_secret');
            
            $update_array['weixin_xcx_isuse'] = input('post.weixin_xcx_isuse');
            $update_array['weixin_xcx_appid'] = input('post.weixin_xcx_appid');
            $update_array['weixin_xcx_secret'] = input('post.weixin_xcx_secret');
            
            $update_array['weixin_app_isuse'] = input('post.weixin_app_isuse');
            $update_array['weixin_app_appid'] = input('post.weixin_app_appid');
            $update_array['weixin_app_secret'] = input('post.weixin_app_secret');
            //定义验证规则
            $account_validate = ds_validate('account');
            if (!$account_validate->scene('wx')->check($update_array)){
                $this->error($account_validate->getError());
            }
            $result = $config_model->editConfig($update_array);
            if ($result) {
                $this->log(lang('account_synchronous_login'));
                $this->success(lang('ds_common_save_succ'));
            } else {
                $this->error(lang('ds_common_save_fail'));
            }
        }
    }

    /**
     * 获取卖家栏目列表,针对控制器下的栏目
     */
    protected function getAdminItemList() {
        $menu_array = array(
            array(
                'name' => 'qq',
                'text' => lang('qq_interconnection'),
                'url' => url('Account/qq')
            ),
            array(
                'name' => 'sina',
                'text' => lang('sina_interconnection'),
                'url' => url('Account/sina')
            ),
            array(
                'name' => 'wx',
                'text' => lang('wx_login'),
                'url' => url('Account/wx')
            ),
        );
        return $menu_array;
    }

}

?>
