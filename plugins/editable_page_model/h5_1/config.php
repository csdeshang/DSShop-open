<?php

class Model1{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'image_width'=>$post['image_width'],
            'image_height'=>$post['image_height'],
            'show_format'=>$post['show_format'],
            'adv'=>array_values($post['adv']),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}

