//导出Excel
function download_excel(obj){
	var tablehtml = $("#datatable").html();
	if(!tablehtml){
        layer.msg('暂时没有数据');
		return false;
	}
	var data = $(obj).attr('data-param');
	if(data == undefined  || data.length<=0){
        layer.msg('参数错误');
		return false;
	}
	eval("data = "+data);
	go(data.url);
}

//统计地图
function getMap(stat_json,obj_id){
	$('#'+obj_id).vectorMap({ map: 'china_zh', color: "#ffd5d5", 
        onLabelShow: function (event, label, code) {
            $.each(stat_json, function (i, items) {
                if (code == items.cha) {
                    label.html(items.name + items.des);
                }
            });
        }
	});
	//改变有活动省份区域的颜色
	$.each(stat_json, function (i, items) {
		var jsonStr = "{" + items.cha + ":'"+items.color+"'}";
		$('#'+obj_id).vectorMap('set', 'colors', eval('(' + jsonStr + ')'));
	});
}
