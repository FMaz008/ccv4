/**
Cette fonction sert a charger une action a partir du menu
Cette fonction sert à faire un lien standard qui s'ouvre uniquement dans le panneau d'action
*/
actionLink = function(url)
{
	//BUT: Charger une page d'action dans le panneau d'action
	var objContent = $('#actionPanelContent');
	
	objContent.html('Chargement en cours...');
        $.ajax({
            url:'?popup=1&m=' + url,
            method: 'get',
            data: '', 
            dataType: "html",
            success: function (data) {
                $('#actionPanelContent').html(data);
            }
        });
	
	//Masquer le menu, peu-importe son statut
	objContent.hide(); //La visibilité va etre inversée par showhideaction()
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
	
	$('#actionPanelContent').html('Chargement en cours...');
	
        $.ajax({
            url:$(objForm).attr("action"),
            method: $(objForm).attr("method"),
            data: $(objForm).serialize(), 
            dataType: "html",
            success: function (data) {
                $('#actionPanelContent').html(data);
            }
        });
	
	return false; //Ne pas envoyer le formulaire

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
		$("#countdown").html("expirée");
	}
	else
	{
		var minutes = Math.floor(Time_Left / 60);
		var seconds = Time_Left - (minutes * 60);
	
		$("#countdown").html(CD_ZP(minutes) + ':' + CD_ZP(seconds));
		setTimeout('countdown(' + (Time_Left-1) + ');', 1000);
	}
}






ajaxExtendSession = function()
{
    //Load the login page (or any page), which will re-extend the session in PHP.
    
    $.ajax({
        url:"?v=Login&popup=1",
        method: 'get',
        data: '', 
        dataType: "html",
        success: function (data) {
            //Nothing to be done, really.
        }
    });
    setTimeout('ajaxExtendSession();', 600*1000); //Extra 10min
}