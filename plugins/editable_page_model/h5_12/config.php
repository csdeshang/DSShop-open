<?php

class Model12{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'show_format'=>$post['show_format'],
            'nav'=>array_values($post['nav']),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}