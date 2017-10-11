
validate = function(url, mustSel, mustConfirm)
{
    var selectedRadio = $('input[name=db_id]:checked', '#form1').val();
    if (mustSel && selectedRadio==null)
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
        $.ajax({
            url:'?mj=Search&popup=1',
            method: 'post',
            data: 'searchWhat=item_locate_by_dbId&db_id='+id, 
            dataType: "html",
            success: function (data) {
                locate_completed(data);
            }
        });
}

locate_completed = function(rval)
{
	$('#locateTd_'+tmp_dbid).html(rval);
	$('#locateTr_'+tmp_dbid).show();
}
