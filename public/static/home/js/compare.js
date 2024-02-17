//加载对比商品
function loadCompare(isrefresh) {
    $("#comparelist").load(HOMESITEURL + '/Compare/showcompare.html');
    return;
    if (!$("#comparelist").html()) {
        isrefresh = true;
    }
    if (isrefresh == true) {
        $("#comparelist").load(HOMESITEURL + '/Compare/showcompare.html');
    }
}
//添加对比商品
function addCompare(gid) {
    gid = parseInt(gid);
    if (gid > 0) {
        $.ajax({
            type: "GET",
            dataType: "json",
            url: HOMESITEURL + '/Compare/addcompare.html?id=' + gid,
            async: false,
            success: function (data) {
                if (data.done == true) {
                    $("[ds_type='compare_" + gid + "']").addClass('selected');
                    loadCompare(true);
                    $(".top #content-compare").animate({right: '40px'});
                } else {
                    layer.alert(data.msg);
                }
            }
        });
    } else {
        layer.alert('参数错误');
    }
    $("#lockcompare").val('unlock');//解除加入对比按钮的锁定
}
//清空对比栏
function delCompare(gid, type) {
    $.ajax({
        type: "GET",
        dataType: "json",
        url: HOMESITEURL + '/Compare/delcompare?gid=' + gid + '&type=' + type,
        async: false,
        success: function (data) {
            if (data.done == true) {
                //将对比按钮置为未对比状态
                if (type == 'mini') {
                    if (gid == 'all') {
                        $("[ds_type^='compare_']").removeClass('selected');
                    } else {
                        $("[ds_type='compare_" + gid + "']").removeClass('selected');
                    }
                }
                //加载对比信息
                if (type == 'mini') {
                    //加载对比栏
                    loadCompare(true);
                    //$("#content-compare").animate({right: '40px'});
                } else {
                    go(HOMESITEURL + '/Compare/index/' + (data.gid_str?('gids/'+data.gid_str):''));
                }
            }
            $("#lockcompare").val('unlock');//解除加入对比按钮的锁定
        }
    });
}
//初始加入对比按钮
function initCompare() {
    //绑定对比按钮事件
    $("[ds_type^='compare_']").bind('click', function () {
        if ($("#lockcompare").val() == 'unlock') {
            $("#lockcompare").val('lock');//锁定加入对比按钮
            //处理参数
            var data_str = '';
            eval('data_str =' + $(this).attr('data-param'));
            var gid = data_str.gid;

            if ($(this).hasClass('selected')) {
                $(this).removeClass('selected');
                //删除该对比商品
                delCompare(gid, 'mini');
            } else {
                //新增该对比商品
                addCompare(gid);
            }
        }
    });

    //根据是否已加入对比，显示不同样式
    $.getJSON(HOMESITEURL + '/Compare/checkcompare', function (data) {
        if (data) {
            $.each(data, function (i, val) {
                $("[ds_type='compare_" + val + "']").addClass('selected');
            });
        }
    });
}