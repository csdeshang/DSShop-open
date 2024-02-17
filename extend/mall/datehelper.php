<?php

/** 
 * 获得系统年份数组
 */
function getSystemYearArr(){
    $year_arr = array('2017'=>'2017','2018'=>'2018','2019'=>'2019','2020'=>'2020','2021'=>'2021','2022'=>'2022','2023'=>'2023','2024'=>'2024','2025'=>'2025','2026'=>'2026','2027'=>'2027');
    return $year_arr;
}
/**
 * 获得系统月份数组
 */
function getSystemMonthArr(){
	$month_arr = array('1'=>'01','2'=>'02','3'=>'03','4'=>'04','5'=>'05','6'=>'06','7'=>'07','8'=>'08','9'=>'09','10'=>'10','11'=>'11','12'=>'12');
	return $month_arr;
}
/**
 * 获得系统周数组
 */
function getSystemWeekArr(){
	$week_arr = array('1'=>'周一','2'=>'周二','3'=>'周三','4'=>'周四','5'=>'周五','6'=>'周六','7'=>'周日');
	return $week_arr;
}
/**
 * 获取某月的最后一天
 */
function getMonthLastDay($year, $month){
    $t = mktime(0, 0, 0, $month + 1, 1, $year);
    $t = $t - 60 * 60 * 24;
    return $t;
}
/**
 * 获得系统某月的周数组，第一周不足的需要补足
 */
function getMonthWeekArr($current_year, $current_month){
	//该月第一天
	$firstday = strtotime($current_year.'-'.$current_month.'-01');
	//该月的第一周有几天
	$firstweekday = (7 - date('N',$firstday) +1);
	//计算该月第一个周一的时间
	$starttime = $firstday-3600*24*(7-$firstweekday);
	//该月的最后一天
	$lastday = strtotime($current_year.'-'.$current_month.'-01'." +1 month -1 day");
	//该月的最后一周有几天
	$lastweekday = date('N',$lastday);
	//该月的最后一个周末的时间
	$endtime = $lastday-3600*24*($lastweekday%7);
	$step = 3600*24*7;//步长值
	$week_arr = array();
	for ($i=$starttime; $i<$endtime; $i= $i+3600*24*7){
		$week_arr[] = array('key'=>date('Y-m-d',$i).'|'.date('Y-m-d',$i+3600*24*6), 'val'=>date('Y-m-d',$i).'~'.date('Y-m-d',$i+3600*24*6));
	}
	return $week_arr;
}
/**
 * 获取本周的开始时间和结束时间
 */
function getWeek_SdateAndEdate($current_time){
    $current_time = strtotime(date('Y-m-d',$current_time));
	$return_arr['sdate'] = date('Y-m-d', $current_time-86400*(date('N',$current_time) - 1));
	$return_arr['edate'] = date('Y-m-d', $current_time+86400*(7- date('N',$current_time)));
	return $return_arr;
}