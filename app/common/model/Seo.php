<?php

namespace app\common\model;


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
 * 数据层模型
 */
class Seo extends BaseModel {

    /**
     * 存放SEO信息
     * @access private
     * @author csdeshang
     * @var obj
     */
    private $seo;

    /**
     * 取得SEO信息
     * @access public
     * @author csdeshang
     * @param array/string $type 类型
     * @return obj
     */
    public function type($type) {
        if (is_array($type)) { //商品分类
            $this->seo['seo_title'] = isset($type[1])?$type[1]:'';
            $this->seo['seo_keywords'] = isset($type[2])?$type[2]:'';
            $this->seo['seo_description'] = isset($type[3])?$type[3]:'';
        } else {
            $this->seo = $this->getSeo($type);
        }
        if (!is_array($this->seo))
            return $this;
        foreach ($this->seo as $key => $value) {
            $this->seo[$key] = str_replace(array('{sitename}'), array(config('ds_config.site_name')), $value);
        }
        return $this;
    }

    /**
     * 生成SEO缓存并返回
     * @access private
     * @author csdeshang
     * @param string $type 类型
     * @return array
     */
    private function getSeo($type) {
        $list = rkcache('seo', true);
        return $list[$type];
    }

    /**
     * 传入参数替换SEO中的标签
     * @access public
     * @author csdeshang
     * @param array $array 参数数组
     * @return obj
     */
    public function param($array = null) {
        if (!is_array($this->seo))
            return $this;
        if (is_array($array)) {
            $array_key = array_keys($array);
            foreach ($array_key as $k=>$val){
                $array_key[$k]='{'.$val.'}';
            }
            foreach ($this->seo as $key => $value) {
                $this->seo[$key] = str_replace($array_key, array_values($array), $value);
            }
        }
        return $this;
    }

    /**
     * 抛出SEO信息到模板
     * @access public
     * @author csdeshang
     * @return type
     */
    public function show() {
        $this->seo['seo_title'] = preg_replace("/{.*}/siU", '', $this->seo['seo_title']);
        $this->seo['seo_keywords'] = preg_replace("/{.*}/siU", '', $this->seo['seo_keywords']);
        $this->seo['seo_description'] = preg_replace("/{.*}/siU", '', $this->seo['seo_description']);
        return array(
            'html_title' => $this->seo['seo_title'] ? $this->seo['seo_title'] : config('ds_config.site_name'),
            'seo_keywords' => $this->seo['seo_keywords'] ? $this->seo['seo_keywords'] : config('ds_config.site_name'),
            'seo_description' => $this->seo['seo_description'] ? $this->seo['seo_description'] : config('ds_config.site_name'),
        );
    }
}

?>
