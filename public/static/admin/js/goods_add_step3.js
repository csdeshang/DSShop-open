$(function(){
    // 商品图片ajax上传
    $('.dssc-upload-btn').find('input[type="file"]').on('change', function(){
        var id = $(this).attr('id');
        ajaxFileUpload(id);
    });
    //浮动导航  waypoints.js
//    $("#uploadHelp").waypoint(function(event, direction) {
//        $(this).parent().toggleClass('sticky', direction === "down");
//        event.stopPropagation();
//    }); 
    // 关闭相册
    $('a[dstype="close_album"]').click(function(){
        $(this).hide();
        $(this).prev().show();
        $(this).parent().next().html('');
    });
    // 绑定点击事件
    $('div[dstype^="file"]').each(function(){
        if ($(this).prev().find('input[type="hidden"]').val() != '') {
            selectDefaultImage($(this));
        }
    });
});

// 图片上传ajax
function ajaxFileUpload(id, o) {
    $('img[dstype="' + id + '"]').attr('src', ADMINSITEROOT + "/images/loading.gif");

    $.ajaxFileUpload({
        url : ADMINSITEURL + '/Goodsadd/image_upload',
        secureuri : false,
        fileElementId : id,
        dataType : 'json',
        data : {name : id},
        success : function (data, status) {
                    if (typeof(data.error) != 'undefined') {
                        alert(data.error);
                        $('img[dstype="' + id + '"]').attr('src',DEFAULT_GOODS_IMAGE);
                    } else {
                        $('input[dstype="' + id + '"]').val(data.name);
                        $('img[dstype="' + id + '"]').attr('src', data.thumb_name);
                        selectDefaultImage($('div[dstype="' + id + '"]'));      // 选择默认主图
                    }

                },
        error : function (data, status, e) {
                    alert(e);

                }
    });
    return false;

}

// 选择默认主图&&删除
function selectDefaultImage($this) {
    // 默认主题
    $this.click(function(){
        $(this).parents('ul:first').find('.show-default').removeClass('selected').find('input').val('0');
        $(this).addClass('selected').find('input').val('1');
    });
    // 删除
    $this.parents('li:first').find('a[dstype="del"]').click(function(){
        $this.unbind('click').removeClass('selected').find('input').val('0');
        $this.prev().find('input').val('').end().find('img').attr('src', DEFAULT_GOODS_IMAGE);
    });
}

// 从图片空间插入主图
function insert_img(name, src, color_id) {
    var $_thumb = $('ul[dstype="ul'+ color_id +'"]').find('.upload-thumb');
    $_thumb.each(function(){
        if ($(this).find('input').val() == '') {
            $(this).find('img').attr('src', src);
            $(this).find('input').val(name);
            selectDefaultImage($(this).next());      // 选择默认主图
            return false;
        }
    });
}