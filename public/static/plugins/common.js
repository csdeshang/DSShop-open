$(function() {
    var controller = $('#search ul.tab').attr('dstype');

    $('#search ul.tab li span').eq(0).html('商品');
    $('#search-form').attr("action", HOMESITEURL + "/Search/index.html");

    $('#search').hover(function() {
        $('#search ul.tab li').eq(1).show();
        $('#search ul.tab li i').addClass('over').removeClass('arrow');
    }, function() {
        $('#search ul.tab li').eq(1).hide();
        $('#search ul.tab li i').addClass('arrow').removeClass('over');
    });
    $('#search ul.tab li').eq(1).click(function() {
        $(this).hide();
        $('#keyword').attr('placeholder', '请输入您要搜索的商品关键字');
        $('#search ul.tab li span').eq(0).html('商品');
        $('#search-form').attr("action", HOMESITEURL+"/Search/index.html");

        $("#keyword").focus();
    });
});

/**
 * 
 * @param {type} formid   form表单ID
 * @param {type} type     返回类型  reload 刷新当前界面   default 保持默认状态不做刷新
 * @param {type} url      跳转的连接地址
 * @param {type} time     跳转的时间
 * @returns {undefined}
 */
function ds_ajaxpost(formid,type,url,waittime){
    if (typeof(waittime) == "undefined"){
        waittime = 1000;
    }
    var _form = $("#"+formid);
    $.ajax({
        type: "POST",
        url: _form.attr('action'),
        data: _form.serialize(),
        dataType:"json",
        success: function (res) {
            layer.msg(res.message, {time: waittime}, function () {
                if (res.code == 10000) {
                    if (typeof (type) == 'undefined' && type == null && type == '') {
                        location.reload();
                    } else if(type=='url') {
                        location.href = url;
                    } else if(type=='default') {
                        //不做操作只显示
                    }else{
                        location.reload();
                    }
                }
            });
        }
    });
}


/**
 * 
 * @param {type} url   URL链接地址
 * @param {type} type  返回类型  reload  remove(移除指定行) default默认不做任何操作
 * @param {type} param 参数
 * @returns {undefined}
 */
function ds_ajaxget(url,type,param)
{
    $.ajax({
        url: url,
        type: "get",
        dataType: "json",
        success: function (data) {
            layer.msg(data.message, {time: 1000}, function () {
                if (data.code == 10000) {
                    if (typeof (type) == "undefined" || type == null || type == '' || type=='reload') {
                        location.reload();
                    } else if (type == "remove") {
                        $("#ds_row_" + param).remove();
                    }else {
                        //不做操作
                    }
                }
            });
        }
    });
}
/**
 * 
 * @param {type} url   URL链接地址
 * @param {type} msg   显示提示内容
 * @param {type} type  返回类型  reload  remove(移除指定行) default默认不做任何操作
 * @param {type} param 参数
 * @returns {undefined}
 */
function ds_ajaxget_confirm(url,msg,type,param) {
    if (typeof (msg) != 'undefined' && msg != null && msg != '') {
        layer.confirm(msg, {
            btn: ['确定', '取消'],
            title: false,
        }, function () {
            ds_ajaxget(url,type,param);
        });
    }else{
        ds_ajaxget(url,type,param);
    }
}

/**
 * 
 * @param {type} msg  显示提示
 * @param {type} url  跳转URL 
 * @returns {undefined}
 */
function ds_get_confirm(msg, url){
    if(msg != ''){
        layer.confirm(msg, {
            btn: ['确定', '取消'],
            title: false,
        }, function () {
            window.location = url;
        });
    }else{
    	window.location = url;
    }
}


function go(url){
    window.location = url;
}


/* 格式化金额 */
function price_format(price){
    if(typeof(PRICE_FORMAT) == 'undefined'){
        PRICE_FORMAT = '&yen;%s';
    }
    price = number_format(price, 2);
    return price;
}
function number_format(num, ext){
    if(ext < 0){
        return num;
    }
    num = Number(num);
    if(isNaN(num)){
        num = 0;
    }
    var _str = num.toString();
    var _arr = _str.split('.');
    var _int = _arr[0];
    var _flt = _arr[1];
    if(_str.indexOf('.') == -1){
        /* 找不到小数点，则添加 */
        if(ext == 0){
            return _str;
        }
        var _tmp = '';
        for(var i = 0; i < ext; i++){
            _tmp += '0';
        }
        _str = _str + '.' + _tmp;
    }else{
        if(_flt.length == ext){
            return _str;
        }
        /* 找得到小数点，则截取 */
        if(_flt.length > ext){
            _str = _str.substr(0, _str.length - (_flt.length - ext));
            if(ext == 0){
                _str = _int;
            }
        }else{
            for(var i = 0; i < ext - _flt.length; i++){
                _str += '0';
            }
        }
    }

    return _str;
}

/* 火狐下取本地全路径 */
function getFullPath(obj)
{
    if(obj)
    {
        //ie
        if (window.navigator.userAgent.indexOf("MSIE")>=1)
        {
            obj.select();
            if(window.navigator.userAgent.indexOf("MSIE") == 25){
                obj.blur();
            }
            return document.selection.createRange().text;
        }
        //firefox
        else if(window.navigator.userAgent.indexOf("Firefox")>=1)
        {
            if(obj.files)
            {
                //return obj.files.item(0).getAsDataURL();
                return window.URL.createObjectURL(obj.files.item(0));
            }
            return obj.value;
        }
        return obj.value;
    }
}
/* 转化JS跳转中的 ＆ */
function transform_char(str)
{
    if(str.indexOf('&'))
    {
        str = str.replace(/&/g, "%26");
    }
    return str;
}

//图片垂直水平缩放裁切显示
(function($){
    $.fn.VMiddleImg = function(options) {
        var defaults={
            "width":null,
            "height":null
        };
        var opts = $.extend({},defaults,options);
        return $(this).each(function() {
            var $this = $(this);
            var objHeight = $this.height(); //图片高度
            var objWidth = $this.width(); //图片宽度
            var parentHeight = opts.height||$this.parent().height(); //图片父容器高度
            var parentWidth = opts.width||$this.parent().width(); //图片父容器宽度
            var ratio = objHeight / objWidth;
            if (objHeight > parentHeight && objWidth > parentWidth) {
                if (objHeight > objWidth) { //赋值宽高
                    $this.width(parentWidth);
                    $this.height(parentWidth * ratio);
                } else {
                    $this.height(parentHeight);
                    $this.width(parentHeight / ratio);
                }
                objHeight = $this.height(); //重新获取宽高
                objWidth = $this.width();
                if (objHeight > objWidth) {
                    $this.css("top", (parentHeight - objHeight) / 2);
                    //定义top属性
                } else {
                    //定义left属性
                    $this.css("left", (parentWidth - objWidth) / 2);
                }
            }
            else {
                if (objWidth > parentWidth) {
                    $this.css("left", (parentWidth - objWidth) / 2);
                }
                $this.css("top", (parentHeight - objHeight) / 2);
            }
        });
    };
})(jQuery);
function ResizeImage(ImgD,FitWidth,FitHeight){
    var image=new Image();
    image.src=ImgD.src;
    if(image.width>0 && image.height>0)
    {
        if(image.width/image.height>= FitWidth/FitHeight)
        {
            if(image.width>FitWidth)
            {
                ImgD.width=FitWidth;
                ImgD.height=(image.height*FitWidth)/image.width;
            }
            else
            {
                ImgD.width=image.width;
                ImgD.height=image.height;
            }
        }
        else
        {
            if(image.height>FitHeight)
            {
                ImgD.height=FitHeight;
                ImgD.width=(image.width*FitHeight)/image.height;
            }
            else
            {
                ImgD.width=image.width;
                ImgD.height=image.height;
            }
        }
    }
}

function trim(str) {
    return (str + '').replace(/(\s+)$/g, '').replace(/^\s+/g, '');
}
//弹出框登录
function login_dialog(){
    CUR_DIALOG = ajax_form('login','登录',HOMESITEURL+'/Login/login.html?inajax=1',360,1);
}

/* 显示Ajax表单 */
function ajax_form(id, title, url, width, model)
{
    if (!width)	width = 480;
    if (!model) model = 1;
    var d = DialogManager.create(id);
    d.setTitle(title);
    d.setContents('ajax', url);
    d.setWidth(width);
    d.show('center',model);
    return d;
}
//显示一个内容为自定义HTML内容的消息
function html_form(id, title, _html, width, model) {
    if (!width)	width = 480;
    if (!model) model = 0;
    var d = DialogManager.create(id);
    d.setTitle(title);
    d.setContents(_html);
    d.setWidth(width);
    d.show('center',model);
    return d;
}
//收藏商品js
function collect_goods(fav_id, jstype, jsobj) {
    $.get(HOMESITEURL+'/Index/login.html', function(result) {
        if (result == '0') {
            login_dialog();
        } else {
            var url = HOMESITEURL+'/Memberfavorites/favoritesgoods';
            $.getJSON(url, {'fid': fav_id}, function(data) {
                if (data.done)
                {
                    layer.msg(data.msg);
                    if (jstype == 'count') {
                        $('[dstype="' + jsobj + '"]').each(function() {
                            $(this).html(parseInt($(this).text()) + 1);
                        });
                    }
                    if (jstype == 'succ') {
                        $('[dstype="' + jsobj + '"]').each(function() {
                            $(this).html("收藏成功");
                        });
                    }
                }
                else
                {
                    layer.msg(data.msg);
                }
            });
        }
    });
}

//加载购物车信息
function load_cart_information() {
    $.getJSON(HOMESITEURL + '/Cart/ajax_load', function(result) {
        var obj = $('.header .user_menu .my-cart');
        var mini =$('#rtoolbar_cartlist');
        if (result) {
            var html = '';
            if (result.cart_goods_num > 0) {
                for (var i in result['list']) {
                    var goods = result['list'][i];
                    html += '<dl id="cart_item_' + goods['cart_id'] + '"><dt class="goods-name"><a href="' + goods['goods_url'] + '">' + goods['goods_name'] + '</a></dt>';
                    html += '<dd class="goods-thumb"><a href="' + goods['goods_url'] + '" title="' + goods['goods_name'] + '"><img src="' + goods['goods_image'] + '"></a></dd>';
                    html += '<dd class="goods-sales"></dd>';
                    html += '<dd class="goods-price"><em>&yen;' + goods['goods_price'] + '×' + goods['goods_num'] + '</dd>';
                    html += '<dd class="handle"><a href="javascript:void(0);" onClick="drop_topcart_item(' + goods['cart_id'] + ',' + goods['goods_id'] + ');">删除</a></dd>';
                    html += "</dl>";
                }
                obj.find('.incart-goods').html(html);
                obj.find('.incart-goods-box').perfectScrollbar('destroy');
                obj.find('.incart-goods-box').perfectScrollbar({suppressScrollX: true});
                html = "共<i>" + result.cart_goods_num + "</i>种商品&nbsp;&nbsp;总计金额：<em>&yen;" + result.cart_all_price + "</em>";
                obj.find('.total-price').html(html);
                mini.find('.total-price').html('<p>共<em class="goods-price" style="margin-left: 5px">' + result.cart_goods_num + '</em>种商品</p><p>共计：<em class="goods-price">&yen;' + result.cart_all_price + '</em></p>');
                if (obj.find('.addcart-goods-num').size() == 0) {
                    obj.append('<div class="addcart-goods-num">0</div>');
                }
                obj.find('.addcart-goods-num').html(result.cart_goods_num);
                $('#rtoobar_cart_count').html(result.cart_goods_num).show();
            } else {
                html = "<div class='no-order'><span>您的购物车中暂无商品，赶快选择心爱的商品吧！</span></div>";
                obj.find('.incart-goods').html(html);
                mini.find('.total-price').html(html);
                obj.find('.total-price').html('');
                if (obj.find('.addcart-goods-num').size() == 0) {
                    obj.append('<div class="addcart-goods-num">0</div>');
                }
                obj.find('.addcart-goods-num').html(result.cart_goods_num);
                $('#rtoobar_cart_count').html('').hide();
            }
        }
    });
}

//头部删除购物车信息，登录前使用goods_id,登录后使用cart_id
function drop_topcart_item(cart_id, goods_id) {
    $.getJSON(HOMESITEURL + '/Cart/del',{'cart_id': cart_id, 'goods_id': goods_id}, function(result) {
        if (result.state == 'true') {
            $("[ds_type='cart_item_"+cart_id+"']").remove();
            load_cart_information();
        } else {
            alert(result.msg);
        }
    });
}

//加载最近浏览的商品
function load_history_information(){
    $.getJSON(HOMESITEURL+'/Index/viewed_info.html', function(result){
        var obj = $('.header .user_menu .my-mall');
        if(result['m_id'] >0){
            if (typeof result['consult'] !== 'undefined') obj.find('#member_consult').html(result['consult']);
            if (typeof result['consult'] !== 'undefined') obj.find('#member_voucher').html(result['voucher']);
        }
        var goods_id = 0;
        var text_append = '';
        var n = 0;
        if (typeof result['viewed_goods'] !== 'undefined') {
            for (goods_id in result['viewed_goods']) {
                var goods = result['viewed_goods'][goods_id];
                text_append += '<li class="goods-thumb"><a href="'+goods['url']+'" title="'+goods['goods_name']+
                '" target="_blank"><img src="'+goods['goods_image']+'" alt="'+goods['goods_name']+'"></a>';
                text_append += '</li>';
                n++;
                if (n > 4) break;
            }
        }
        if (text_append == '') text_append = '<li class="no-goods">暂无商品</li>';;
        obj.find('.browse-history ul').html(text_append);
    });
}

/*
 * 弹出窗口
 */
(function($) {
    $.fn.ds_show_dialog = function(options) {

        var that = $(this);
        var settings = $.extend({}, {width: 480, title: '', close_callback: function() {}}, options);

        var init_dialog = function(title) {
            var _div = that;
            that.addClass("dialog_wrapper");
            that.wrapInner(function(){
                return '<div class="dialog_content">';
            });
            that.wrapInner(function(){
                return '<div class="dialog_body" style="position: relative;">';
            });
            that.find('.dialog_body').prepend('<h3 class="dialog_head" style="cursor: move;"><span class="dialog_title"><span class="dialog_title_icon">'+settings.title+'</span></span><span class="dialog_close_button">X</span></h3>');
            that.append('<div style="clear:both;"></div>');

            $(".dialog_close_button").click(function(){
                settings.close_callback();
                _div.hide();
            });

            that.draggable({handle: ".dialog_head"});
        };

        if(!$(this).hasClass("dialog_wrapper")) {
            init_dialog(settings.title);
        }
        settings.left = $(window).scrollLeft() + ($(window).width() - settings.width) / 2;
        settings.top  = ($(window).height() - $(this).height()) / 2;
        $(this).attr("style","display:none; z-index: 1100; position: fixed; width: "+settings.width+"px; left: "+settings.left+"px; top: "+settings.top+"px;");
        $(this).show();

    };
})(jQuery);





(function($) {
    $.fn.ds_region = function(options) {
        var $region = $(this);
        var settings = $.extend({}, {
            area_id: 0,
            region_span_class: "_region_value",
            src: "cache",
            show_deep: 0,
            btn_style_html: "",
            tip_type: ""
        }, options);
        settings.islast = false;
        settings.selected_deep = 0;
        settings.last_text = "";
        this.each(function() {
            var $inputArea = $(this);
            if ($inputArea.val() === "") {
                initArea($inputArea)
            } else {
                var $region_span = $('<span id="_area_span" class="' + settings.region_span_class + '">' + $inputArea.val() + "</span>");
                var $region_btn = $('<input type="button" class="input-btn" ' + settings.btn_style_html + ' value="编辑" />');
                $inputArea.after($region_span);
                $region_span.after($region_btn);
                $region_btn.on("click", function() {
                    $region_span.remove();
                    $region_btn.remove();
                    initArea($inputArea)
                });
                settings.islast = true
            }
            this.settings = settings;
            if ($inputArea.val() && /^\d+$/.test($inputArea.val())) {
                $.getJSON(HOMESITEURL  + "/Index/json_area_show?area_id=" + $inputArea.val() + "&callback=?", function(data) {
                    $("#_area_span").html(data.text == null ? "无" : data.text)
                })
            }
        });

        function initArea($inputArea) {
            settings.$area = $("<select></select>");
            $inputArea.before(settings.$area);
            loadAreaArray(function() {
                loadArea(settings.$area, settings.area_id)
            })
        }
        function loadArea($area, area_id) {
            if ($area && ds_a[area_id].length > 0) {
                var areas = [];
                areas = ds_a[area_id];
                if (settings.tip_type && settings.last_text != "") {
                    $area.append("<option value=''>" + settings.last_text + "(*)</option>")
                } else {
                    $area.append("<option value=''>-请选择-</option>")
                }
                for (i = 0; i < areas.length; i++) {
                    $area.append("<option value='" + areas[i][0] + "'>" + areas[i][1] + "</option>")
                }
                settings.islast = false
            }
            $area.on("change", function() {
                var region_value = "",
                    area_ids = [],
                    selected_deep = 1;
                $(this).nextAll("select").remove();
                $region.parent().find("select").each(function() {
                    if ($(this).find("option:selected").val() != "") {
                        region_value += $(this).find("option:selected").text() + " ";
                        area_ids.push($(this).find("option:selected").val())
                    }
                });
                settings.selected_deep = area_ids.length;
                settings.area_ids = area_ids.join(" ");
                $region.val(region_value);
                settings.area_id_1 = area_ids[0] ? area_ids[0] : "";
                settings.area_id_2 = area_ids[1] ? area_ids[1] : "";
                settings.area_id_3 = area_ids[2] ? area_ids[2] : "";
                settings.area_id_4 = area_ids[3] ? area_ids[3] : "";
                settings.last_text = $region.prevAll("select").find("option:selected").last().text();
                var area_id = settings.area_id = $(this).val();
                if ($('#_area_1').length > 0) $("#_area_1").val(settings.area_id_1);
                if ($('#_area_2').length > 0) $("#_area_2").val(settings.area_id_2);
                if ($('#_area_3').length > 0) $("#_area_3").val(settings.area_id_3);
                if ($('#_area_4').length > 0) $("#_area_4").val(settings.area_id_4);
                if ($('#_area').length > 0) $("#_area").val(settings.area_id);
                if ($('#_areas').length > 0) $("#_areas").val(settings.area_ids);
                if (settings.show_deep > 0 && $region.prevAll("select").size() == settings.show_deep) {
                    settings.islast = true;
                    if (typeof settings.last_click == 'function') {
                        settings.last_click(area_id);
                    }
                    return
                }
                if (area_id > 0) {
                    if (ds_a[area_id] && ds_a[area_id].length > 0) {
                        var $newArea = $("<select></select>");
                        $(this).after($newArea);
                        loadArea($newArea, area_id);
                        settings.islast = false
                    } else {
                        settings.islast = true;
                        if (typeof settings.last_click == 'function') {
                            settings.last_click(area_id);
                        }
                    }
                } else {
                    settings.islast = false
                }
                if ($('#islast').length > 0) $("#islast").val("");
            })
        }
        function loadAreaArray(callback) {
            if (typeof ds_a === "undefined") {
                $.getJSON(HOMESITEURL  + "/Index/json_area.html?src=" + settings.src + "&callback=?", function(data) {
                    ds_a = data;
                    callback()
                })
            } else {
                callback()
            }
        }
        if (typeof jQuery.validator != 'undefined') {
            jQuery.validator.addMethod("checklast", function(value, element) {
                return $(element).fetch('islast');
            }, "请将地区选择完整");
        }
    };
    $.fn.fetch = function(k) {
        var p;
        this.each(function() {
            if (this.settings) {
                p = eval("this.settings." + k);
                return false
            }
        });
        return p
    }


})(jQuery);

/* 加入购物车 */
function addcart(goods_id, quantity, callbackfunc,dir) {

    var url = HOMESITEURL + '/Cart/add.html';
    quantity = parseInt(quantity);
    $.getJSON(url, {'goods_id': goods_id, 'quantity': quantity}, function(data) {
        if (data != null) {
            if (data.state) {
                if (callbackfunc) {
                    eval(callbackfunc + "(data)");
                }
                // 头部加载购物车信息
                load_cart_information();
                $("#rtoolbar_cartlist").load(HOMESITEURL + '/Cart/ajax_load?type=html');
                if(dir) {
                    layer.msg('添加购物车成功');
                }
            } else {
                layer.msg(data.msg);
            }
        }
    });
}

function setCookie(name, value, days) {
    var exp = new Date();
    exp.setTime(exp.getTime() + days * 24 * 60 * 60 * 1000);
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    document.cookie = name + "=" + escape(value) + ";expires=" + exp.toGMTString();
}
function getCookie(name) {
    var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
    if (arr != null) {
        return unescape(arr[2]);
        return null;
    }
}
function delCookie(name) {
    var exp = new Date();
    exp.setTime(exp.getTime() - 1);
    var cval = getCookie(name);
    if (cval != null) {
        document.cookie = name + "=" + cval + ";expires=" + exp.toGMTString();
    }
}


(function($) {
    $.fn.F_slider = function(options){
        var defaults = {
            page : 1,
            len : 0,		// 滚动篇幅
            axis : 'y'		// y为上下滚动，x为左右滚动
        }
        var options = $.extend(defaults,options);
        return this.each(function(){
            var $this = $(this);
            var len = options.len;
            var page = options.page;
            if(options.axis == 'y'){
                var Val = $(this).find('.F-center').height();
                var Param = 'top';
            }else if(options.axis == 'x'){
                var Val = $(this).find('.F-center').parent().width();
                var Param = 'left';
            }
            $this.find('.F-prev').click(function(){
                if( page == 1){
                    eval("$this.find('.F-center').animate({"+Param+":'-=' + Val*(len-1)},'slow');");
                    page=len;
                }else{
                    eval("$this.find('.F-center').animate({"+Param+":'+=' + Val},'slow');");
                    page--;
                }
            });
            $this.find('.F-next').click(function(){
                if(page == len){
                    eval("$this.find('.F-center').animate({"+Param+":0},'slow');");
                    page=1;
                }else{
                    eval("$this.find('.F-center').animate({"+Param+":'-=' + Val},'show');");
                    page++;
                }
            });
        });
    }
})(jQuery);

//获取翻页输入框里的页数
$(function(){
    $(document).on('input', '.form-control', function() {
        var val = $(this).val();
        var url = $(this).next().next().attr('href');
        if(url.indexOf("page=") >= 0){
          var ipos = url.indexOf('page=');
          var href = url.substring(0,ipos+5) + val; 
        }else{
            if(url.indexOf("?") >= 0){
               var href = url+'&page=' + val; 
            }else{
               var href = url+'?page=' + val; 
            }
        }
        $(this).next().next().attr("href",href);
    });
})
