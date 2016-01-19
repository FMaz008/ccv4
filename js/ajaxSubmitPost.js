/**
Cette fonction sert a charger une action a partir du menu
Cette fonction sert à faire un lien standard qui s'ouvre uniquement dans le panneau d'action
*/
actionLink = function(url)
{
	//BUT: Charger une page d'action dans le panneau d'action
	var objContent = $('actionPanelContent');
	
	objContent.innerHTML='Chargement en cours...';
	var myAjax = new Ajax.Request(
			'?popup=1&m=' + url,
			{
				method: 'get',
				parameters: '',
				onComplete: ajaxSubmitForm_confirm,
				onFailure: ajaxSubmitForm_fail
			});
	
	//Masquer le menu, peu-importe son statut
	objContent.style.display = "none"; //La visibilité va etre inversée par showhideaction()
	toggleActionPanel();
}

/**
Cette fonction sert a convertir une requete POST standard en processus AJAX

Utilisation:
Ajouter simplement le onsubmit suivant:
<form id="parler" method="post" action="?popup=1&m=Action_Parler2" onsubmit="return ajaxSubmitForm(this);">
*/
ajaxSubmitForm = function (objForm)
{
	
	var objContent = $('actionPanelContent');
	var param = Form.serialize(objForm.readAttribute('id'));
	
	if(objContent!=null)
		objContent.innerHTML='Chargement en cours...';
	
	var myAjax = new Ajax.Request(
			objForm.readAttribute("action"),
			{
				method: objForm.readAttribute("method"), 
				parameters: param,
				onComplete: ajaxSubmitForm_confirm,
				onFailure: ajaxSubmitForm_fail
			});
	
	return false; //Ne pas envoyer le formulaire

}


ajaxSubmitForm_confirm = function(originalRequest)
{
	
	var rval= originalRequest.responseText;
	
	var objContent = $('actionPanelContent');
	if(objContent!=null)
	{
		objContent.innerHTML = rval;
		actionLoadScripts(objContent);
	}
	else
	{
		document.body.innerHTML = rval;
		actionLoadScripts(document.body);
	}
}

actionLoadScripts = function(obj)
{
	//Src.: http://www.developpez.net/forums/showpost.php?p=1185289&postcount=18
	var AllScripts=obj.getElementsByTagName("script")
	//alert(AllScripts.length + " zone de script a charger");
	for (var i=0; i<AllScripts.length; i++)
	{
		var s=AllScripts[i];
		if (s.src && s.src!="")
		{
			eval(getFileContent(s.src)) //Ramasser les sources externes
		}
		else
		{
			eval(s.innerHTML);
		}
	}
}

getFileContent = function(url) {
	var Xhr=GetXmlHttpRequest();
	Xhr.open("GET",url,false);
	Xhr.send(null);
	return Xhr.responseText;
}


ajaxSubmitForm_fail = function()
{
	var objContent = $('actionPanelContent');
	if(objContent!=null)
		objContent.innerHTML="La requ&ecirc;te &agrave; &eacute;chou&acute;e, veuillez r&eacute;-essayer plus tard.";
	else
		alert("La requ&ecirc;te &agrave; &eacute;chou&acute;e, veuillez r&eacute;-essayer plus tard.");
}








//Fonction pour l'affichage du temps session restant
CD_ZP = function(objVal)
{
	var str=""+objVal;
	var strl=str.length;
	return(strl!=2?"0"+str:str)
}
		 
countdown = function (Time_Left){
	if(Time_Left == 0)
	{
		document.getElementById("countdown").innerHTML = "expirée";
		if(SHOW_AJAX_LOGIN == 1)
			showAjaxLogin();
	}
	else
	{
		var minutes = Math.floor(Time_Left / 60);
		var seconds = Time_Left - (minutes * 60);
	
		document.getElementById("countdown").innerHTML = CD_ZP(minutes) + ':' + CD_ZP(seconds);
		setTimeout('countdown(' + (Time_Left-1) + ');', 1000);
	}
}





ajaxLogin = function(objForm, sessionTime)
{
	var param = Form.serialize(objForm.readAttribute('id'));
	var myAjax = new Ajax.Request(
			objForm.readAttribute("action"),
			{
				method: objForm.readAttribute("method"), 
				parameters: param,
				onComplete: function(originalRequest)
							{
								var rval= originalRequest.responseText;
								
								if(rval == 0)
								{
									hideAjaxLogin();
									countdown(sessionTime);
								}
								else
								{
									$('ajaxLogin_error').style.display = "block";
								}
							}
			});
	
	return false; //Ne pas envoyer le formulaire
}

hideAjaxLogin = function()
{
	$('ajaxLogin_plzwait1').style.display = 'none';
	$('ajaxLogin_plzwait2').style.display = 'none';
}

showAjaxLogin = function()
{
	$('ajaxLogin_plzwait1').style.display = 'block';
	$('ajaxLogin_plzwait2').style.display = 'block';
}
