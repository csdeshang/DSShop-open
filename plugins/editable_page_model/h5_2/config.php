<?php

class Model2{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'editor_content'=>$post['editor_content'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        $config['editor_content']=htmlspecialchars_decode($config['editor_content']);
        return ds_callback(true,'',$config);
    }
}