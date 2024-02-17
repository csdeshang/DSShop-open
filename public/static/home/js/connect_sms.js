$(function() {
    $(document).off('click',"#btn_sms_captcha").on('click',"#btn_sms_captcha", function() {
        var type = $(this).attr('ds_type');//ds_type 1为注册 2为登录  3为找回密码
        var sms_mobile = $(this).parents("form").find("#sms_mobile").val();
        var target = this;
        if (sms_mobile.length == 11) {
            var ajaxurl = HOMESITEURL + '/Connectsms/get_captcha.html?type=' + type;
            ajaxurl += '&sms_mobile=' + sms_mobile;
            $.ajax({
                type: "GET",
                url: ajaxurl,
                async: false,
                success: function (rs) {
                    if (rs == 'true') {
                        layer.msg('短信动态码已发出');
                        countdown(target,"获取手机验证码");
                    } else {
                        layer.msg(rs);
                    }
                }
            });
        }else{
            layer.msg('请先输入正确手机号');
        }
    })
})
var wait = 60;
function countdown(obj,msg) {
    obj = $(obj);
    if (wait <= 0) {
        obj.prop("disabled", false);
        obj.html(msg);
        wait = 60;
    } else {
        if (msg == undefined || msg == null) {
            msg = obj.html();
        }
        obj.prop("disabled", true);
        obj.html(wait + "秒后重新获取");
        wait--;
        setTimeout(function () {
            countdown(obj,msg)
        }, 1000)
    }
}
function check_captcha() {
    if ($("#sms_mobile").val().length == 11 && $("#sms_captcha").val().length == 6) {
        var ajaxurl = HOMESITEURL+'/Connectsms/check_captcha.html';
        ajaxurl += '&sms_captcha=' + $('#sms_captcha').val() + '&sms_mobile=' + $('#sms_mobile').val();
        $.ajax({
            type: "GET",
            url: ajaxurl,
            async: false,
            success: function(rs) {
                if (rs == 'true') {
                    $.getScript(HOMESITEURL+'/Connectsms/register.html' + '?sms_mobile=' + $('#sms_mobile').val());
                    $("#register_sms_form").show();
                    $("#post_form").hide();
                } else {
                    layer.msg(rs);
                }
            }
        });
    }
}