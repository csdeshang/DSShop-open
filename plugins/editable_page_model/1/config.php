<?php

class Model1{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'padding_top'=>$post['padding_top'],
            'padding_bottom'=>$post['padding_bottom'],
            'image_width'=>$post['image_width'],
            'image_height'=>$post['image_height'],
            'adv'=>array_values($post['adv']),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}

