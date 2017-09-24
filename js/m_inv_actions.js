var inventaire_actions_version = 11;

//Fonction générales
fail = function(){
	$('#request_msg').show();
	$('#request_msgtxt').html("La requête à échouée, veuillez ré-essayer plus tard.");
}

showplzwait = function(){
	$('#plzwait1').show();
	$('#plzwait2').show();
	$('#request_msgtxt').html("Veuillez patienter...");
	$('#request_msg').hide();
}
hideplzwait = function(){
	$('#plzwait1').hide();
	$('#plzwait2').hide();
}



//CONSOMMER
conso = function(id){
	
	
	showplzwait();
	
	var dg = FindItem(id);
	
        $.ajax({
            url:'?popup=1&m=Action_Perso_InventaireConso',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                conso_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}

//LIRE LIVRE
lireLivre = function(id){

	var dg = FindItem(id);
	$('#redir_id').val(dg.id);
	$('#redir_form').attr("action","?popup=1&m=Action_Lieu_LireLivre");
	ajaxSubmitForm($('#redir_form'));
	
}

conso_confirm = function(rval){
	
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		var conso	= FindDz('consommer');
		$('#perso_pr').html(params[3]);
		$('#perso_pa').html(params[2]);
		$('#perso_pn').html(params[4]);
		
		//Rétracter la fiche vers l'ancienne position de l'item
		var pos1 = new centerItemIntoItem(dg, dg.baseDz);
		fiche.moveResize(dg.id, pos1.x, pos1.y, 0, 0, false, 10, 20);
		
		//Effectuer le changement de Dz et de Menu
		fiche.isAffiche = false;
		
		
		//Déplacer l'item vers sa nouvelle Dz
		var pos2 = new centerItemIntoItem(dg, conso);
		dg.moveTo(dg.id, pos2.x, pos2.y, false, 10, 20);
		
		//Lorsque le déplacement est terminé, rafraichir la fenêtre pour afficher un inventaire sans l'item jeté
		//setTimeout("window.location.reload()", 20*10);

		dg.obj.stopObserving('click');
		
		
		hideplzwait();
	}else{
		$('#request_msg').css({ display: "block" });
		if(params.length==1){
			$('#request_msgtxt').html(decodeURIComponent(params[0]));
		}else{
			$('#request_msgtxt').html(decodeURIComponent(params[1]));
		}
	}
}




//EQUIPER
equiper = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	
        $.ajax({
            url:'?popup=1&m=Action_Perso_InventaireEquiper',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                equiper_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}
equiper_confirm = function(rval){
	
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		
		$('#perso_pa').html(params[2]);
		$('#perso_pr').html(params[3]);
		
		//Rétracter la fiche vers l'ancienne position de l'item
		var pos1 = new centerItemIntoItem(dg, dg.baseDz);
		fiche.moveResize(dg.id, pos1.x, pos1.y, 0, 0, false, 10, 20);
		
		//Effectuer le changement de Dz et de Menu
		dg.baseDz = FindDz(dg.equipType);
		fiche.modAction('equiper','ranger','Ranger');
		fiche.isAffiche = false;
		
		//Déplacer l'item vers sa nouvelle Dz
		var pos2 = new centerItemIntoItem(dg, dg.baseDz);
		dg.moveTo(dg.id, pos2.x, pos2.y, false, 10, 20);
		
		hideplzwait();
	}else{
		$('#request_msg').css({ display: "block" });
		if(params.length==1){
			$('#request_msgtxt').html(decodeURIComponent(params[0]));
		}else{
			$('#request_msgtxt').html(decodeURIComponent(params[1]));
		}
	}
}



//RANGER
ranger = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	
        $.ajax({
            url:'?popup=1&m=Action_Perso_InventaireRanger',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                ranger_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}
ranger_confirm = function(rval){
	
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		$('#perso_pa').html(params[2]);
		$('#perso_pr').html(params[3]);
		
		//Rétracter la fiche vers l'ancienne position de l'item
		var pos1 = new centerItemIntoItem(dg, dg.baseDz);
		fiche.moveResize(dg.id, pos1.x, pos1.y, 0, 0, false, 10, 20);
		
		//Effectuer le changement de Dz et de Menu
		dg.baseDz = FindDz(dg.id);
		fiche.modAction('ranger','equiper','Équiper');
		fiche.isAffiche = false;
		
		//Déplacer l'item vers sa nouvelle Dz
		var pos2 = new centerItemIntoItem(dg, dg.baseDz);
		dg.moveTo(dg.id, pos2.x, pos2.y, false, 10, 20);
		
		hideplzwait();
	}else{
		$('#request_msg').css({ display: "block" });
		if(params.length==1){
			$('#request_msgtxt').html(decodeURIComponent(params[0]));
		}else{
			$('#request_msgtxt').html(decodeURIComponent(params[1]));
		}
	}
}

//CACHER
cacher = function(id){

	if(!confirm("Cacher l'item quelquepart dans le lieu ?"))
		return;
		
	showplzwait();
	
	var dg = FindItem(id);
	
        $.ajax({
            url:'?popup=1&m=Action_Perso_InventaireCacher',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                jeter_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}

//JETER
jeter = function(id){
	
	if(!confirm("Jeter cet item ?"))
		return;
	
	showplzwait();
	
	var dg = FindItem(id);
	$.ajax({
            url:'?popup=1&m=Action_Perso_InventaireJeter',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                jeter_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}
submitJeterForm = function(url, itemid, qte){
	showplzwait();
	
        $.ajax({
            url:url,
            method: 'post',
            data: 'id='+itemid+'&askQte='+qte, 
            dataType: "html",
            success: function (data) {
                jeter_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}
jeter_confirm = function(rval){
	
	var params=rval.split("|");
	if (params.length > 1 && params[1]=="OK"){
		
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		var jeter	= FindDz('jeter');
		$('#perso_pa').html(params[2]);
		$('#perso_pr').html(params[3]);
		
		
		//Rétracter la fiche vers l'ancienne position de l'item
		var pos1 = new centerItemIntoItem(dg, dg.baseDz);
		fiche.moveResize(dg.id, pos1.x, pos1.y, 0, 0, false, 10, 20);
		
		//Effectuer le changement de Dz et de Menu
		fiche.isAffiche = false;
		
		
		//Déplacer l'item vers sa nouvelle Dz
		var pos2 = new centerItemIntoItem(dg, jeter);
		dg.moveTo(dg.id, pos2.x, pos2.y, false, 10, 20);
		
		//Lorsque le déplacement est terminé, rafraichir la fenêtre pour afficher un inventaire sans l'item jeté
		//setTimeout("window.location.reload()", 20*10);
		
		hideplzwait();
	}else{
		$('#request_msg').css({ display: "block" });
		if(params.length==1){
			$('#request_msgtxt').html(decodeURIComponent(params[0]));
		}else{
			$('#request_msgtxt').html(decodeURIComponent(params[1]));
		}
	}
}



//CHARGER DE MUNITION
charger = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	$.ajax({
            url:'?popup=1&m=Action_Perso_InventaireCharger',
            method: 'post',
            data: 'id='+dg.id, 
            dataType: "html",
            success: function (data) {
                charger_confirm(data);
            },
            error: function () {
                fail();
            }
        });
}
charger_confirm = function(rval){
	
	var params=rval.split("|");
	
	$('#request_msg').css({ display: "block" });
	$('#request_msgtxt').html(decodeURIComponent(params[1]));
}
submitMunForm = function(url, itemid, munid){
	showplzwait();
	$.ajax({
            url:url,
            method: 'post',
            data: 'id='+itemid+'&munid='+munid, 
            dataType: "html",
            success: function (data) {
                charger_confirmFin(data);
            },
            error: function () {
                fail();
            }
        });
}
charger_confirmFin = function(rval){
	var params=rval.split("|");
	
	//0: Arme ID
	//1: Status
	//2: PA total
	//3: Inventaire PR
	//4: Arme Qte
	//5: Mun ID
	//6: Mun Qte
	if (params.length > 1 && params[1]=="OK"){
		$("qte_" + params[0]).html(params[4]);
		$("qte_" + params[5]).html(params[6]);
		$('#perso_pa').html(params[2]);
		$('#perso_pr').html(params[3]);
		hideplzwait();
	}else{
		$('#request_msg').css({ display: "block" });
		if(params.length==1){
			$('#request_msgtxt').html(decodeURIComponent(params[0]));
		}else{
			$('#request_msgtxt').html(decodeURIComponent(params[1]));
		}
	}
}
