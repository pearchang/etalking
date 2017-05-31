var color0, color1;
var page;

//var requiredFields = new Array("");
//var fieldNames = new Array("");

function checkRequiredFields(input)
{
	var fieldCheck = true; 
	var fieldsNeeded = "\n 請輸入以下欄位資料 :\n\n　　";

	if (!input)
		input = document.frm;
	for(var fieldNum = 0; fieldNum < requiredFields.length; fieldNum++)
	{ 
		if ((input.elements[requiredFields[fieldNum]].value == "") || (input.elements[requiredFields[fieldNum]].value == " "))
		{ 
			fieldsNeeded += fieldNames[fieldNum] + "\n　　"; 
			fieldCheck = false; 
		}
	}
	if (fieldCheck == true) 
		return true;
	else
	{
		alert(fieldsNeeded); 
		return false;
	}
}

function checkRequiredFields2(input, requiredFields, message)
{
	var fieldCheck = true; 

	if (!input)
		input = document.frm;
	for(var fieldNum = 0; fieldNum < requiredFields.length; fieldNum++)
	{
		msg = $('#msg_' + requiredFields[fieldNum]);
		if ((input.elements[requiredFields[fieldNum]].value == "") || (input.elements[requiredFields[fieldNum]].value == " "))
		{ 
			fieldCheck = false;
			if (msg)
				msg.text(message);
			input.elements[requiredFields[fieldNum]].focus();
		}
		else
		{
			if (msg)
				msg.text('');
		}
	}
	if (fieldCheck == true) 
		return true;
	else
		return false;
}


function checkBlank(obj, msg)
{
	if (!obj)
		return;
	if (obj.value.length == 0)
	{
		alert(msg);
		return true;
	}
	return false;
}

function checkPositive(obj, msg, msg2)
{
	if (!obj)
		return;
	if (msg2 != '' && checkBlank(obj, msg2))
		return false;
	var x = parseInt(obj.value);
	if (x < 0)
	{
		alert(msg);
		return true;
	}
	return false;
}

function checkZeroLength(obj, msg)
{
	if (!obj)
		return;
	if (obj.length == 0)
	{
		alert(msg);
		return true;
	}
	return false;
}

function checkMustOne(obj, msg)
{
	if (!obj)
		return;
	for (i = 0; i < obj.length; i++)
		if (obj[i].checked)
			break;
	if (i == obj.length)
	{
		alert(msg);
		return true;
	}
	return false;
}

function checkNotFirst(obj, msg)
{
	if (!obj)
		return;
	if (obj.selectedIndex == 0)
	{
		alert(msg);
		return true;
	}
	return false;
}

function choose(obj, value)
{
	if (!obj)
		return;
	for (i = 0; i < obj.length; i++)
	{
		if (obj.options[i].value == value)
		{
			obj.selectedIndex = i;
			break;
		}
	}
}

function chooseOne(obj, value)
{
	if (!obj)
		return;
	if (!obj.length)
		obj.checked = true;
	else
	{
		for (i = 0; i < obj.length; i++)
		{
			if (obj[i].value == value)
			{
				obj[i].checked = true;
				break;
			}
		}
	}
}

function chooseBox(obj, value)
{
	if (!obj)
		return;
	if (obj.value == value)
		obj.checked = true;
	else
		obj.checked = false;
}

function getSelect(obj)
{
	if (!obj)
		return null;
	return obj.options[obj.selectedIndex].value;
}

function getRadio(obj)
{
	if (!obj)
		return;
	for (i = 0; i < obj.length; i++)
		if (obj[i].checked)
			return obj[i].value;
}

function go(url)
{
	window.location.href = url;
}

function goback()
{
	var ss = window.location.pathname.split('/');
	window.location.href = ss[1] == 'admin' ? '/admin/back' : '/back';
	//window.history.go(-1);
}

function col0(obj, over)
{
	if (over)
	{
		color0 = obj.style.backgroundColor;
		obj.style.backgroundColor = '#66CCFF';
	}
	else
		obj.style.backgroundColor = color0;
}

function col1(obj, over)
{
	if (over)
	{
		color1 = obj.style.backgroundColor;
		obj.style.backgroundColor = '#66CCFF';
	}
	else
		obj.style.backgroundColor = color1;
}

// Calendar
function selectHandler(cal, date)
{
  cal.sel.value = date;
  if (cal.dateClicked)
    cal.callCloseHandler();
}

function closeHandler(cal)
{
  cal.hide();
  _dynarch_popupCalendar = null;
}

function showCalendar(obj)
{
  if (_dynarch_popupCalendar != null)
    _dynarch_popupCalendar.hide();
  else
	{
    var cal = new Calendar(0, null, selectHandler, closeHandler);
    cal.showsTime = false;
    cal.showsOtherMonths = true;
    _dynarch_popupCalendar = cal;
//	var d = new Date();
//	var y = d.getFullYear();
//    cal.setRange(y, y + 4);
	cal.setRange(1900, 2070);
    cal.create();
  }
  _dynarch_popupCalendar.setDateFormat('%Y-%m-%d');
  _dynarch_popupCalendar.parseDate(obj.value);
  _dynarch_popupCalendar.sel = obj;
  _dynarch_popupCalendar.showAtElement(obj.nextSibling, "Br");
  return false;
}

function getAbsolutePos(el)
{
	var SL = 0, ST = 0;
	var is_div = /^div$/i.test(el.tagName);
	if (is_div && el.scrollLeft)
		SL = el.scrollLeft;
	if (is_div && el.scrollTop)
		ST = el.scrollTop;
	var r = { x: el.offsetLeft - SL, y: el.offsetTop - ST };
	if (el.offsetParent) {
		var tmp = this.getAbsolutePos(el.offsetParent);
		r.x += tmp.x;
		r.y += tmp.y;
	}
	return r;
}

function checkVote(frm)
{
	var n = false;

	for (i = 0; i < frm.elements.length; i++)
	{
		z = frm.elements[i];
		if (z.name == 'song')
			n |= z.checked;
	}
	if (!n)
	{
		alert('請選擇你最喜愛的歌曲!');
		return false;
	}
	return true;
}

function getAge(dateString) {
	var today = new Date();
	var birthDate = new Date(dateString);
	var age = today.getFullYear() - birthDate.getFullYear();
	var m = today.getMonth() - birthDate.getMonth();
	if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
		age--;
	}
	return age;
}

function checkTwID(id) {
	//建立字母分數陣列(A~Z)
	var city = new Array(
		1,10,19,28,37,46,55,64,39,73,82, 2,11,
		20,48,29,38,47,56,65,74,83,21, 3,12,30
	)
	id = id.toUpperCase();
	// 使用「正規表達式」檢驗格式
	if (id.search(/^[A-Z](1|2)\d{8}$/i) == -1) {
		alert('基本格式錯誤');
		return false;
	} else {
		//將字串分割為陣列(IE必需這麼做才不會出錯)
		id = id.split('');
		//計算總分
		var total = city[id[0].charCodeAt(0)-65];
		for(var i=1; i<=8; i++){
			total += eval(id[i]) * (9 - i);
		}
		//補上檢查碼(最後一碼)
		total += eval(id[9]);
		//檢查比對碼(餘數應為0);
		return ((total%10 == 0 ));
	}
}


if (CKEDITOR)
{
	CKEDITOR.config.toolbar = 'Custom';
	CKEDITOR.config.toolbar_Custom =
	[
		{ name: 'document', items : [ 'Source' ] },
		{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
		{ name: 'editing', items : [ 'Find','Replace','-','SelectAll' ] },
		{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
		'/',
		{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
		{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
		{ name: 'insert', items : [ 'Image','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak' ] },
		'/',
		{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
		{ name: 'colors', items : [ 'TextColor','BGColor' ] },
		{ name: 'tools', items : [ 'Maximize', 'ShowBlocks','-','About' ] }
	];
	CKEDITOR.config.height = '360';
}