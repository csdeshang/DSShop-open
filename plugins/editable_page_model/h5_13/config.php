<?php

class Model13{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'notice_image'=>$post['notice_image'],
            'notice_link'=>$post['notice_link'],
            'notice_title'=>array_values($post['notice_title']),
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}