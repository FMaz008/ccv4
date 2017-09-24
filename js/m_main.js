
toggleActionPanel = function()
{	//BUT: Affichier/Masquer le panneau d'action
	var objContent = $('#actionPanelContent');
	var objPanel = $("#actionPanel");
	
	if (objContent.css('display') == 'none'){
            objPanel.css({ width : "810px" });
            objContent.show();
	}else{
            objContent.hide();
            objPanel.css({ width : "700px" });
	}
}

checkAll = function()
{	//BUT: Cocher toutes les cases à cocher de la page
	void(d=document);
	void(el=d.getElementsByTagName('INPUT'));
	for(i=0;i<el.length;i++)
		void(el[i].checked=1)
}

confirmdel = function($msg)
{	//BUT: Demander une confirmation
	return confirm ($msg);
}


