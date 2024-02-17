<?php

class Model9{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'padding_top'=>$post['padding_top'],
            'padding_bottom'=>$post['padding_bottom'],
            'back_image'=>$post['back_image'],
            'title_big'=>$post['title_big'],
            'title_small'=>$post['title_small'],
            'goods_class_1'=>$post['goods_class_1'],
            'gc_id'=>isset($post['gc_id'])?$post['gc_id']:array(),
            'adv_center'=>$post['adv_center'],
            'adv_right'=>$post['adv_right'],
            'if_fixed_goods'=>$post['if_fixed_goods'],
            'goods_id'=>isset($post['goods_id'])?$post['goods_id']:array(),
            'goods_class_2'=>$post['goods_class_2'],
            'goods_sort'=>$post['goods_sort'],
            'if_fixed_brand'=>$post['if_fixed_brand'],
            'brand_id'=>isset($post['brand_id'])?$post['brand_id']:array(),
            'goods_class_3'=>$post['goods_class_3'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config,$store_id=0){
        $config=json_decode($config,true);
        $goodsclass_model=model('goodsclass');
        $cate_list=array();
        if(isset($config['gc_id'])){
            $i=0;
            foreach($config['gc_id'] as $gc_id => $val){
              $temp=$goodsclass_model->getGoodsclassInfoById($gc_id);
              if($temp){
                  $temp['children']=$goodsclass_model->getGoodsclassListByParentId($gc_id);
                  foreach($temp['children'] as $k => $child){
                    $temp['children'][$k]['children']=$goodsclass_model->getGoodsclassListByParentId($child['gc_id']);
                  }
                $cate_list[intval($val['sort'])*10+$i]=$temp;
              }
              $i++;
            }
          }
          ksort($cate_list);
          $config['gc_list']=array_values($cate_list);
          $goods_model=model('goods');
        $goods_list=array();
        if($config['if_fixed_goods']==1){
            if(!empty($config['goods_id'])){
                $where=array();
                if($store_id){
                    $where[] = array('store_id','=',$store_id);
                }
                $where[]=array('goods_id','in',array_keys($config['goods_id']));
                $goods_list=$goods_model->getGoodsOnlineList($where, '*', 0, 'goods_id desc');
                $sorted_goods=array();
                foreach($goods_list as $v1){
                    if(empty($sorted_goods)){
                        $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id'][$v1['goods_id']]['sort'])));
                    }else{
                        $c=count($sorted_goods);
                        foreach($sorted_goods as $k2 => $v2){
                            if($v2['sort']>intval($config['goods_id'][$v1['goods_id']]['sort'])){
                                array_splice($sorted_goods,$k2,0,array(array_merge($v1,array('sort'=>intval($config['goods_id'][$v1['goods_id']]['sort'])))));
                                break;
                            }
                        }
                        if($c==count($sorted_goods)){
                            $sorted_goods[]=array_merge($v1,array('sort'=>intval($config['goods_id'][$v1['goods_id']]['sort'])));
                        }
                    }
                }
                $goods_list=$sorted_goods;
            }
        }else{
            $order='goods_id desc';
            switch(intval($config['goods_sort'])){
                case 2:
                    $order='goods_salenum desc';
                    break;
                case 3:
                    $order='evaluation_good_star desc';
                    break;        
            }
            $where = array();
            if($store_id){
                $where[] = array('goodscommon.store_id','=',$store_id);
            }
            if ($config['goods_class_2']) {
                $where[]=array('goodscommon.gc_id_1|goodscommon.gc_id_2|goodscommon.gc_id_3|goodscommon.gc_id','=',intval($config['goods_class_2']));
            }
            //所需字段
            $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.store_id,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";
            $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_goodsfcode,goods.is_have_gift,goodscommon.store_name,goodscommon.is_platform_store';

            $goods_list = $goods_model->getGoodsUnionList($where,$fieldstr , $order,'goodscommon.goods_commonid', 10);
        }
        $config['goods_list']=$goods_list;
        $brand_model = model('brand');
        $brand_list = array();
        if ($config['if_fixed_brand']) {
            if (!empty($config['brand_id'])) {
                $condition = array();
                $condition[] = array('brand_id','in',array_keys($config['brand_id']));
                $brand_list = $brand_model->getBrandPassedList($condition, '*', 10,Db::raw('FIELD(brand_id, '.implode(',',array_keys($config['brand_id'])).')'));
            }
        } else {

            $where = array();
            if ($config['goods_class_3']) {
                $where[] = array('gc_id','=',$config['goods_class_3']);
            }
            $brand_list = $brand_model->getBrandPassedList($where, '*', 10);
        }
        $config['brand_list']=$brand_list;
        return ds_callback(true,'',$config);
    }
}

