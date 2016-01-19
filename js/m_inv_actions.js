var inventaire_actions_version = 10;

//Fonction générales
fail = function(){
	$('request_msg').style.display="block";
	$('request_msgtxt').innerHTML="La requête à échouée, veuillez ré-essayer plus tard.";
}

showplzwait = function(){
	$('plzwait1').style.display="block";
	$('plzwait2').style.display="block";
	$('request_msgtxt').innerHTML="Veuillez patienter...";
	$('request_msg').style.display="none";
}
hideplzwait = function(){
	$('plzwait1').style.display="none";
	$('plzwait2').style.display="none";
}



//CONSOMMER
conso = function(id){
	
	
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireConso', 
			{
				method: 'post', 
				parameters: 'id='+dg.id, 
				onComplete: conso_confirm,
				onFailure: fail
			});
}

//LIRE LIVRE
lireLivre = function(id){

	var dg = FindItem(id);
	$('redir_id').value=dg.id;
	$('redir_form').action="?popup=1&m=Action_Lieu_LireLivre";
	ajaxSubmitForm($('redir_form'));
	
}

conso_confirm = function(originalRequest){
	
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		var conso	= FindDz('consommer');
		$('perso_pr').innerHTML=params[3];
		$('perso_pa').innerHTML=params[2];
		$('perso_pn').innerHTML=params[4];
		
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
		$('request_msg').style.display="block";
		if(params.length==1){
			$('request_msgtxt').innerHTML=decodeURIComponent(params[0]);
		}else{
			$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
		}
	}
}




//EQUIPER
equiper = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireEquiper', 
			{
				method: 'post', 
				parameters: 'id='+dg.id, 
				onComplete: equiper_confirm,
				onFailure: fail
			});
}
equiper_confirm = function(originalRequest){
	
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		
		$('perso_pa').innerHTML=params[2];
		$('perso_pr').innerHTML=params[3];
		
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
		$('request_msg').style.display="block";
		if(params.length==1){
			$('request_msgtxt').innerHTML=decodeURIComponent(params[0]);
		}else{
			$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
		}
	}
}



//RANGER
ranger = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireRanger', 
			{
				method: 'post', 
				parameters: 'id='+dg.id, 
				onComplete: ranger_confirm,
				onFailure: fail
			});
}
ranger_confirm = function(originalRequest){
	
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	
	if (params.length > 1 && params[1]=="OK"){
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		$('perso_pa').innerHTML=params[2];
		$('perso_pr').innerHTML=params[3];
		
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
		$('request_msg').style.display="block";
		if(params.length==1){
			$('request_msgtxt').innerHTML=decodeURIComponent(params[0]);
		}else{
			$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
		}
	}
}

//CACHER
cacher = function(id){

	if(!confirm("Cacher l'item quelquepart dans le lieu ?"))
		return;
		
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireCacher',
			{
				method: 'post',
				parameters: 'id='+dg.id,
				onComplete: jeter_confirm,
				onFailure: fail
				});
}

//JETER
jeter = function(id){
	
	if(!confirm("Jeter cet item ?"))
		return;
	
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireJeter', 
			{
				method: 'post', 
				parameters: 'id='+dg.id, 
				onComplete: jeter_confirm,
				onFailure: fail
			});
}
submitJeterForm = function(url, itemid, qte){
	showplzwait();
	
	var myAjax = new Ajax.Request(
			url,
			{
				method: 'post', 
				parameters: 'id='+itemid+'&askQte='+qte, 
				onComplete: jeter_confirm,
				onFailure: fail
			});
}
jeter_confirm = function(originalRequest){
	
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	if (params.length > 1 && params[1]=="OK"){
		
		var dg		= FindItem(params[0]);
		var fiche	= FindItemFiche(params[0]);
		var jeter	= FindDz('jeter');
		$('perso_pa').innerHTML=params[2];
		$('perso_pr').innerHTML=params[3];
		
		
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
		$('request_msg').style.display="block";
		if(params.length==1){
			$('request_msgtxt').innerHTML=decodeURIComponent(params[0]);
		}else{
			$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
		}
	}
}



//CHARGER DE MUNITION
charger = function(id){
	showplzwait();
	
	var dg = FindItem(id);
	
	var myAjax = new Ajax.Request(
			'?popup=1&m=Action_Perso_InventaireCharger', 
			{
				method: 'post', 
				parameters: 'id='+dg.id, 
				onComplete: charger_confirm,
				onFailure: fail
			});
}
charger_confirm = function(originalRequest){
	
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	
	$('request_msg').style.display="block";
	$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
}
submitMunForm = function(url, itemid, munid){
	showplzwait();
	
	var myAjax = new Ajax.Request(
			url,
			{
				method: 'post', 
				parameters: 'id='+itemid+'&munid='+munid, 
				onComplete: charger_confirmFin,
				onFailure: fail
			});
}
charger_confirmFin = function(originalRequest){
	var rval= originalRequest.responseText;
	var params=rval.split("|");
	
	//0: Arme ID
	//1: Status
	//2: PA total
	//3: Inventaire PR
	//4: Arme Qte
	//5: Mun ID
	//6: Mun Qte
	if (params.length > 1 && params[1]=="OK"){
		$("qte_" + params[0]).innerHTML = params[4];
		$("qte_" + params[5]).innerHTML = params[6];
		$('perso_pa').innerHTML=params[2];
		$('perso_pr').innerHTML=params[3];
		hideplzwait();
	}else{
		$('request_msg').style.display="block";
		if(params.length==1){
			$('request_msgtxt').innerHTML=decodeURIComponent(params[0]);
		}else{
			$('request_msgtxt').innerHTML=decodeURIComponent(params[1]);
		}
	}
}
