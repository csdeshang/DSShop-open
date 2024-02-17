<?php

class Model14{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'link'=>$post['link'],
            'if_show_logo'=>$post['if_show_logo'],
            'if_show_more'=>$post['if_show_more'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}