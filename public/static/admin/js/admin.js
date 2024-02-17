$(function() {
    //自定义radio 模拟 switch样式
    $(".cb-enable").click(function() {
        var parent = $(this).parents('.onoff');
        $('.cb-disable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox', parent).prop('checked', true);
    });
    $(".cb-disable").click(function() {
        var parent = $(this).parents('.onoff');
        $('.cb-enable', parent).removeClass('selected');
        $(this).addClass('selected');
        $('.checkbox', parent).prop('checked', false);
    });
    //公共复选框点击切换样式
    $(".checkbox-common").on("click", function () {
        //如果样式是选中设置input为不选中
        if ($(this).hasClass('selected')) {
            $(this).children("input").prop("checked", false);
        }
        var checkbox = $(this).children("input");
        if (checkbox.is(":checked")) {
            $(this).addClass("selected");
        } else {
            $(this).removeClass("selected");
        }
    });
    //公共单选框点击切换样式
    $(".radio-common").on("click", function () {
        var radio = $(this).children("input");
        var name = radio.attr("name");
        if (radio.is(":checked")) {
            $(".radio-common>input[type='radio'][name='" + name + "']").parent().removeClass("selected");
            $(this).addClass("selected");
        } else {
            $(this).removeClass("selected");
        }
    });
    
});
$(function() {
    // 显示隐藏预览图 start
    $('.show_image').hover(
            function() {
                $(this).next().css('display', 'block');
            },
            function() {
                $(this).next().css('display', 'none');
            }
    );

    // 全选 start
    $('.checkall').click(function() {
        var _self = this.checked;
        $('.checkitem').each(function() {
            $(this).prop('checked', _self);
        });
        $('.checkall').prop('checked', _self);
    });

    // 表格鼠标悬停变色 start
    $("tbody tr").hover(
            function() {
                $(this).css({background: "#FBFBFB"});
            },
            function() {
                $(this).css({background: "#FFF"});
            });

    // 可编辑列（input）变色
    $('.editable').hover(
            function() {
                $(this).removeClass('editable').addClass('editable2');
            },
            function() {
                $(this).removeClass('editable2').addClass('editable');
            }
    );

    // 提示操作 展开与隐藏
    $("#checkZoom").click(function() {
        $(this).next("ul").toggle(800);
        $(this).find(".arrow").toggleClass("up");
    });

    // 可编辑列（area）变色
    $('.editable-tarea').hover(
            function() {
                $(this).removeClass('editable-tarea').addClass('editable-tarea2');
            },
            function() {
                $(this).removeClass('editable-tarea2').addClass('editable-tarea');
            }
    );
    // 初始化上传
    trigger_uploader();
});


/* AJAX选择品牌 */
(function($) {
    $.fn.brandinit = function(options) {
        
        var brand_container = $(this);
        //根据首字母查询
        $(this).find('.letter[dstype="letter"]').find('a[data-letter]').click(function() {
            var _url = $(this).parents('.brand-index:first').attr('data-url');
            var _letter = $(this).attr('data-letter');
            var _search = $(this).html();
            $.getJSON(_url, {type: 'letter', letter: _letter}, function(data) {
                $(brand_container).insertBrand({param: data, search: _search});
            });
        });
        
        // 根据关键字查询
        $(this).find('.search[dstype="search"]').find('a').click(function() {
            var _url = $(this).parents('.brand-index:first').attr('data-url');
            var _keyword = $('#search_brand_keyword').val();
            $.getJSON(_url, {type: 'keyword', keyword: _keyword}, function(data) {
                $(brand_container).insertBrand({param: data, search: _keyword});
            });
        });
        // 选择品牌
        $(this).find('ul[dstype="brand_list"]').on('click', 'li', function() {
            $('#b_id').val($(this).attr('data-id'));
            $('#b_name').val($(this).attr('data-name'));
        });
        //搜索品牌列表滚条绑定
        $(this).find('div[dstype="brandList"]').perfectScrollbar();
    }
    $.fn.insertBrand = function(options) {
        //品牌搜索容器
        var dataContainer = $(this);
        $(dataContainer).find('div[dstype="brandList"]').show();
        $(dataContainer).find('div[dstype="noBrandList"]').hide();
        var _ul = $(dataContainer).find('ul[dstype="brand_list"]');
        _ul.html('');
        if ($.isEmptyObject(options.param)) {
            $(dataContainer).find('div[dstype="brandList"]').hide();
            $(dataContainer).find('div[dstype="noBrandList"]').show().find('strong').html(options.search);
            return false;
        }
        $.each(options.param, function(i, n) {
            $('<li data-id="' + n.brand_id + '" data-name="' + n.brand_name + '"><em>' + n.brand_initial + '</em>' + n.brand_name + '</li>').appendTo(_ul);
        });

        //搜索品牌列表滚条绑定
        $(dataContainer).find('div[dstype="brandList"]').perfectScrollbar('update');
    };
})(jQuery);


/**
 * Layer 通用ifram弹出窗口
 */
function dsLayerOpen(url, title,width,height) {
    if (!width)	width = '900px';
    if (!height) height = '500px';
    layer.open({
        type: 2,
        title: title,
        area: [width,height],
        fixed: false, //不固定
        maxmin: true,
        content: url
    });
}

/**
 * Layer 通用confirm弹出窗口
 */
function dsLayerConfirm(url, title) {
    layer.confirm(title, {
        btn: ['确定', '取消'],
        title: false,
    }, function () {
        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            success: function (data) {
               if(data.code == 10000){
                   location.reload();
               }else{
                   layer.msg(data.message, {icon: 2})
               }
            }
        });
    });
}

/**
 * 批量删除
 */
function submit_delete_batch() {
    /* 获取选中的项 */
    var items = '';
    $('.checkitem:checked').each(function () {
        items += this.value + ',';
    });
    if (items != '') {
        items = items.substr(0, (items.length - 1));
        submit_delete(items);
    }else{
        layer.alert('请勾选选项', {icon: 2})  
    }
}

/**
 * 导出XLS
 * @param {type} url
 */
function export_xls(url)
{
    var inputs = $(".ds-search-form").find("input:text");
    var selects = $(".ds-search-form").find("select");
    var param = '';
    for (i = 0; i < inputs.length; i++)
    {
        if (inputs[i].name != 'controller' && inputs[i].name != 'action')
            param += "&" + inputs[i].name + "=" + $(inputs[i]).val();
    }
    for (i = 0; i < selects.length; i++)
    {
        param += "&" + selects[i].name + "=" + $(selects[i]).val();
    }
    location.href = url + param.replace("&", "?");
}


function trigger_uploader(){
    // 打开商品图片上传器
    $('#open_uploader').unbind('click');
    $('#open_uploader').click(function(){
        if($('#uploader').css('display') == 'none'){
            $('#uploader').show();
            $(this).find('.hide').attr('class','show');
        }else{
            $('#uploader').hide();
            $(this).find('.show').attr('class','hide');
        }
    });
    /* 悬停解释 */
    $('*[ecm_title]').hover(function(){
        $('*[ds_type="explain_layer"]').remove();
        $(this).parent().parent().append('<div class="titles" ds_type="explain_layer" style="display:none; z-index:999">' + $(this).attr('ecm_title') + '<div class="line"></div></div>');
        $('*[ds_type="explain_layer"]').fadeIn();
    },
    function(){
        $('*[ds_type="explain_layer"]').fadeOut();
    }
    );
}
