var smilies_array = new Array();
smilies_array[1] = [['1', ':smile:', 'smile.gif', '28', '28', '28','微笑'], ['2', ':sad:', 'sad.gif', '28', '28', '28','难过'], ['3', ':biggrin:', 'biggrin.gif', '28', '28', '28','呲牙'], ['4', ':cry:', 'cry.gif', '28', '28', '28','大哭'], ['5', ':huffy:', 'huffy.gif', '28', '28', '28','发怒'], ['6', ':shocked:', 'shocked.gif', '28', '28', '28','惊讶'], ['7', ':tongue:', 'tongue.gif', '28', '28', '28','调皮'], ['8', ':shy:', 'shy.gif', '28', '28', '28','害羞'], ['9', ':titter:', 'titter.gif', '28', '28', '28','偷笑'], ['10', ':sweat:', 'sweat.gif', '28', '28', '28','流汗'], ['11', ':mad:', 'mad.gif', '28', '28', '28','抓狂'], ['12', ':lol:', 'lol.gif', '28', '28', '28','阴险'], ['13', ':loveliness:', 'loveliness.gif', '28', '28', '28','可爱'], ['14', ':funk:', 'funk.gif', '28', '28', '28','惊恐'], ['15', ':curse:', 'curse.gif', '28', '28', '28','咒骂'], ['16', ':dizzy:', 'dizzy.gif', '28', '28', '28','晕'], ['17', ':shutup:', 'shutup.gif', '28', '28', '28','闭嘴'], ['18', ':sleepy:', 'sleepy.gif', '28', '28', '28','睡'], ['19', ':hug:', 'hug.gif', '28', '28', '28','拥抱'], ['20', ':victory:', 'victory.gif', '28', '28', '28','胜利'], ['21', ':sun:', 'sun.gif', '28', '28', '28','太阳'],['22', ':moon:', 'moon.gif', '28', '28', '28','月亮'], ['23', ':kiss:', 'kiss.gif', '28', '28', '28','示爱'], ['24', ':handshake:', 'handshake.gif', '28', '28', '28','握手']];

var STATICURL = BASESITEROOT+'/static/plugins/js/smilies/';
function smilies_show(id, smcols, seditorkey,textobj) {
	if(seditorkey && !$("#"+seditorkey + 'sml_menu')[0]) {
		var div = document.createElement("div");
		div.id = seditorkey + 'sml_menu';
		div.className = 'sllt';
		$('#smilies_div').append(div);
		var div = document.createElement("div");
		div.id = id;
		div.style.overflow = 'hidden';
		$("#"+seditorkey + 'sml_menu').append(div);
	}
	smilies_onload(id, smcols, seditorkey);
	//image绑定操作函数
	$("#smiliesdiv").find("[dstype='smiliecode']").bind('click',function(){
		insertsmilie(this,textobj);
	});
}
function insertsmilie(smilieone,textobj){
	var code = $(smilieone).attr('codetext');
	$(textobj).insertAtCaret(code);
	$(textobj).focus();
	$('#smilies_div').html('');
	$('#smilies_div').hide();
}
function smilies_onload(id, smcols, seditorkey) {
	seditorkey = !seditorkey ? '' : seditorkey;
	$("#"+id).html('<div id="' + id + '_data"></div><div class="sllt-p" id="' + id + '_page"></div>');
	smilies_switch(id, smcols, 1, seditorkey);
}

function smilies_switch(id, smcols, page, seditorkey) {
	//page = page ? page : 1;
	page = 1;
	if(!smilies_array || !smilies_array[page]) return;
	smiliesdata = '<table id="' + id + '_table" cellpadding="0" cellspacing="0"><tr>';
	j = k = 0;
	img = [];
	for(i in smilies_array[page]) {
		if(j >= smcols) {
			smiliesdata += '<tr>';
			j = 0;
		}
		var s = smilies_array[page][i];
		smilieimg = STATICURL + 'images/' + s[2];
		img[k] = new Image();
		img[k].src = smilieimg;
		smiliesdata += s && s[0] ? '<td id="' + seditorkey + 'smilie_' + s[0] + '_td" dstype="smiliecode" codetext="'+s[1]+'"><img id="smilie_' + s[0] + '" width="' + s[3] +'" height="' + s[4] +'" src="' + smilieimg + '" alt="' + s[1] + '" title="'+s[6]+'" />' : '<td>';
		j++; k++;
	}
	smiliesdata += '</table>';
	/* smiliespage = '';
	if(smilies_array.length > 2) {
		prevpage = ((prevpage = parseInt(page) - 1) < 1) ? smilies_array.length - 1 : prevpage;
		nextpage = ((nextpage = parseInt(page) + 1) == smilies_array.length) ? 1 : nextpage;
		smiliespage = '<div class="z"><a href="javascript:void(0)" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + prevpage + ', \'' + seditorkey + '\');">上页</a>' +
			'<a href="javascript:void(0)" onclick="smilies_switch(\'' + id + '\', \'' + smcols + '\', ' + nextpage + ', \'' + seditorkey + '\');">下页</a></div>' +
			page + '/' + (smilies_array.length - 1);
	} 
	$('#'+ id + '_page').html(smiliespage);	*/
	$('#'+ id + '_data').html(smiliesdata);
}