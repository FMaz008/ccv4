var draganddrop_version = 2;

// Conception de Francois Mazerolle, 2006
// Vous pouvez utiliser et/ou modifier ce script dans un contexte non-lucratif seulement.
// Si vous désirez une authorisation d'usage commerciale, veuillez me contacter à l'adresse suivante: admin@maz-concept.com
//
// Conception by Francois Mazerolle, 2006
// You can use and/or modify this script for non-lucrative purposes only.
// If you need a commercial usage authorisation, please contact me at: admin@maz-concept.com 

//BUG(S) À CORRIGER:
// - Si un DG contiend un tag <img> le drag va mal démarré si le onMousedown est fait sur l'image.

//Note: DG = L'élément déplacable (dragable), DZ= Drop Zone, zone pour lacher un DG.


//Concernant tout le script
var isdrag=false; //Si un Drag est en cours.
var dragobj; //DG qui est en train d'être dragé
var dzallowed; //DZ authorisé par le DG
var startdz; //Concerver en mémoire la DZ de départ

//Concernant la fonction SlideTo
var sensX=0;
var sensY=0;
var moveSteps=-1; //Combien de déplacement il y aurra entre les 2 positions. Pas une config, elle doit commencer à -1

// GESTION DES ACTIONS ET FONCTION SUR LES OBJETS
//Concernant les fonctions qui servent à détecter des positions et celles qui utilisent les positions
var posx = 0; // Position actuelle du curseur
var posy = 0;
var posxobj = 0; //Position du curseur sur l'objet (soustraite cette valeur à la position du curseur pour avoir les coordonnées de l'objet)
var posyobj = 0;
var posxini = 0; //Position d'origine du DG
var posyini = 0;
var elemposx= 0; //Position d'un élément
var elemposy= 0;

position = function(e){ // Fonction qui détecte la position du curseur sur la page

	if (!e) var e = window.event;
	if (e.pageX || e.pageY){
		posx = e.pageX;
		posy = e.pageY;
	}else if (e.clientX || e.clientY){
		posx = e.clientX + document.body.scrollLeft;
		posy = e.clientY + document.body.scrollTop;
	}
	
	if(isdrag){
		SetCurrentDGPos(posx-posxobj,posy-posyobj);
	}
}

ElementPosition = function(obj){ // Fonction qui détecte la position d'un élément sur la page
	//Définir la position de l'élément
	elemposx=0;
	elemposy=0;
	
	if (obj.offsetParent){
		while (obj.offsetParent)
		{
			elemposx += obj.offsetLeft
			elemposy += obj.offsetTop
			obj = obj.offsetParent;
		}
	}else if (obj.x){
		elemposx += obj.x;
		elemposy += obj.y;
	}	
}

FindDiv = function(type,name){ //Fonction qui retourne l'objet selon son type et son nom (id, param#2)
	//Trouver le DG
	var dgIds;
	var dgId;
	void(el=document.getElementsByTagName("div"));
	for(i=0;i<el.length;i++){
		dgIds=el[i].id.split("|");
		if(dgIds[0]==type){ //Si l'objet trouvé est du type de celui que l'ont recherche
		
			dgId=dgIds[1].split(",");
			for(e=0;e<dgId.length;e++){ //Lister tout les id associé à cet item
			
				//Si ll'objet est celui recherché
				if(name==dgId[e]){
					return el[i];
				}
			}
		}
	}
	return false;
}

FindCurrentDZ = function(mba) { //Fonction qui recherche si nous sommes au dessus d'une DZ et qui retourne l'objet DZ si oui.
	//mba = Must Be Authorized (true ou false)
	var IsAuthorised=false;
	var dzIds;
	var dzId;
	void(el=document.getElementsByTagName("div"));
	for(var i=0;i<el.length;i++){
		if(el[i].id.substring(0,2)=="dz"){
		
			if (mba){
				//Passer toutes les DZ authorisés en mémoire
				for (var e=0;e<dzallowed.length;e++){
					dzIds=el[i].id.split("|");
					
					//Si la DZ est authorisée
					dzId=dzIds[1].split(",");
					for(j=0;j<dzId.length;j++){ //Lister tout les id associé à cet item
						if(dzallowed[e]==dzId[j]){
							IsAuthorised=true;
						}
					}
				}
			}else{ IsAuthorised=true; }
			
			if (IsAuthorised){
				//Trouver la position réelle sur la page de l'élément
				ElementPosition(el[i]);
				
				//Déterminer si le curseur est dans la DZ
				if ((elemposx < posx)
				&& ((elemposx+el[i].offsetWidth) > posx)
				&& (elemposy < posy)
				&& ((elemposy+el[i].offsetHeight) > posy)){
					return el[i];
				}
			}
			IsAuthorised=false;
		}
	}
	return false; //Aucune DZ trouvée
}



start_drag = function(e){ //Détecter les début de click pour lancer la début d'un drag

	//Vérifier qu'aucun drag est en cours actuellement
	if(isdrag){ return false; }
	
	//Trouver la position actuelle du curseur
	position(e);
	
	//Trouver tout les DG
	var dgId;
	void(el=document.getElementsByTagName("div"));
	for(var i=0;i<el.length;i++){
		dgId=el[i].id.split("|");
		if(dgId[0]=="dg"){
		
			//Déterminer si le curseur est dans le DG
			if (el[i].offsetLeft < posx
			&& (el[i].offsetLeft+el[i].offsetWidth) > posx
			&& el[i].offsetTop < posy
			&& (el[i].offsetTop+el[i].offsetHeight) > posy){
				
				//Encadrer les DZ acceptées par ce DG
				dzallowed=dgId[2].split(",");
				
				//Trouver si nous sommes actuellement dans un DZ
				startdz = FindCurrentDZ(true);
				
				var dzId;
				for (var e=0;e<dzallowed.length;e++){
					if(dzallowed[e]!=""){
						dzId=FindDiv("dz",dzallowed[e]);
						
						if(!dzId){
							alert (dzallowed[e]+" est indéfini.");
						}else{
							if (dzId!=startdz){
								dzId.className="dropable_on";
							}
						}
					}
				}
			
				//Trouver la position innitiale du DG que nous dragons (s'il est dropé n'importe ou, il reviendra à sa position innitiale)
				posxini = el[i].offsetLeft;
				posyini = el[i].offsetTop;
				
				//Démarrer un Drag (afin d'empecher 2 drag simultanés)
				isdrag = true; 
				
				//Placer le DG au dessus de tout
				el[i].style.zIndex=9999;
				
				// Mettre en mémoire l'objet que nous sommes en train de drager
				dragobj = el[i];
				
				//Trouver la position du curseur dans l'objet
				if(isdefined('dragobj')) {
					posxobj=posx-dragobj.offsetLeft;
					posyobj=posy-dragobj.offsetTop;
				}
				
				//Mettre un curseur de déplacement
				document.body.style.cursor="move";
				
				//Surveiller les mouvement de souri ainsi que le relachement du bouton de souris.
				if (document.attachEvent) { //IE
					document.attachEvent ('onmousemove', position);
					document.attachEvent ('onmouseup', stop_drag);
				}else{ //FF
					document.addEventListener('mousemove', position, false);
					document.addEventListener('mouseup', stop_drag, false);
				}
				
				//Quitter la fonction afin de ne pas démarrer 2 drag en même temps.
				return false;
					
			}
		}
	}
}

stop_drag = function(e){ //Fonction qui arrête le Drag.
	var dzId;
	var dz;
	
	//Vérifier qu'un drag est en cours actuellement (Sinon on est supposé arrêter quoi ? Bug de IE qui detach pas assez rapidement l'objet)
	if(!isdrag){ return false; }
	
	if (document.attachEvent) { //IE
		detachEvent ('onmousemove', position);
	}else{
		document.removeEventListener('mousemove', position, false);
	}
	

	//Trouver si nous sommes actuellement sur une DZ
	dz = FindCurrentDZ(true);
	if(dz){ // Si une DZ authorisée est trouvée
		dzId=dz.id.split("|");
		
		//Trouver la position réelle sur la page de l'élément
		ElementPosition(dz);
			
		//Changer la position d'origine de l'élément à dragger pour la position de la DZ authorisée**
		posxini = elemposx+(dz.offsetWidth/2-dragobj.offsetWidth/2);
		posyini = elemposy+(dz.offsetHeight/2-dragobj.offsetHeight/2);
		
		//Un changement à été fait, vérifier si la DZ comporte une action et que la DZ n'est pas celle de départ.
		if (dzId.length>2 && startdz!=dz) {
			eval(dzId[2]); // Exécuter l'action de la DZ.
		}
	}
	
	
	//Passer toutes les DZ authorisés en mémoire
	for (var e=0;e<dzallowed.length;e++){
		dzId=FindDiv("dz",dzallowed[e]);
		dzId.className="dropable_off";
	}
	
	//Replacer l'item à son emplacement initial ou dans son DZ**^^ si c'est nécésaire
	slideTo(posxini,posyini,10);
	
	//Placer le DG à son élévation normale
	dragobj.style.zIndex=1;
				
	//Mettre un curseur normal
	document.body.style.cursor="default";
}


//Fonctions de déplacement
slideTo = function(tx, ty, interval) { //Fonction qui "Glisse" un objet vers une position
	
	if(moveSteps==-1){
		moveSteps=20; //Nombre de déplacement à effectuer (Plus le nombre est grand plus le déplacement est lent)
	}
	
	// Calculer le "pas" de chaque déplacement
	sensX=Math.round((dragobj.offsetLeft-tx)/moveSteps)
	sensY=Math.round((dragobj.offsetTop-ty)/moveSteps)
	
	if(moveSteps>0){
		dragobj.style.left=dragobj.offsetLeft-sensX+"px";
		dragobj.style.top=dragobj.offsetTop-sensY+"px";
	}
	moveSteps-=1;
		
	//Si le déplacement est terminé, placer précisément l'objet et stoper le timer, sinon, continuer le déplacement
	if(moveSteps==0){
		if(isdefined('timerID')) {
			clearTimeout(timerID);
		}
		SetCurrentDGPos(posxini,posyini);
		moveSteps=-1;
		
		// Le drag est terminé (Note, si jamais le drag ne se terminait jamais, il sera impossible de commencer un autre drag)
		isdrag = false; 
	}else{
		timerID = setTimeout("slideTo("+tx+", "+ty+", "+interval+")", interval);
	}
}

SetDGintoDZ = function(dg,dz,smooth){ //Fonction qui place le DG dans un DZ simplement en passant leurs noms.


	dragobj=FindDiv("dg",dg);
	
	//Si le DG est introuvable, innutile de chercher le DZ
	if(!isdefined('dragobj')) { return false; }
	
	
	//Trouver la position du DZ
	dropobj=FindDiv("dz",dz);
	ElementPosition(dropobj);
	posxini = elemposx+(dropobj.offsetWidth/2-dragobj.offsetWidth/2);
	posyini = elemposy+(dropobj.offsetHeight/2-dragobj.offsetHeight/2);

	
	//Effectuer le placement
	if(smooth){
		slideTo(posxini,posyini,5)
	}else{
		SetCurrentDGPos(posxini,posyini);
	}
}

SetCurrentDGPos = function(x,y){ // Fonction qui place le DG actuel (dragobj) à une position précise.
	dragobj.style.left = x+"px";
	dragobj.style.top  = y+"px";
	return false;
}






//Mini-fonction d'une ligne
IfDrag_retFalse = function(e){ if(isdrag) return false; } //Retourne false si un drag est en cours
isdefined = function(variable){ return (typeof(window[variable]) == "undefined")?  false: true; } //Fonction qui détermine si une variable est définie




//Surveiller les évènements qui lance le drag
if (document.attachEvent) { //IE
	document.attachEvent ('onmousedown', start_drag);
}else{
	document.addEventListener("mousedown",start_drag,true);

}
//Afin d'éviter que le drag sélectionne du texte en arrière plan lors de déplacements
document.onselectstart=IfDrag_retFalse;
document.onmousedown=IfDrag_retFalse;


// ### FIN DU SCRIPT
