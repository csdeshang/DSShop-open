//删除兑换积分礼品购物车
function drop_pcart_item(pcart_id){
    var tr = $('#pcart_item_' + pcart_id);
    var amount_span = $('#pcart_amount');
    var cart_goods_kinds = $('#cart_goods_kinds');
    $.getJSON(HOMESITEURL+'/Pointcart/drop.html?pc_id=' + pcart_id, function(result){
    	window.location.reload();    //刷新
    });
}
function pcart_change_quantity(pcart_id, input){
    var amount_span = $('#pcart_amount');
    var subtotal_span = $('#item_' + pcart_id + '_subtotal');
    var quantity_input = $('#input_item_' + pcart_id);
    //暂存为局部变量，否则如果用户输入过快有可能造成前后值不一致的问题
    var _v = input.value;
    $.getJSON(HOMESITEURL+'/Pointcart/update.html?pc_id=' + pcart_id + '&quantity=' + _v, function(result){
        if(result.done){
            //更新成功
            $(input).attr('changed', _v);
            amount_span.html(result.amount);
            subtotal_span.html(result.subtotal);
            quantity_input.val(result.quantity);
        }
        else{
            //更新失败
            alert(result.msg);
            window.location.reload();    //刷新
        }
    });
}
function pcart_decrease_quantity(pcart_id){
    var item = $('#input_item_' + pcart_id);
    var orig = Number(item.val());
    if(orig > 1){
        item.val(orig - 1);
        item.keyup();
    }
}
function pcart_add_quantity(pcart_id){
    var item = $('#input_item_' + pcart_id);
    var orig = Number(item.val());
    item.val(orig + 1);
    item.keyup();
}