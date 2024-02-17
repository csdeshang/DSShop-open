<?php

class Model11{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'padding_top'=>$post['padding_top'],
            'padding_bottom'=>$post['padding_bottom'],
            'adv'=>array_values($post['adv']),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        $goods_model=model('goods');
        foreach($config['adv'] as $key => $val){
            $goods_list=array();
            if($val['if_fixed_goods']==1){
                if(!empty($val['goods_id'])){
                    $where=array();
                    $where[]=array('goods_id','in',array_keys($val['goods_id']));
                    $goods_list=$goods_model->getGoodsOnlineList($where, '*', 0, 'goods_id desc');
                    $sorted_goods=array();
                    foreach($goods_list as $v1){
                        if(empty($sorted_goods)){
                            $sorted_goods[]=array_merge($v1,array('sort'=>intval($val['goods_id'][$v1['goods_id']]['sort'])));
                        }else{
                            $c=count($sorted_goods);
                            foreach($sorted_goods as $k2 => $v2){
                                if($v2['sort']>intval($val['goods_id'][$v1['goods_id']]['sort'])){
                                    array_splice($sorted_goods,$k2,0,array(array_merge($v1,array('sort'=>intval($val['goods_id'][$v1['goods_id']]['sort'])))));
                                    break;
                                }
                            }
                            if($c==count($sorted_goods)){
                                $sorted_goods[]=array_merge($v1,array('sort'=>intval($val['goods_id'][$v1['goods_id']]['sort'])));
                            }
                        }
                    }
                    $goods_list=$sorted_goods;
                }
            }else{
                $order='goods_id desc';
                switch(intval($val['goods_sort'])){
                    case 2:
                        $order='goods_salenum desc';
                        break;
                    case 3:
                        $order='evaluation_good_star desc';
                        break;        
                }
                $where = array();
                if ($val['goods_class']) {
                    $where[]=array('goodscommon.gc_id_1|goodscommon.gc_id_2|goodscommon.gc_id_3|goodscommon.gc_id','=',intval($val['goods_class']));
                }
                //所需字段
                $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";
                $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_goodsfcode,goods.is_have_gift';
    
                $goods_list = $goods_model->getGoodsUnionList($where,$fieldstr , $order,'goodscommon.goods_commonid', intval($val['goods_count']));
            }
            $config['adv'][$key]['goods_list']=$goods_list;
        }
        
        return ds_callback(true,'',$config);
    }
}

