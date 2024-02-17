<?php

class Model8{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'padding_top'=>$post['padding_top'],
            'padding_bottom'=>$post['padding_bottom'],
            'goods_class'=>$post['goods_class'],
            'gc_id'=>isset($post['gc_id'])?$post['gc_id']:array(),
            'gc'=>array_values($post['gc']),
            'adv_center'=>$post['adv_center'],
            'article_title_1'=>$post['article_title_1'],
            'article_title_2'=>$post['article_title_2'],
            'article'=>$post['article'],
            'adv_right'=>$post['adv_right'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
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
          foreach($config['adv_center'] as $key => $val){
            if(!$val['image']){
                unset($config['adv_center'][$key]);
            }
          }
          $config['adv_center']=array_values($config['adv_center']);
          foreach($config['article'] as $key => $val){
            if(!$val['title']){
                unset($config['article'][$key]);
            }
          }
          $config['article']=array_values($config['article']);
        return ds_callback(true,'',$config);
    }
}

