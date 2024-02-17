$(function () {
    //表情模块
    $("#smilies_div").position({
        of: $("body"),
        at: "left bottom",
        offset: "10 10"
    });
    $(document).on('click', "[ds_type='smiliesbtn']", function () {
        //光标处插入代码功能
        $("[ds_type='contenttxt']").setCaret();
        var data = $(this).attr('data-param');
        eval("data = " + data);
        smiliesshowdiv(data.txtid, this);
    });
});
//显示和隐藏表情模块
function smiliesshowdiv(txtid, btnobj) {
    if ($('#smilies_div').css("display") == 'none') {
        if ($('#smilies_div').html() == '') {
            smilies_show('smiliesdiv', 8, 'e_', $("#content_" + txtid));
        }
        $('#smilies_div').show();
        smiliesposition(btnobj);
    } else {
        $('#smilies_div').hide();
    }
}
//弹出层位置控制
function smiliesposition(btnobj) {
    $("#smilies_div").position({
        of: btnobj,
        at: "left bottom",
        offset: "105 57"
    });
}