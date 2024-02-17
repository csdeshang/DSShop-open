<?php

class Model10{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'padding_top'=>$post['padding_top'],
            'padding_bottom'=>$post['padding_bottom'],
            'back_image_l'=>$post['back_image_l'],
            'title_big_l'=>$post['title_big_l'],
            'title_small_l'=>$post['title_small_l'],
            'goods_class_1_l'=>$post['goods_class_1_l'],
            'gc_id_l'=>isset($post['gc_id_l'])?$post['gc_id_l']:array(),
            'adv_right_l'=>$post['adv_right_l'],
            'if_fixed_goods_l'=>$post['if_fixed_goods_l'],
            'goods_id_l'=>isset($post['goods_id_l'])?$post['goods_id_l']:array(),
            'goods_class_2_l'=>$post['goods_class_2_l'],
            'goods_sort_l'=>$post['goods_sort_l'],
            'if_fixed_brand_l'=>$post['if_fixed_brand_l'],
            'brand_id_l'=>isset($post['brand_id_l'])?$post['brand_id_l']:array(),
            'goods_class_3_l'=>$post['goods_class_3_l'],
            'back_image_r'=>$post['back_image_r'],
            'title_big_r'=>$post['title_big_r'],
            'title_small_r'=>$post['title_small_r'],
            'goods_class_1_r'=>$post['goods_class_1_r'],
            'gc_id_r'=>isset($post['gc_id_r'])?$post['gc_id_r']:array(),
            'adv_right_r'=>$post['adv_right_r'],
            'if_fixed_goods_r'=>$post['if_fixed_goods_r'],
            'goods_id_r'=>isset($post['goods_id_r'])?$post['goods_id_r']:array(),
            'goods_class_2_r'=>$post['goods_class_2_r'],
            'goods_sort_r'=>$post['goods_sort_r'],
            'if_fixed_brand_r'=>$post['if_fixed_brand_r'],
            'brand_id_r'=>isset($post['brand_id_r'])?$post['brand_id_r']:array(),
            'goods_class_3_r'=>$post['goods_class_3_r'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        $goodsclass_model=model('goodsclass');
        $cate_list=array();
        if(isset($config['gc_id_l'])){
            $temp=$goodsclass_model->getGoodsclassInfoById($config['gc_id_l']);
            if($temp){
                $temp['children']=$goodsclass_model->getGoodsclassListByParentId($config['gc_id_l']);
                foreach($temp['children'] as $k => $child){
                  $temp['children'][$k]['children']=$goodsclass_model->getGoodsclassListByParentId($child['gc_id']);
                }
              $cate_list[]=$temp;
            }
          }
          $config['gc_list_l']=$cate_list;
          $cate_list=array();
        if(isset($config['gc_id_r'])){
            $temp=$goodsclass_model->getGoodsclassInfoById($config['gc_id_r']);
            if($temp){
                $temp['children']=$goodsclass_model->getGoodsclassListByParentId($config['gc_id_r']);
                foreach($temp['children'] as $k => $child){
                  $temp['children'][$k]['children']=$goodsclass_model->getGoodsclassListByParentId($child['gc_id']);
                }
              $cate_list[]=$temp;
            }
          }
          $config['gc_list_r']=$cate_list;
          $goods_model=model('goods');
        $goods_list=array();
        if($config['if_fixed_goods_l']==1){
            if(!empty($config['goods_id_l'])){
                $where=array();
                $where[]=array('goods_id','in',array_keys($config['goods_id_l']));
                $goods_list=$goods_model->getGoodsOnlineList($where, '*', 0, 'goods_id desc');
                $sorted_goods=array();
                foreach($goods_list as $v1){
                    if(empty($sorted_goods)){
                        $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id_l'][$v1['goods_id']]['sort'])));
                    }else{
                        $c=count($sorted_goods);
                        foreach($sorted_goods as $k2 => $v2){
                            if($v2['sort']>intval($config['goods_id_l'][$v1['goods_id']]['sort'])){
                                array_splice($sorted_goods,$k2,0,array(array_merge($v1,array('sort'=>intval($config['goods_id_l'][$v1['goods_id']]['sort'])))));
                                break;
                            }
                        }
                        if($c==count($sorted_goods)){
                            $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id_l'][$v1['goods_id']]['sort'])));
                        }
                    }
                }
                $goods_list=$sorted_goods;
            }
        }else{
            $order='goods_id desc';
            switch(intval($config['goods_sort_l'])){
                case 2:
                    $order='goods_salenum desc';
                    break;
                case 3:
                    $order='evaluation_good_star desc';
                    break;        
            }
            $where = array();
            if ($config['goods_class_2_l']) {
                $where[]=array('goodscommon.gc_id_1|goodscommon.gc_id_2|goodscommon.gc_id_3|goodscommon.gc_id','=',intval($config['goods_class_2_l']));
            }
            //所需字段
            $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";
            $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_goodsfcode,goods.is_have_gift';

            $goods_list = $goods_model->getGoodsUnionList($where,$fieldstr , $order,'goodscommon.goods_commonid', 10);
        }
        $config['goods_list_l']=$goods_list;
        $goods_list=array();
        if($config['if_fixed_goods_r']==1){
            if(!empty($config['goods_id_r'])){
                $where=array();
                $where[]=array('goods_id','in',array_keys($config['goods_id_r']));
                $goods_list=$goods_model->getGoodsOnlineList($where, '*', 0, 'goods_id desc');
                $sorted_goods=array();
                foreach($goods_list as $v1){
                    if(empty($sorted_goods)){
                        $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id_r'][$v1['goods_id']]['sort'])));
                    }else{
                        $c=count($sorted_goods);
                        foreach($sorted_goods as $k2 => $v2){
                            if($v2['sort']>intval($config['goods_id_r'][$v1['goods_id']]['sort'])){
                                array_splice($sorted_goods,$k2,0,array(array_merge($v1,array('sort'=>intval($config['goods_id_r'][$v1['goods_id']]['sort'])))));
                                break;
                            }
                        }
                        if($c==count($sorted_goods)){
                            $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id_r'][$v1['goods_id']]['sort'])));
                        }
                    }
                }
                $goods_list=$sorted_goods;
            }
        }else{
            $order='goods_id desc';
            switch(intval($config['goods_sort_r'])){
                case 2:
                    $order='goods_salenum desc';
                    break;
                case 3:
                    $order='evaluation_good_star desc';
                    break;        
            }
            $where = array();
            if ($config['goods_class_2_r']) {
                $where[]=array('goodscommon.gc_id_1|goodscommon.gc_id_2|goodscommon.gc_id_3|goodscommon.gc_id','=',intval($config['goods_class_2_r']));
            }
            //所需字段
            $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";
            $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_goodsfcode,goods.is_have_gift';

            $goods_list = $goods_model->getGoodsUnionList($where,$fieldstr , $order,'goodscommon.goods_commonid', 10);
        }
        $config['goods_list_r']=$goods_list;
        $brand_model = model('brand');
        $brand_list = array();
        if ($config['if_fixed_brand_l']) {
            if (!empty($config['brand_id_l'])) {
                $condition = array();
                $condition[] = array('brand_id','in',array_keys($config['brand_id_l']));
                $brand_list = $brand_model->getBrandPassedList($condition, '*', 10,Db::raw('FIELD(brand_id, '.implode(',',array_keys($config['brand_id_l'])).')'));
            }
        } else {

            $where = array();
            if ($config['goods_class_3_l']) {
                $where[] = array('gc_id','=',$config['goods_class_3_l']);
            }
            $brand_list = $brand_model->getBrandPassedList($where, '*', 10);
        }
        $config['brand_list_l']=$brand_list;
        $brand_list = array();
        if ($config['if_fixed_brand_r']) {
            if (!empty($config['brand_id_r'])) {
                $condition = array();
                $condition[] = array('brand_id','in',array_keys($config['brand_id_r']));
                $brand_list = $brand_model->getBrandPassedList($condition, '*', 10,Db::raw('FIELD(brand_id, '.implode(',',array_keys($config['brand_id_r'])).')'));
            }
        } else {

            $where = array();
            if ($config['goods_class_3_r']) {
                $where[] = array('gc_id','=',$config['goods_class_3_r']);
            }
            $brand_list = $brand_model->getBrandPassedList($where, '*', 10);
        }
        $config['brand_list_r']=$brand_list;
        return ds_callback(true,'',$config);
    }
}

