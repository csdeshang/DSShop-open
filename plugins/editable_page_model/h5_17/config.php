<?php

class Model17{
    public function filterData($post){
        $data=array(
            'back_color'=>$post['back_color'],
            'video'=>$post['video'],
        );
        return ds_callback(true,'',$data);
    }
    
    public function formatData($config){
        $config=json_decode($config,true);
        return ds_callback(true,'',$config);
    }
}