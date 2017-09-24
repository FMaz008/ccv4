// Conception de Francois Mazerolle, 2010
// Vous pouvez utiliser et/ou modifier ce script dans un contexte non-lucratif seulement.
// Si vous désirez une authorisation d'usage commerciale, veuillez me contacter à l'adresse suivante: admin@maz-concept.com
//
// Conception by Francois Mazerolle, 2010
// You can use and/or modify this script for non-lucrative purposes only.
// If you need a commercial usage authorisation, please contact me at: admin@maz-concept.com 



infobulle = function(obj, evt, posX, posY)
{
	if(posX == undefined) posX =5;
	if(posY == undefined) posY =5;
	
        
	var it = $('#ib_' + obj.attr("id"));

	if(it == undefined)
	{
		alert("L'infobulle #ib_" + obj.id + " ne peut être trouvée, veuillez rapporter le bug sur le forum.");
		return false;
	}
	
	if (posX + it.offsetWidth > obj.offsetWidth)
		posX = obj.offsetWidth - it.offsetWidth;
	if (posX < 0 )
		posX = 0; 
        
	it.css({ top : (evt.pageY + posY) + 'px' });
	it.css({ left : (evt.pageX + posX) + 'px' });

	it.css({ display : 'block' });
	obj.css({ cursor : "pointer"});
	obj.mouseout(function(event) {
           it.css({ display : 'none' });
	});
}

hovermenu = function(obj, evt, paddingX, paddingY)
{
	if(paddingX == undefined) paddingX =0;
	if(paddingY == undefined) paddingY =0;
	
	var it = $('hm_' + obj.id);

	if(it == undefined)
	{
		alert("Le menu ib_" + obj.id + " ne peut être trouvée, veuillez rapporter le bug sur le forum.");
		return false;
	}
	
	it.style.top = obj.cumulativeOffset().top + "px";
	it.style.left = obj.cumulativeOffset().left + "px";
	it.style.paddingTop = paddingY + "px";
	it.style.paddingLeft= paddingX + "px";
	it.style.width = obj.getWidth() + "px";
	it.style.display = 'block';
	it.observe('mouseleave',  function(event){
									it.style.display = "none";
								});
}

var heBulleTxt = new Array();

persoinfo = function(obj, evt, posX, posY)
{
	//Afficher la bulle
	infobulle(obj, evt, posX, posY);
	$('#ibmsg_' + obj.id).html("Chargement en cours...");

	if(heBulleTxt[obj.id] == undefined)
	{
		//Charger
                $.ajax({
                    url:'?popup=1&m=PersoBulle',
                    method: 'post',
                    data: 'id='+obj.id, 
                    dataType: "html",
                    success: function (data) {
                        heBulleTxt[obj.id] = data;
                        $('#ibmsg_' + obj.id).html(heBulleTxt[obj.id]);
                    },
                    error: function (data) {
                        $('#ibmsg_' + obj.id).html("Erreur lors du chargement");
                    }
                });
	}
	else
	{
		$('#ibmsg_' + obj.id).html(heBulleTxt[obj.id]);
	}
}
