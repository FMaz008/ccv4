/**
 * SRC: http://www.rexchung.com/2007/02/22/getting-radio-buttons-value-with-prototypejs/
 */
$RF = function(fId, rName)
{
    var tmp = Form.getInputs(fId,'radio',rName).find(function(radio) { return radio.checked; })
	if(tmp==undefined)	
		return null;
	return tmp.value;
}

validate = function(url, mustSel, mustConfirm)
{
	if (mustSel && $RF('form1','db_id')==null)
	{
		alert('Vous devez sélectionner un item.');
		return false;
	}
	
	document.forms['form1'].action = '?mj=' + url;
	
	if(mustConfirm)
		return confirm('Supprimer cet items ainsi que toutes les instances en circulation ?');
	
	return true;
}

var tmp_dbid;
locate = function(id)
{
	tmp_dbid = id;
	var myAjax = new Ajax.Request(
		'?mj=Search&popup=1', 
		{
			method: 'post', 
			parameters: 'searchWhat=item_locate_by_dbId&db_id='+id,
			onComplete: locate_completed
		});
}

locate_completed = function(originalRequest)
{
	var rval= originalRequest.responseText;
	$('locateTd_'+tmp_dbid).innerHTML = rval;
	$('locateTr_'+tmp_dbid).style.display="table-row";
}
