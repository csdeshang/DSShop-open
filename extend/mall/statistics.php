<?php

/**
 * 获得折线图统计图数据
 * param $statarr 图表需要的设置项
 */
function getStatData_LineLabels($stat_arr){
	//图表区、图形区和通用图表配置选项
	$stat_arr['chart']['type'] = 'line';
	//图表序列颜色数组
	isset($stat_arr['colors'])?'':$stat_arr['colors'] = array('#058DC7', '#ED561B', '#8bbc21', '#0d233a');
	//去除版权信息
	$stat_arr['credits']['enabled'] = false;
	//导出功能选项
	$stat_arr['exporting']['enabled'] = false;
	//标题如果为字符串则使用默认样式
	is_string($stat_arr['title'])?$stat_arr['title'] = array('text'=>"<b>{$stat_arr['title']}</b>",'x'=>-20):'';
	//子标题如果为字符串则使用默认样式
    if(isset($stat_arr['subtitle'])) {
        is_string($stat_arr['subtitle']) ? $stat_arr['subtitle'] = array(
            'text' => "<b>{$stat_arr['subtitle']}</b>", 'x' => -20
        ) : '';
    }
	//Y轴如果为字符串则使用默认样式
	if(is_string($stat_arr['yAxis'])){
		$text = $stat_arr['yAxis'];
		unset($stat_arr['yAxis']);
		$stat_arr['yAxis']['title']['text'] = $text;
	}
	return json_encode($stat_arr);
}

/**
 * 获得Column2D统计图数据
 */
function getStatData_Column2D($stat_arr){
    //图表区、图形区和通用图表配置选项
	$stat_arr['chart']['type'] = 'column';
	//去除版权信息
	$stat_arr['credits']['enabled'] = false;
	//导出功能选项
	$stat_arr['exporting']['enabled'] = false;
	//标题如果为字符串则使用默认样式
	is_string($stat_arr['title'])?$stat_arr['title'] = array('text'=>"<b>{$stat_arr['title']}</b>",'x'=>-20):'';
	//子标题如果为字符串则使用默认样式
    if(isset($stat_arr['subtitle'])) {
        is_string($stat_arr['subtitle']) ? $stat_arr['subtitle'] = array(
            'text' => "<b>{$stat_arr['subtitle']}</b>", 'x' => -20
        ) : '';
    }
	//Y轴如果为字符串则使用默认样式
	if(is_string($stat_arr['yAxis'])){
		$text = $stat_arr['yAxis'];
		unset($stat_arr['yAxis']);
		$stat_arr['yAxis']['title']['text'] = $text;
	}
	//柱形的颜色数组
	$color = array('#7a96a4','#cba952','#667b16','#a26642','#349898','#c04f51','#5c315e','#445a2b','#adae50','#14638a','#b56367','#a399bb','#070dfa','#47ff07','#f809b7');
	
	foreach ($stat_arr['series'] as $series_k=>$series_v){
	    foreach ($series_v['data'] as $data_k=>$data_v){
	        $data_v['color'] = $color[$data_k%15];
	        $series_v['data'][$data_k] = $data_v;
	    }
	    $stat_arr['series'][$series_k]['data'] = $series_v['data'];
	}
	//print_r($stat_arr); die;
	return json_encode($stat_arr);
}

/**
 * 获得Basicbar统计图数据
 */
function getStatData_Basicbar($stat_arr){
    //图表区、图形区和通用图表配置选项
	$stat_arr['chart']['type'] = 'bar';
	//去除版权信息
	$stat_arr['credits']['enabled'] = false;
	//导出功能选项
	$stat_arr['exporting']['enabled'] = false;
	//显示datalabel
	$stat_arr['plotOptions']['bar']['dataLabels']['enabled'] = true;
	//标题如果为字符串则使用默认样式
	is_string($stat_arr['title'])?$stat_arr['title'] = array('text'=>"<b>{$stat_arr['title']}</b>",'x'=>-20):'';
	//子标题如果为字符串则使用默认样式
    if (isset($stat_arr['subtitle'])) {
        is_string($stat_arr['subtitle']) ? $stat_arr['subtitle'] = array(
            'text' => "<b>{$stat_arr['subtitle']}</b>", 'x' => -20
        ) : '';
    }
	//Y轴如果为字符串则使用默认样式
	if(is_string($stat_arr['yAxis'])){
		$text = $stat_arr['yAxis'];
		unset($stat_arr['yAxis']);
		$stat_arr['yAxis']['title']['text'] = $text;
	}
	//柱形的颜色数组1
	$color = array('#7a96a4','#cba952','#667b16','#a26642','#349898','#c04f51','#5c315e','#445a2b','#adae50','#14638a','#b56367','#a399bb','#070dfa','#47ff07','#f809b7');
	
	foreach ($stat_arr['series'] as $series_k=>$series_v){
	    foreach ($series_v['data'] as $data_k=>$data_v){
	        if (!isset($data_v['color'])){
	            $data_v['color'] = $color[$data_k%15];
	        }
	        $series_v['data'][$data_k] = $data_v;
	    }
	    $stat_arr['series'][$series_k]['data'] = $series_v['data'];
	}
	//print_r($stat_arr); die;
	return json_encode($stat_arr);
}

/**
 * 计算环比
 */
function getHb($updata, $currentdata){
	if($updata != 0){
		$mtomrate = round(($currentdata - $updata)/$updata*100, 2).'%';
	} else {
	    $mtomrate = '-';
	}
	return $mtomrate; 
}

/**
 * 计算同比
 */
function getTb($updata, $currentdata){
	if($updata != 0){
		$ytoyrate = round(($currentdata - $updata)/$updata*100, 2).'%';
	} else {
	    $ytoyrate = '-';
	}
	return $ytoyrate; 
}

/**
 * 地图统计图
 */
function getStatData_Map($stat_arr){
    //$color_arr = array('#f63a3a','#ff5858','#ff9191','#ffc3c3','#ffd5d5');
    $color_arr = array('#fd0b07','#ff9191','#f7ba17','#fef406','#25aae2');
    $stat_arrnew = array();
    foreach ($stat_arr as $k=>$v){
        $stat_arrnew[] = array('cha'=>$v['cha'],'name'=>$v['name'],'des'=>$v['des'],'color'=>$color_arr[$v['level']]);
    }
    return json_encode($stat_arrnew);
}
/**
 * 获得饼形图数据
 */
function getStatData_Pie($data){
	$stat_arr['chart']['type'] = 'pie';
	$stat_arr['credits']['enabled'] = false;
	$stat_arr['title']['text'] = $data['title'];
	$stat_arr['tooltip']['pointFormat'] = '{series.name}: <b>{point.y}</b>';
	$stat_arr['plotOptions']['pie'] = array(
		'allowPointSelect'=>true,
		'cursor'=>'pointer',
		'dataLabels'=>array(
			'enabled'=>$data['label_show'],
			'color'=>'#000000',
			'connectorColor'=>'#000000',
			'format'=>'<b>{point.name}</b>: {point.percentage:.1f} %'
		)
	);
	$stat_arr['series'][0]['name'] = $data['name'];
	$stat_arr['series'][0]['data'] = array();
	foreach ($data['series'] as $k=>$v){
		$stat_arr['series'][0]['data'][] = array($v['p_name'],$v['allnum']);
	}
	//exit(json_encode($stat_arr));
	return json_encode($stat_arr);
}