<?php

namespace app\common\logic;

/**
 * ============================================================================
 * DSMall多用户商城
 * ============================================================================
 * 版权所有 2014-2028 长沙德尚网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.csdeshang.com
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * 逻辑层模型
 */
class EditablePageModel {

    public function modelAdd($page_id, $type, $model_id, $config_id, $store_id = 0) {
        $editable_page_config_model = model('editable_page_config');
        $editable_page_model_model = model('editable_page_model');

        $editable_page_model_info = $editable_page_model_model->getOneEditablePageModel(array('editable_page_model_id' => $model_id));
        if (!$editable_page_model_info) {
            return ds_callback(false, lang('editable_page_model_not_exist'));
        }
        $sort = 0;
        $condition = array();
        $condition[] = array('editable_page_id', '=', $page_id);
        if ($config_id) {
            $editable_page_config_info = $editable_page_config_model->getOneEditablePageConfig(array('editable_page_config_id' => $config_id));

            if ($editable_page_config_info) {
                $sort = $editable_page_config_info['editable_page_config_sort_order'] + 1;
                $condition[] = array('editable_page_config_sort_order', '>', $editable_page_config_info['editable_page_config_sort_order']);
            }
        }
        //重新排序
        $editable_page_config_list = $editable_page_config_model->getEditablePageConfigList($condition);
        if (!empty($editable_page_config_list)) {
            foreach ($editable_page_config_list as $val) {
                $editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $val['editable_page_config_id']), array('editable_page_config_sort_order' => $val['editable_page_config_sort_order'] + 1));
            }
        }
        if ($type == 'h5') {
            //初始数据
            if ($model_id == 2) {
                $editable_page_model_info['editable_page_model_content'] = '{"width":"100%","height":"300px","back_color":"unset","margin_top":"0","margin_bottom":"0","editor":["&lt;table&gt;&lt;tbody&gt;&lt;tr class=&quot;firstRow&quot;&gt;&lt;td width=&quot;237&quot; valign=&quot;middle&quot; rowspan=&quot;2&quot; colspan=&quot;1&quot; align=&quot;center&quot;&gt;&lt;img src=&quot;\/uploads\/home\/common\/page-model-h5-2-1.png&quot;\/&gt;&lt;\/td&gt;&lt;td width=&quot;237&quot; valign=&quot;top&quot; align=&quot;left&quot;&gt;&lt;img src=&quot;\/uploads\/home\/common\/page-model-h5-2-2.png&quot;\/&gt;&lt;\/td&gt;&lt;\/tr&gt;&lt;tr&gt;&lt;td width=&quot;237&quot; valign=&quot;top&quot; align=&quot;left&quot;&gt;&lt;img src=&quot;\/uploads\/home\/common\/page-model-h5-2-2.png&quot;\/&gt;&lt;\/td&gt;&lt;\/tr&gt;&lt;\/tbody&gt;&lt;\/table&gt;&lt;p&gt;&lt;br\/&gt;&lt;\/p&gt;"]}';
            }
            if ($model_id == 1) {
                $editable_page_model_info['editable_page_model_content'] = '{"width":"100%","height":"188px","back_color":"unset","margin_top":"0","margin_bottom":"0","image":[{"count":2,"list":{"1":{"path":"\/uploads\/home\/common\/page-model-h5-1-1.png","sort":"9"},"0":{"path":"\/uploads\/home\/common\/page-model-h5-1-1.png","sort":"9"}}}],"link":[{"count":2,"list":[]}]}';
            }
            $editable_page_model_info['editable_page_model_content'] = str_replace('1200px', '100%', $editable_page_model_info['editable_page_model_content']);
        }
        $data = array(
            'editable_page_id' => $page_id,
            'editable_page_config_sort_order' => $sort,
            'editable_page_model_id' => $model_id,
            'editable_page_config_content' => $editable_page_model_info['editable_page_model_content'],
        );
        $new_config_id = $editable_page_config_model->addEditablePageConfig($data);
        $data['editable_page_config_id'] = $new_config_id;
        $data['editable_page_config_content'] = json_decode($data['editable_page_config_content'], true);


        $data = $this->updatePage($data, $store_id);
        return ds_callback(true, '', $data);
    }

    public function modelMove($direction, $config_id) {
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_info = $editable_page_config_model->getOneEditablePageConfig(array('editable_page_config_id' => $config_id));
        if (!$editable_page_config_info) {
            return ds_callback(false, lang('param_error'));
        }
        $condition = array();
        if ($direction) {
            $sort = array('editable_page_config_sort_order', '>', $editable_page_config_info['editable_page_config_sort_order']);
            $order = 'editable_page_config_sort_order asc';
        } else {
            $sort = array('editable_page_config_sort_order', '<', $editable_page_config_info['editable_page_config_sort_order']);
            $order = 'editable_page_config_sort_order desc';
        }

        $condition[] = array('editable_page_id', '=', $editable_page_config_info['editable_page_id']);
        $editable_page_config_list = $editable_page_config_model->getEditablePageConfigList($condition, 1, $order);
        if (empty($editable_page_config_list)) {
            return ds_callback(false, lang('param_error'));
        } else {
            $editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $editable_page_config_info['editable_page_config_id']), array('editable_page_config_sort_order' => $editable_page_config_list[0]['editable_page_config_sort_order']));
            $editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $editable_page_config_list[0]['editable_page_config_id']), array('editable_page_config_sort_order' => $editable_page_config_info['editable_page_config_sort_order']));
        }

        return ds_callback(true);
    }

    public function modelSort($direction, $config_id, $o_config_id) {
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_info1 = $editable_page_config_model->getOneEditablePageConfig(array('editable_page_config_id' => $direction ? $config_id : $o_config_id));
        if (!$editable_page_config_info1) {
            return ds_callback(false, lang('param_error'));
        }
        $editable_page_config_info2 = $editable_page_config_model->getOneEditablePageConfig(array('editable_page_config_id' => $direction ? $o_config_id : $config_id));
        if (!$editable_page_config_info2) {
            return ds_callback(false, lang('param_error'));
        }
        //重新排序
        $condition = array();
        $condition[] = array('editable_page_id', '=', $editable_page_config_info1['editable_page_id']);
        $condition[] = array('editable_page_config_sort_order', 'between', [$editable_page_config_info1['editable_page_config_sort_order'], $editable_page_config_info2['editable_page_config_sort_order']]);
        $editable_page_config_list = $editable_page_config_model->getEditablePageConfigList($condition);
        if (!empty($editable_page_config_list)) {
            foreach ($editable_page_config_list as $val) {
                if ($val['editable_page_config_id'] == $config_id) {
                    if ($direction) {
                        $sort = $editable_page_config_list[count($editable_page_config_list) - 1]['editable_page_config_sort_order'];
                    } else {
                        $sort = $editable_page_config_list[0]['editable_page_config_sort_order'];
                    }
                    $editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $val['editable_page_config_id']), array('editable_page_config_sort_order' => $sort));
                    continue;
                }

                if ($direction) {
                    $sort = $val['editable_page_config_sort_order'] - 1;
                } else {
                    $sort = $val['editable_page_config_sort_order'] + 1;
                }
                $editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $val['editable_page_config_id']), array('editable_page_config_sort_order' => $sort));
            }
        }
        return ds_callback(true);
    }

    public function modelEdit($editable_page_config_info, $post, $store_id = 0) {
        $editable_page_config_model = model('editable_page_config');
        $config_info = json_decode($editable_page_config_info['editable_page_config_content'], true);
        $res = $this->getBaseConfig($editable_page_config_info['editable_page_model_id'], $config_info, $post);
        if(!$res['code']){
            return $res;
        }
        $config_info=$res['data'];
        if (!$editable_page_config_model->editEditablePageConfig(array('editable_page_config_id' => $editable_page_config_info['editable_page_config_id']), array('editable_page_config_content' => json_encode($config_info)))) {
            return ds_callback(false, lang('ds_common_op_fail'));
        }
        $editable_page_config_info['editable_page_config_content'] = $config_info;

        $editable_page_config_info = $this->updatePage($editable_page_config_info, $store_id);
        return ds_callback(true, '', $editable_page_config_info);
    }

    public function imageDel($file_id) {
        $upload_model = model('upload');
        /**
         * 删除图片
         */
        $file_array = $upload_model->getOneUpload($file_id);
        @unlink(BASE_UPLOAD_PATH . DIRECTORY_SEPARATOR . ATTACH_EDITABLE_PAGE . DIRECTORY_SEPARATOR . $file_array['file_name']);
        /**
         * 删除信息
         */
        $condition = array();
        $condition[] = array('upload_id', '=', $file_id);
        $upload_model->delUpload($condition);
        return ds_callback(true);
    }

    /**
     * 图片上传
     */
    public function imageUpload($name, $config_id) {
        $file_name = '';
        $file_object = request()->file($name);
        if ($file_object) {

            $res = ds_upload_pic(ATTACH_EDITABLE_PAGE, $name);
            if ($res['code']) {
                $file_name = $res['data']['file_name'];
            } else {
                return ds_callback(false, $res['msg']);
            }
        } else {
            return ds_callback(false, lang('param_error'));
        }
        /**
         * 模型实例化
         */
        $upload_model = model('upload');
        /**
         * 图片数据入库
         */
        $insert_array = array();
        $insert_array['file_name'] = $file_name;
        $insert_array['upload_type'] = '7';
        $insert_array['file_size'] = $_FILES[$name]['size'];
        $insert_array['item_id'] = intval($config_id);
        $insert_array['upload_time'] = TIMESTAMP;
        $result = $upload_model->addUpload($insert_array);
        if ($result) {
            $data = array();
            $data['file_id'] = $result;
            $data['file_name'] = $file_name;
            $data['file_path'] = ds_get_pic(ATTACH_EDITABLE_PAGE , $file_name);
            /**
             * 整理为json格式
             */
            return ds_callback(true, '', $data);
        } else {
            return ds_callback(false, lang('ds_common_op_fail'));
        }
    }

    private function getBaseConfig($model_id, $config_info, $post) {

        $config_info['back_color'] = $post['back_color'];
        $config_info['margin_top'] = $post['margin_top'];
        $config_info['margin_bottom'] = $post['margin_bottom'];
        switch ($model_id) {
            case 1:
                $config_info['width'] = $post['width'];
                $config_info['height'] = $post['height'];
                $config_info['image'][0]['count'] = $config_info['link'][0]['count'] = intval($post['image_count']);
                if ($config_info['image'][0]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                break;
            case 2:
                $config_info['width'] = $post['width'];
                $config_info['height'] = $post['height'];
                break;
            case 3:
            case 5:
            case 6:
                $config_info['goods'][0]['count'] = intval($post['goods_count']);
                if ($config_info['goods'][0]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                break;
            case 11:
                $config_info['image'][0]['count'] = $config_info['link'][0]['count'] = intval($post['image_count']);
                $config_info['goods'][0]['count'] = intval($post['goods_count']);
                if ($config_info['image'][0]['count'] < 1 || $config_info['goods'][0]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                for ($i = 0; $i < $config_info['image'][0]['count']; $i++) {
                    if (!isset($config_info['goods'][$i])) {
                        $config_info['goods'][$i] = $config_info['goods'][0];
                    }
                    $config_info['goods'][$i]['count'] = $config_info['goods'][0]['count'];
                }
                $config_info['goods'] = array_slice($config_info['goods'], 0, $config_info['image'][0]['count']);
                break;
            case 12:
                $config_info['image'][0]['count'] = $config_info['link'][0]['count'] = $config_info['text'][0]['count'] = intval($post['image_count']);
                if ($config_info['image'][0]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                break;
            case 13:
                $config_info['text'][1]['count'] = $config_info['link'][1]['count'] = intval($post['text_count']);
                if ($config_info['text'][1]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                break;
            case 14:
                $config_info['image'][0]['count'] = intval($post['image_count']);
                if ($config_info['image'][0]['count'] < 1) {
                    return ds_callback(false, lang('param_error'));
                }
                for ($i = 0; $i < $config_info['image'][0]['count']; $i++) {
                    if (!isset($config_info['image'][$i + 1])) {
                        $config_info['image'][$i + 1] = $config_info['image'][1];
                    }
                }
                $config_info['image'] = array_slice($config_info['goods'], 0, $config_info['image'][0]['count'] + 1);
                break;
        }
        return ds_callback(true, '',$config_info);
    }

    public function updatePage($data, $store_id = 0) {
        $editable_page_config_model = model('editable_page_config');
        $editable_page_config_model->store_id = $store_id;
        //更新页面编辑时间
        model('editable_page')->editEditablePage(array('editable_page_id' => $data['editable_page_id']), array('editable_page_edit_time' => TIMESTAMP));

        if (isset($data['editable_page_config_content']['goods'])) {
            $data['goods_list'] = array();
            foreach ($data['editable_page_config_content']['goods'] as $key => $val) {
                $data['goods_list'][$key] = $editable_page_config_model->getEditablePageConfigGoods($val);

                foreach ($data['goods_list'][$key] as $i => $goods) {
                    $data['goods_list'][$key][$i]['goods_image_url'] = goods_thumb($goods, 240);
                }
            }
        }
        if (isset($data['editable_page_config_content']['cate'])) {
            $data['cate_list'] = array();
            foreach ($data['editable_page_config_content']['cate'] as $key => $val) {
                $data['cate_list'][$key] = $editable_page_config_model->getEditablePageConfigCate($val, $data['editable_page_model_id']);
            }
        }
        if (isset($data['editable_page_config_content']['brand'])) {
            $data['brand_list'] = array();
            foreach ($data['editable_page_config_content']['brand'] as $key => $val) {
                $data['brand_list'][$key] = $editable_page_config_model->getEditablePageConfigBrand($val);
            }
        }
        return $data;
    }

}
