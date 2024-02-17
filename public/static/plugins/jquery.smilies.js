/*
 * 表情处理
 */
var smilies_array = new Array();
smilies_array[1] = [['1', ':smile:', 'smile.gif', '28', '28', '28', '微笑'], ['2', ':sad:', 'sad.gif', '28', '28', '28', '难过'], ['3', ':biggrin:', 'biggrin.gif', '28', '28', '28', '呲牙'], ['4', ':cry:', 'cry.gif', '28', '28', '28', '大哭'], ['5', ':huffy:', 'huffy.gif', '28', '28', '28', '发怒'], ['6', ':shocked:', 'shocked.gif', '28', '28', '28', '惊讶'], ['7', ':tongue:', 'tongue.gif', '28', '28', '28', '调皮'], ['8', ':shy:', 'shy.gif', '28', '28', '28', '害羞'], ['9', ':titter:', 'titter.gif', '28', '28', '28', '偷笑'], ['10', ':sweat:', 'sweat.gif', '28', '28', '28', '流汗'], ['11', ':mad:', 'mad.gif', '28', '28', '28', '抓狂'], ['12', ':lol:', 'lol.gif', '28', '28', '28', '阴险'], ['13', ':loveliness:', 'loveliness.gif', '28', '28', '28', '可爱'], ['14', ':funk:', 'funk.gif', '28', '28', '28', '惊恐'], ['15', ':curse:', 'curse.gif', '28', '28', '28', '咒骂'], ['16', ':dizzy:', 'dizzy.gif', '28', '28', '28', '晕'], ['17', ':shutup:', 'shutup.gif', '28', '28', '28', '闭嘴'], ['18', ':sleepy:', 'sleepy.gif', '28', '28', '28', '睡'], ['19', ':hug:', 'hug.gif', '28', '28', '28', '拥抱'], ['20', ':victory:', 'victory.gif', '28', '28', '28', '胜利'], ['21', ':sun:', 'sun.gif', '28', '28', '28', '太阳'], ['22', ':moon:', 'moon.gif', '28', '28', '28', '月亮'], ['23', ':kiss:', 'kiss.gif', '28', '28', '28', '示爱'], ['24', ':handshake:', 'handshake.gif', '28', '28', '28', '握手']];
(function ($) {
    $.fn.smilies = function (options) {
        var settings = $.extend({}, {smilies_id: '_message'}, options);
        settings.smilies_div = "#" + settings.smilies_id + "_smilies_div";
        $(document).click(function () {
            $(settings.smilies_div).html('');
            $(settings.smilies_div).hide();
        });
        $(this).after('<div id="' + settings.smilies_id + '_smilies_div" class="smilies-module"></div>');
        $(settings.smilies_div).position({
            of: $("body"),
            at: "left bottom",
            offset: "10 10"
        });

        $(this).on('click', function () {
            //光标处插入代码功能
            smiliesshowdiv(this);
            return false;
        });
        //显示和隐藏表情模块
        function smiliesshowdiv(btnobj) {
            if ($(settings.smilies_div).css("display") == 'none') {
                if ($(settings.smilies_div).html() == '') {
                    smilies_show(8, 'e_' + settings.smilies_id + '_');
                }
                $(settings.smilies_div).show();
                smiliesposition(btnobj);
            } else {
                $(settings.smilies_div).hide();
            }
        }
        //弹出层位置控制
        function smiliesposition(btnobj) {
            $(settings.smilies_div).position({
                of: btnobj,
                at: "left bottom",
                offset: "110 57"
            });
        }
        function smilies_show(smcols, seditorkey) {
            if (seditorkey && !$("#" + seditorkey + 'sml_menu')[0]) {
                var div = document.createElement("div");
                div.id = seditorkey + 'sml_menu';
                div.className = 'sllt';
                $(settings.smilies_div).append(div);
                div = document.createElement("div");
                div.id = '' + settings.smilies_id + '_smilies_content';
                div.style.overflow = 'hidden';
                $("#" + seditorkey + 'sml_menu').append(div);
            }
            smilies_onload(smcols, seditorkey);
            //image绑定操作函数
            $('#' + settings.smilies_id + '_smilies_content').find("td").bind('click', function () {
                insertsmilie(this);
            });
        }

        function insertsmilie(smilieone) {
            var code = $(smilieone).attr('codetext');
            insertAtCaret(code);
            $('#' + settings.smilies_id).focus();
            $(settings.smilies_div).html('');
            $(settings.smilies_div).hide();
        }

        function insertAtCaret(textFeildValue) {
            var textObj = $('#' + settings.smilies_id).get(0);
            if (document.all && textObj.createTextRange && textObj.caretPos) {
                var caretPos = textObj.caretPos;
                caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == '' ?
                        textFeildValue + '' : textFeildValue;
            }
            else if (textObj.setSelectionRange) {
                var rangeStart = textObj.selectionStart;
                var rangeEnd = textObj.selectionEnd;
                var tempStr1 = textObj.value.substring(0, rangeStart);
                var tempStr2 = textObj.value.substring(rangeEnd);
                textObj.value = tempStr1 + textFeildValue + tempStr2;
                textObj.focus();
                var len = textFeildValue.length;
                textObj.setSelectionRange(rangeStart + len, rangeStart + len);
                textObj.blur();
            }
            else {
                textObj.value += textFeildValue;
            }
        }

        function smilies_onload(smcols, seditorkey) {
            seditorkey = !seditorkey ? '' : seditorkey;
            $('#' + settings.smilies_id + '_smilies_content').html('<div id="' + settings.smilies_id + '_smilies_content_data"></div><div class="sllt-p" id="' + settings.smilies_id + '_smilies_content_page"></div>');
            smilies_switch(smcols, seditorkey);
        }

        function smilies_switch(smcols, seditorkey) {
            var page = 1;
            if (!smilies_array || !smilies_array[page])
                return;
            smiliesdata = '<table id="' + settings.smilies_id + '_smilies_content_table" cellpadding="0" cellspacing="0"><tr>';
            j = k = 0;
            img = [];
            for (i in smilies_array[page]) {
                if (j >= smcols) {
                    smiliesdata += '<tr>';
                    j = 0;
                }
                var s = smilies_array[page][i];
                smilieimg = BASESITEROOT + '/static/plugins/js/smilies/images/' + s[2];
                img[k] = new Image();
                img[k].src = smilieimg;
                smiliesdata += s && s[0] ? '<td id="' + seditorkey + 'smilie_' + s[0] + '_td" codetext="' + s[1] + '"><img id="' + seditorkey + 'smilie_' + s[0] + '" width="' + s[3] + '" height="' + s[4] + '" src="' + smilieimg + '" alt="' + s[1] + '" title="' + s[6] + '" />' : '<td>';
                j++;
                k++;
            }
            smiliesdata += '</table>';
            $('#' + settings.smilies_id + '_smilies_content_data').html(smiliesdata);
        }

    }
})(jQuery);
