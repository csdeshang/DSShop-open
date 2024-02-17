<?php

class Model16{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'height'=>$post['height'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}