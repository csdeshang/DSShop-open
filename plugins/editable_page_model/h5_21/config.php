<?php
use think\facade\Db;
class Model21{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'goods_count'=>$post['goods_count'],
            'goods_class'=>$post['goods_class'],
            'goods_sort'=>$post['goods_sort'],
            'if_show_title_icon'=>$post['if_show_title_icon'],
            'title_icon'=>$post['title_icon'],
            'if_fixed_goods'=>$post['if_fixed_goods'],
            'goods_id'=>isset($post['goods_id'])?$post['goods_id']:array(),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config,$store_id=0){
        $config=json_decode($config,true);
        $goods_model=model('goods');
        $goods_list=array();
        $condition=array();
        if($store_id){
            $condition[] = array('store_id','=',$store_id);
        }
                $condition[] = array('presell_state','=',\app\common\model\Presell::PRESELL_STATE_NORMAL);
                $condition[] = array('presell_end_time','>',TIMESTAMP);
                $condition[] = array('presell_start_time','<',TIMESTAMP);
                $subQuery=Db::name('presell')->field('goods_id')->where($condition)->buildSql();
                $presell_model=model('presell');
        if($config['if_fixed_goods']==1){
            if(!empty($config['goods_id'])){
                $where=array();
                if($store_id){
                    $where[] = array('store_id','=',$store_id);
                }
                $where[]=array('goods_id','in',array_keys($config['goods_id']));
                $where[]=array('goods_id','exp',Db::raw('in '.$subQuery));
                $goods_list=$goods_model->getGoodsOnlineList($where, '*', 0, 'goods_id desc');
                $sorted_goods=array();
                foreach($goods_list as $v1){
                    $presellInfo=$presell_model->getPresellInfoByGoodsID($v1['goods_id']);
                    if(!$presellInfo){
                        continue;
                    }
                    $v1['goods_image']=goods_thumb($v1);
                    $v1['goods_promotion_price']=$presellInfo['presell_price'];
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
            if ($config['goods_class']) {
                $where[]=array('goodscommon.gc_id_1|goodscommon.gc_id_2|goodscommon.gc_id_3|goodscommon.gc_id','=',intval($config['goods_class']));
            }
            $where[]=array('goods_id','exp',Db::raw('in '.$subQuery));
            //所需字段
            $fieldstr = "goods.goods_id,goods.goods_storage,goodscommon.goods_commonid,goodscommon.store_id,goodscommon.goods_name,goodscommon.goods_advword,goodscommon.goods_price,goods.goods_promotion_price,goods.goods_promotion_type,goodscommon.goods_marketprice,goodscommon.goods_image,goods.goods_salenum,goods.evaluation_good_star,goods.evaluation_count";
            $fieldstr .= ',goodscommon.is_virtual,goodscommon.is_goodsfcode,goods.is_have_gift,goodscommon.store_name,goodscommon.is_platform_store';

            $goods_list = $goods_model->getGoodsUnionList($where,$fieldstr , $order,'goodscommon.goods_commonid', intval($config['goods_count']));
            foreach($goods_list as $key => $val){
                $presellInfo=$presell_model->getPresellInfoByGoodsID($val['goods_id']);
                    if(!$presellInfo){
                        continue;
                    }
                    $goods_list[$key]['goods_image']=goods_thumb($val);
                    $goods_list[$key]['goods_promotion_price']=$presellInfo['presell_price'];
            }
        }
        $goods_list=array_values($goods_list);
        $config['goods_list']=$goods_list;
        return ds_callback(true,'',$config);
    }
}