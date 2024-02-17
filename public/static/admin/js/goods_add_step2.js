$(function(){
    // 取消回车提交表单 
    $('input').keypress(function(e){
        var key = window.event ? e.keyCode : e.which;
        if (key.toString() == "13") {
         return false;
        }
    });
    // 添加店铺分类
    $("#add_sgcategory").unbind().click(function(){
        $(".sgcategory:last").after($(".sgcategory:last").clone(true).val(0));
    });
    // 选择店铺分类
    $('.sgcategory').unbind().change( function(){
        var _val = $(this).val();       // 记录选择的值
        $(this).val('0');               // 已选择值清零
        // 验证是否已经选择
        if (!checkSGC(_val)) {
            alert('该分类已经选择,请选择其他分类');
            return false;
        }
        $(this).val(_val);              // 重新赋值
    });
    /* 商品图片ajax上传 */
    $('#goods_image').fileupload({
        dataType: 'json',
        url: ADMINSITEURL + '/Goodsadd/image_upload.html?upload_type=uploadedfile',
        formData: function(form){
            var aclass_id=$("#demo select[name=jumpMenu]").val()
            return [{name:'name',value:'goods_image'},{name:'aclass_id',value:aclass_id?aclass_id:0}];
        },
        add: function (e,data) {
        	$('img[dstype="goods_image"]').attr('src', ADMINSITEROOT + '/images/loading.gif');
            data.submit();
        },
        done: function (e,data) {
            var param = data.result;
            if (typeof(param.error) != 'undefined') {
                alert(param.error);
                $('img[dstype="goods_image"]').attr('src',DEFAULT_GOODS_IMAGE);
            } else {
                $('input[dstype="goods_image"]').val(param.name);
                $('img[dstype="goods_image"]').attr('src',param.thumb_name);
            }
        }
    });
    /* ajax打开图片空间 */
    // 商品主图使用
    $('a[dstype="show_image"]').unbind().ajaxContent({
        event:'click', //mouseover
        loaderType:"img",
        loadingMsg:ADMINSITEROOT+"/images/loading.gif",
        target:'#demo'
    }).click(function(){
        $(this).hide();
        $('a[dstype="del_goods_demo"]').show();
    });
    $('a[dstype="del_goods_demo"]').unbind().click(function(){
        $('#demo').html('');
        $(this).hide();
        $('a[dstype="show_image"]').show();
    });
    //商品主图翻页
    $('#demo').on('click', '.pagination li a', function() {
        $('#demo').load($(this).attr('href'));
        return false;
    });
    // 商品描述使用
    $('a[dstype="show_desc"]').unbind().ajaxContent({
        event:'click', //mouseover
        loaderType:"img",
        loadingMsg:ADMINSITEROOT+"/images/loading.gif",
        target:'#des_demo'
    }).click(function(){
        $(this).hide();
        $('a[dstype="del_desc"]').show();
    });
    
    $('a[dstype="del_desc"]').click(function(){
        $('#des_demo').html('');
        $(this).hide();
        $('a[dstype="show_desc"]').show();
    });
    //商品描述图片翻页
    $('#des_demo').on('click', '.pagination li a', function() {
        $('#des_demo').load($(this).attr('href'));
        return false;
    });
    $('#add_album').fileupload({
        dataType: 'json',
        url: ADMINSITEURL+'/Goodsadd/image_upload.html',
        formData: function(form){
            var aclass_id=$("#des_demo select[name=jumpMenu]").val()
            return [{name:'name',value:'add_album'},{name:'aclass_id',value:aclass_id?aclass_id:0}];
        },
        add: function (e,data) {
           $('i[dstype="add_album_i"]').html("&#xe717;").addClass('rotate').attr('data_type', parseInt($('i[dstype="add_album_i"]').attr('data_type'))+1);
            data.submit();
        },
        done: function (e,data) {
            var _counter = parseInt($('i[dstype="add_album_i"]').attr('data_type'));
            _counter -= 1;
            if (_counter == 0) {
                $('i[dstype="add_album_i"]').removeClass('rotate').html("&#xe733;");
                $('a[dstype="show_desc"]').click();
            }
            $('i[dstype="add_album_i"]').attr('data_type', _counter);
        }
    });
    /* ajax打开图片空间 end */
    
    // 商品属性
    attr_selected();
    $('select[ds_type="attr_select"]').change(function(){
        id = $(this).find('option:selected').attr('ds_type');
        name = $(this).attr('attr').replace(/__NC__/g,id);
        $(this).attr('name',name);
    });
    
    // 修改规格名称
    $('dl[dstype="spec_group_dl"]').on('click', 'input[type="checkbox"]', function(){
        pv = $(this).parents('li').find('span[dstype="pv_name"]');
        if(typeof(pv.find('input').val()) == 'undefined'){
            pv.html('<input type="text" maxlength="20" class="text" value="'+pv.html()+'" />');
        }else{
            pv.html(pv.find('input').val());
        }
    });
    $('dl[dstype="spec_group_dl"]').on('change','span[dstype="pv_name"] > input',function(){
        change_img_name($(this));       // 修改相关的颜色名称
        into_array();           // 将选中的规格放入数组
        goods_stock_set();      // 生成库存配置
    });
    
    // 运费部分显示隐藏
    $('input[dstype="freight"]').click(function(){
            $('input[dstype="freight"]').nextAll('div[dstype="div_freight"]').hide();
            $(this).nextAll('div[dstype="div_freight"]').show();
    });
    
    // 商品所在地
    /*德尚网络待完善 BEGIN*/

    
    // 定时发布时间
    $('#starttime').datepicker({dateFormat: 'yy-mm-dd'});
    
    $('input[name="g_state"]').click(function(){
        if($(this).attr('dstype') == 'auto'){
            $('#starttime').removeAttr('disabled').css('background','');
            $('#starttime_H').removeAttr('disabled').css('background','');
            $('#starttime_i').removeAttr('disabled').css('background','');
        }else{
            $('#starttime').prop('disabled','disabled').css('background','#E7E7E7 none');
            $('#starttime_H').prop('disabled','disabled').css('background','#E7E7E7 none');
            $('#starttime_i').prop('disabled','disabled').css('background','#E7E7E7 none');
        }
    });
    
    // 计算折扣
    $('input[name="g_price"],input[name="g_marketprice"]').change(function(){
        discountCalculator();
    });
    

    /* AJAX添加规格值 */
    // 添加规格
    $('a[dstype="specAdd"]').click(function(){
        
        var _parent = $(this).parents('li:first');
        _parent.find('div[dstype="specAdd1"]').hide();
        _parent.find('div[dstype="specAdd2"]').show();
        _parent.find('input').focus();
    });
    // 取消
    $('a[dstype="specAddCancel"]').click(function(){
        var _parent = $(this).parents('li:first');
        _parent.find('div[dstype="specAdd1"]').show();
        _parent.find('div[dstype="specAdd2"]').hide();
        _parent.find('input').val('');
    });
    // 提交
    $('a[dstype="specAddSubmit"]').click(function(){
        var _parent = $(this).parents('li:first');
        eval('var data_str = ' + _parent.attr('data-param'));
        var _input = _parent.find('input');
        _parent.find('div[dstype="specAdd1"]').show();
        _parent.find('div[dstype="specAdd2"]').hide();
        $.getJSON(data_str.url, {gc_id : data_str.gc_id , sp_id : data_str.sp_id , name : _input.val()}, function(data){
            if (data.done) {
                _parent.before('<li><span dstype="input_checkbox"><input type="checkbox" name="sp_val[' + data_str.sp_id + '][' + data.value_id + ']" ds_type="' + data.value_id + '" value="' +_input.val()+ '" /></span><span dstype="pv_name">' + _input.val() + '</span></li>');
                _input.val('');
            }
        });
    });
    // 修改规格名称
    $('input[dstype="spec_name"]').change(function(){
        eval('var data_str = ' + $(this).attr('data-param'));
        if ($(this).val() == '') {
            $(this).val(data_str.name);
        }
        $('th[dstype="spec_name_' + data_str.id + '"]').html($(this).val());
    });
    // 批量设置价格、库存、预警值
    $('.batch > i').click(function(){
        $('.batch > .batch-input').hide();
        $(this).next().show();
    });
    $('.batch-input > .close').click(function(){
        $(this).parent().hide();
    });
    $('.batch-input > .dssc-btn-mini').click(function(){
        var _value = $(this).prev().val();
        var _type = $(this).attr('data-type');
        if (_type == 'price' || _type == 'marketprice' ) {
            _value = number_format(_value, 2);
        } else {
            _value = parseInt(_value);
        }
        if (_type == 'alarm' && _value > 255) {
            _value = 255;
        }
        if (isNaN(_value)) {
            _value = 0;
        }
        $('input[data_type="' + _type + '" ]').val(_value);
        $(this).parent().hide();
        $(this).prev().val('');
        if (_type == 'price') {
            computePrice();
        }
        if (_type == 'stock') {
            computeStock();
        }
    });
    
    /* AJAX选择品牌 */
    // 根据首字母查询
    $('.letter[dstype="letter"]').find('a[data-letter]').click(function(){
        var _url = $(this).parents('.brand-index:first').attr('data-url');
        var _tid = $(this).parents('.brand-index:first').attr('data-tid');
        var _letter = $(this).attr('data-letter');
        var _search = $(this).html();
        $.getJSON(_url, {type : 'letter', tid : _tid, letter : _letter}, function(data){
            insertBrand(data, _search);
        });
    });
	 $('.letter[dstype="letter"]').find('a[data-empty]').click(function(){
		 $('#b_name').val("");
		 });
	
	
    // 根据关键字查询
    $('.search[dstype="search"]').find('a').click(function(){
        var _url = $(this).parents('.brand-index:first').attr('data-url');
        var _tid = $(this).parents('.brand-index:first').attr('data-tid');
        var _keyword = $('#search_brand_keyword').val();
        $.getJSON(_url, {type : 'keyword', tid : _tid, keyword : _keyword}, function(data){
            insertBrand(data, _keyword);
        });
    });
    // 选择品牌
    $('ul[dstype="brand_list"]').on('click', 'li', function(){
        $('#b_id').val($(this).attr('data-id'));
        $('#b_name').val($(this).attr('data-name'));
        $('.dssc-brand-select > .dssc-brand-select-container').hide();
    });
    
    //搜索品牌列表滚条绑定
    $('div[dstype="brandList"]').perfectScrollbar();
    
    $('select[name="b_id"]').change(function(){
        getBrandName();
    });
    
    $('input[name="b_name"]').focus(function(){
        $('.dssc-brand-select > .dssc-brand-select-container').show();
    });
	//下拉隐藏显示品牌列表
        $('.add-on[dstype="add-on"]').click(function(){
            $('.dssc-brand-select > .dssc-brand-select-container').fadeToggle();
        });
    
    //Ajax提示
    /*德尚网络待完善 BEGIN*/
//    $('.tip').poshytip({
//        className: 'tip-yellowsimple',
//        showTimeout: 1,
//        alignTo: 'target',
//        alignX: 'left',
//        alignY: 'top',
//        offsetX: 5,
//        offsetY: -78,
//        allowTipHover: false
//    });
//    $('.tip2').poshytip({
//        className: 'tip-yellowsimple',
//        showTimeout: 1,
//        alignTo: 'target',
//        alignX: 'right',
//        alignY: 'center',
//        offsetX: 5,
//        offsetY: 0,
//        allowTipHover: false
//    });
    
    /* 虚拟控制 */
    // 虚拟商品有效期
    $('#g_vindate').datepicker({dateFormat: 'yy-mm-dd', minDate: new Date()});
    $('[name="is_gv"]').change(function(){
        if ($('#is_gv_1').prop("checked")) {
            $('#is_goodsfcode_0').click();          // 虚拟商品不能发布F码，取消选择F码
            $('#is_presell_0').click();     // 虚拟商品不能设置预售，取消选择预售
            $('[dstype="virtual_valid"]').show();
            $('[dstype="virtual_null"]').hide();
        } else {
            $('[dstype="virtual_valid"]').hide();
            $('[dstype="virtual_null"]').show();
            $('#g_vindate').val('');
            $('#g_vlimit').val('');
        }
    });
    
    /* F码控制 */
    $('[name="is_fc"]').change(function(){
        if ($('#is_goodsfcode_1').prop("checked")) {
            $('[dstype="fcode_valid"]').show();
        } else {
            $('[dstype="fcode_valid"]').hide();
            $('#g_fccount').val('');
            $('#g_fcprefix').val('');
        }
    });
    
    /* 预售控制 */
    // 预售--发货时间
    $('#g_deliverdate').datepicker({dateFormat: 'yy-mm-dd', minDate: new Date()});
    $('[name="is_presell"]').change(function(){
        if ($('#is_presell_1').prop("checked")) {
            $('[dstype="is_presell"]').show();
        } else {
            $('[dstype="is_presell"]').hide();
        }
    });
    
    /* 预约预售控制 */
    // 预约--出售时间
    $('#g_saledate').datepicker({dateFormat: 'yy-mm-dd', minDate: new Date()});
    $('[name="is_appoint"]').change(function(){
        if ($('#is_appoint_1').prop("checked")) {
            $('[dstype="is_appoint"]').show();
        } else {
            $('[dstype="is_appoint"]').hide();
        }
    });
    
    /* 手机端 商品描述 */
    // 显示隐藏控制面板
    $('div[dstype="mobile_pannel"]').on('click', '.module', function(){
        mbPannelInit();
        $(this).siblings().removeClass('current').end().addClass('current');
    });
    // 上移
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="mp_up"]', function(){
        var _parents = $(this).parents('.module:first');
        _rs = mDataMove(_parents.index(), 0);
        if (!_rs) {
            return false;
        }
        _parents.clone().insertBefore(_parents.prev()).end().remove();
        mbPannelInit();
    });
    // 下移
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="mp_down"]', function(){
        var _parents = $(this).parents('.module:first');
        _rs = mDataMove(_parents.index(), 1);
        if (!_rs) {
            return false;
        }
        _parents.clone().insertAfter(_parents.next()).end().remove();
        mbPannelInit();
    });
    // 删除
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="mp_del"]', function(){
        var _parents = $(this).parents('.module:first');
        mDataRemove(_parents.index());
        _parents.remove();
        mbPannelInit();
    });
    // 编辑
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="mp_edit"]', function(){
        $('a[dstype="meat_cancel"]').click();
        var _parents = $(this).parents('.module:first');
        var _val = _parents.find('.text-div').html();
        $(this).parents('.module:first').html('')
            .append('<div class="content"></div>').find('.content')
            .append('<div class="dssc-mea-text" dstype="mea_txt"></div>')
            .find('div[dstype="mea_txt"]')
            .append('<p id="meat_content_count" class="text-tip">')
            .append('<textarea class="textarea valid" data-old="' + _val + '" dstype="meat_content">' + _val + '</textarea>')
            .append('<div class="button"><a class="dssc-btn dssc-btn-blue" dstype="meat_edit_submit" href="javascript:void(0);">确认</a><a class="dssc-btn ml10" dstype="meat_edit_cancel" href="javascript:void(0);">取消</a></div>')
            .append('<a class="text-close" dstype="meat_edit_cancel" href="javascript:void(0);">X</a>')
            .find('#meat_content_count').html('').end()
            .find('textarea[dstype="meat_content"]').unbind().charCount({
                allowed: 500,
                warning: 50,
                counterContainerID: 'meat_content_count',
                firstCounterText:   '还可以输入',
                endCounterText:     '字',
                errorCounterText:   '已经超出'
            });
    });
    // 编辑提交
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="meat_edit_submit"]', function(){
        var _parents = $(this).parents('.module:first');
        var _c = toTxt(_parents.find('textarea[dstype="meat_content"]').val().replace(/[\r\n]/g,''));
        var _cl = _c.length;
        if (_cl == 0 || _cl > 500) {
            return false;
        }
        _data = new Object;
        _data.type = 'text';
        _data.value = _c;
        _rs = mDataReplace(_parents.index(), _data);
        if (!_rs) {
            return false;
        }
        _parents.html('').append('<div class="tools"><a dstype="mp_up" href="javascript:void(0);">上移</a><a dstype="mp_down" href="javascript:void(0);">下移</a><a dstype="mp_edit" href="javascript:void(0);">编辑</a><a dstype="mp_del" href="javascript:void(0);">删除</a></div>')
            .append('<div class="content"><div class="text-div">' + _c + '</div></div>')
            .append('<div class="cover"></div>');

    });
    // 编辑关闭
    $('div[dstype="mobile_pannel"]').on('click', '[dstype="meat_edit_cancel"]', function(){
        var _parents = $(this).parents('.module:first');
        var _c = _parents.find('textarea[dstype="meat_content"]').attr('data-old');
        _parents.html('').append('<div class="tools"><a dstype="mp_up" href="javascript:void(0);">上移</a><a dstype="mp_down" href="javascript:void(0);">下移</a><a dstype="mp_edit" href="javascript:void(0);">编辑</a><a dstype="mp_del" href="javascript:void(0);">删除</a></div>')
        .append('<div class="content"><div class="text-div">' + _c + '</div></div>')
        .append('<div class="cover"></div>');
    });
    // 初始化控制面板
    mbPannelInit = function(){
        $('div[dstype="mobile_pannel"]')
            .find('a[dstype^="mp_"]').show().end()
            .find('.module')
            .first().find('a[dstype="mp_up"]').hide().end().end()
            .last().find('a[dstype="mp_down"]').hide();
    }
    // 添加文字按钮，显示文字输入框
    $('a[dstype="mb_add_txt"]').click(function(){
        $('div[dstype="mea_txt"]').show();
        $('a[dstype="meai_cancel"]').click();
    
    $('div[dstype="mobile_editor_area"]').find('textarea[dstype="meat_content"]').unbind().charCount({
        allowed: 500,
        warning: 50,
        counterContainerID: 'meat_content_count',
        firstCounterText:   '还可以输入',
        endCounterText:     '字',
        errorCounterText:   '已经超出'
    })});
    // 关闭 文字输入框按钮
    $('a[dstype="meat_cancel"]').click(function(){
        $(this).parents('div[dstype="mea_txt"]').find('textarea[dstype="meat_content"]').val('').end().hide();
    });
    // 提交 文字输入框按钮
    $('a[dstype="meat_submit"]').click(function(){
        var _c = toTxt($('textarea[dstype="meat_content"]').val().replace(/[\r\n]/g,''));
        var _cl = _c.length;
        if (_cl == 0 || _cl > 500) {
            return false;
        }
        _data = new Object;
        _data.type = 'text';
        _data.value = _c;
        _rs = mDataInsert(_data);
        if (!_rs) {
            return false;
        }
        $('<div class="module m-text"></div>')
            .append('<div class="tools"><a dstype="mp_up" href="javascript:void(0);">上移</a><a dstype="mp_down" href="javascript:void(0);">下移</a><a dstype="mp_edit" href="javascript:void(0);">编辑</a><a dstype="mp_del" href="javascript:void(0);">删除</a></div>')
            .append('<div class="content"><div class="text-div">' + _c + '</div></div>')
            .append('<div class="cover"></div>').appendTo('div[dstype="mobile_pannel"]');
        
        $('a[dstype="meat_cancel"]').click();
    });
    // 添加图片按钮，显示图片空间文字
    $('a[dstype="mb_add_img"]').click(function(){
        $('a[dstype="meat_cancel"]').click();
        $('div[dstype="mea_img"]').show().load(ADMINSITEURL+'/Goodsalbum/pic_list?item=mobile');
    });
    // 关闭 图片选择
    $('div[dstype="mobile_editor_area"]').on('click', 'a[dstype="meai_cancel"]', function(){
        $('div[dstype="mea_img"]').html('');
    });
    // 插图图片
    insert_mobile_img = function(data){
        _data = new Object;
        _data.type = 'image';
        _data.value = data;
        _rs = mDataInsert(_data);
        if (!_rs) {
            return false;
        }
        $('<div class="module m-image"></div>')
            .append('<div class="tools"><a dstype="mp_up" href="javascript:void(0);">上移</a><a dstype="mp_down" href="javascript:void(0);">下移</a><a dstype="mp_rpl" href="javascript:void(0);">替换</a><a dstype="mp_del" href="javascript:void(0);">删除</a></div>')
            .append('<div class="content"><div class="image-div"><img src="' + data + '"></div></div>')
            .append('<div class="cover"></div>').appendTo('div[dstype="mobile_pannel"]');
        
    }
    // 替换图片
    $('div[dstype="mobile_pannel"]').on('click', 'a[dstype="mp_rpl"]', function(){
        $('a[dstype="meat_cancel"]').click();
        $('div[dstype="mea_img"]').show().load(ADMINSITEURL+'/Goodsalbum/pic_list.html?item=mobile&type=replace');
    });
    // 插图图片
    replace_mobile_img = function(data){
        var _parents = $('div.m-image.current');
        _parents.find('img').attr('src', data);
        _data = new Object;
        _data.type = 'image';
        _data.value = data;
        mDataReplace(_parents.index(), _data);
    }
    // 插入数据
    mDataInsert = function(data){
        _m_data = mDataGet();
        _m_data.push(data);
        return mDataSet(_m_data);
    }
    // 数据移动 
    // type 0上移  1下移
    mDataMove = function(index, type) {
        _m_data = mDataGet();
        _data = _m_data.splice(index, 1);
        if (type) {
            index += 1;
        } else {
            index -= 1;
        }
        _m_data.splice(index, 0, _data[0]);
        return mDataSet(_m_data);
    }
    // 数据移除
    mDataRemove = function(index){
        _m_data = mDataGet();
        _m_data.splice(index, 1);     // 删除数据
        return mDataSet(_m_data);
    }
    // 替换数据
    mDataReplace = function(index, data){
        _m_data = mDataGet();
        _m_data.splice(index, 1, data);
        return mDataSet(_m_data);
    }
    // 获取数据
    mDataGet = function(){
        _m_body = $('input[name="m_body"]').val();
        if (_m_body == '' || _m_body == 'false') {
            var _m_data = new Array;
        } else {
            eval('var _m_data = ' + _m_body);
        }
        return _m_data;
    }
    // 设置数据
    mDataSet = function(data){
        var _i_c = 0;
        var _i_c_m = 20;
        var _t_c = 0;
        var _t_c_m = 5000;
        var _sign = true;
        $.each(data, function(i, n){
            if (n.type == 'image') {
                _i_c += 1;
                if (_i_c > _i_c_m) {
                    alert('只能选择'+_i_c_m+'张图片');
                    _sign = false;
                    return false;
                }
            } else if (n.type == 'text') {
                _t_c += n.value.length;
                if (_t_c > _t_c_m) {
                    alert('只能输入'+_t_c_m+'个字符');
                    _sign = false;
                    return false;
                }
            }
        });
        if (!_sign) {
            return false;
        }
        $('span[dstype="img_count_tip"]').html('还可以选择图片<em>' + (_i_c_m - _i_c) + '</em>张');
        $('span[dstype="txt_count_tip"]').html('还可以输入<em>' + (_t_c_m - _t_c) + '</em>字');
        _data = JSON.stringify(data);
        $('input[name="m_body"]').val(_data);
        return true;
    }
    // 转码
    toTxt = function(str) {
        var RexStr = /\<|\>|\"|\'|\&|\\/g
        str = str.replace(RexStr, function(MatchStr) {
            switch (MatchStr) {
            case "<":
                return "";
                break;
            case ">":
                return "";
                break;
            case "\"":
                return "";
                break;
            case "'":
                return "";
                break;
            case "&":
                return "";
                break;
            case "\\":
                return "";
                break;
            default:
                break;
            }
        })
        return str;
    }
});
// 计算商品库存
function computeStock(){
    // 库存
    var _stock = 0;
    $('input[data_type="stock"]').each(function(){
        if($(this).val() != ''){
            _stock += parseInt($(this).val());
        }
    });
    $('input[name="g_storage"]').val(_stock);
}

// 计算价格
function computePrice(){
    // 计算最低价格
    var _price = 0;var _price_sign = false;
    $('input[data_type="price"]').each(function(){
        if($(this).val() != '' && $(this)){
            if(!_price_sign){
                _price = parseFloat($(this).val());
                _price_sign = true;
            }else{
                _price = (parseFloat($(this).val())  > _price) ? _price : parseFloat($(this).val());
            }
        }
    });
    $('input[name="g_price"]').val(number_format(_price, 2));

    discountCalculator();       // 计算折扣
}

// 计算折扣
function discountCalculator() {
    var _price = parseFloat($('input[name="g_price"]').val());
    var _marketprice = parseFloat($('input[name="g_marketprice"]').val());
    if((!isNaN(_price) && _price != 0) && (!isNaN(_marketprice) && _marketprice != 0)){
        var _discount = parseInt(_price/_marketprice*100);
        $('input[name="g_discount"]').val(_discount);
    }
}

//获得商品名称
function getBrandName() {
    var brand_name = $('select[name="b_id"] > option:selected').html();
    $('input[name="b_name"]').val(brand_name);
}
//修改相关的颜色名称
function change_img_name(Obj){
     var S = Obj.parents('li').find('input[type="checkbox"]');
     S.val(Obj.val());
     var V = $('tr[dstype="file_tr_'+S.attr('ds_type')+'"]');
     V.find('span[dstype="pv_name"]').html(Obj.val());
     V.find('input[type="file"]').attr('name', Obj.val());
}
// 商品属性
function attr_selected(){
    $('select[ds_type="attr_select"] option:selected').each(function(){
        id = $(this).attr('ds_type');
        name = $(this).parents('select').attr('attr').replace(/__NC__/g,id);
        $(this).parents('select').attr('name',name);
    });
}
// 验证店铺分类是否重复
function checkSGC($val) {
    var _return = true;
    $('.sgcategory').each(function(){
        if ($val !=0 && $val == $(this).val()) {
            _return = false;
        }
    });
    return _return;
} 
/* 插入商品图片 */
function insert_img(name, src) {
    $('input[dstype="goods_image"]').val(name);
    $('img[dstype="goods_image"]').attr('src',src);
}

/* 插入编辑器 */
function insert_editor(file_path) {
    ue.execCommand('insertimage', {src:file_path});
}

function setArea(area1, area2) {
    $('#province_id').val(area1).change();
    $('#city_id').val(area2);
}

// 插入品牌
function insertBrand(param, search) {
    $('div[dstype="brandList"]').show();
    $('div[dstype="noBrandList"]').hide();
    var _ul = $('ul[dstype="brand_list"]');
    _ul.html('');
    if ($.isEmptyObject(param)) {
        $('div[dstype="brandList"]').hide();
        $('div[dstype="noBrandList"]').show().find('strong').html(search);
        return false;
    }
    $.each(param, function(i, n){
        $('<li data-id="' + n.brand_id + '" data-name="' + n.brand_name + '"><em>' + n.brand_initial + '</em>' + n.brand_name + '</li>').appendTo(_ul);
    });

    //搜索品牌列表滚条绑定
    $('div[dstype="brandList"]').perfectScrollbar('update');
}