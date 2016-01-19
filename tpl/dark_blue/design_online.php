<?php
/** 
 * Fichier CSS
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.2
 * @package CSS
 */
header('Content-type: text/css; charset=utf-8');

$borderColor1 = '#555599';
$bgColor1 = 	'#000816';
$borderColor2 = '#44447F';
$bgColor2 = 	'#114466';
$borderColor3 = '#333366';
$bgColor3 = 	'#000033';
$valueColor = 	'#EEEEDD';
?>

/**
CE FICHIER EST SÉPARÉ COMME SUIT:
- Général ......................... ( Style global du site )
- Layout .......................... ( Positionnement des zones principales du site )
- Zones et Tableaux ............... ( Style génénal des tableaux/panel )
- Formulaires ..................... ( Style appliqués aux éléments des formulaires )
- Styles applicables aux textes ... ( Style de certains type de texte: Homme, Femme, HJ, IJ, etc. )

- Spécificités:
-- Visitor
-- Member
-- Member Actions

*/


/* ############################################################### */
/* ### GÉNÉRAL */

body {
	background-color: #000000;
	color: #FFDDAA;
	margin:0px;
	font-family: Georgia, "MS Serif", "New York", serif; 
	font-size: 12pt;
}

body a, span.fakelink {
	color: <?=$valueColor;?>;
	text-decoration:none;
	cursor:pointer;
}

body a:hover, span.fakelink:hover {
	color: <?=$valueColor;?>;
	text-decoration:underline;
}

.clearboth{
	clear:both;
}

.infobulle{
	position: absolute;
	display:none;
	background-color:<?=$bgColor1;?>;
	padding:1px;
	border:1px solid <?=$borderColor1;?>;
	z-index:999;
}
.hovermenu
{
	position: absolute;
	display:none;
	z-index:999;
}


/* ############################################################### */
/* ### LAYOUT */


div.menu a {
	text-decoration: none;
	text-align:right;
	color: #666699;

	display: block;
	margin:0px;
	padding-right:5px;
	
	border-left:1px solid <?=$borderColor3;?>;
	border-bottom:1px solid <?=$borderColor3;?>;
}

div.menu a:hover {
	text-align:right;
	color:<?=$valueColor;?>;
	background-image: url('img/design_menusel.gif');
	background-repeat: no-repeat;
	background-position: 120px 0px;

	margin:0px;
	padding-right:10px;
	margin-right:5px;

	border-left:2px solid #8888FF;
	border-bottom:1px solid #8888FF;
}



div.forumtitre
{
    font-weight:bold;
    
}
div.forumsujet
{
    color:<?=$valueColor;?>;
	text-decoration:none;
}
hr.forumsep
{
    margin-top: 2px;
    margin-bottom: 2px;
}

div.topbar {
	z-index:10;
	left:0px;
	top:0px;
	width:1000px;
	height:62px;
	background-image:url('img/design_topbar.gif');
	background-position: 0px 0px;
	background-repeat:no-repeat;
}



div.leftbar {
	float:left;
}
div.leftbarseparator {
	z-index:10;
	left:0px;
	top:0px;
	width:135px;
	height:10px;

	margin-top:1px;
	margin-bottom:1px;
	padding:0px;

	background-image: url('img/design_leftbarsep.gif');
	background-position: 15px 0px;
	/*background-repeat: repeat-y;*/
	background-repeat: no-repeat;
}
div.menu {
	z-index:10;
	left:0px;
	top:0px;
	width:135px;

	padding:5px;
	padding-top:80px;
	margin-left:10px;

	background-image: url('img/design_menubg.gif');
	background-color: <?=$bgColor1;?>;
	background-position: -10px 0px;
	background-repeat: no-repeat;
	border:2px solid <?=$borderColor1;?>;
}


div.info {
	z-index:10;
	left:0px;
	top:0px;
	width:135px;

	padding:5px;
	margin-left:10px;

	text-align:right;
	background-image:url('img/design_leftbarbg_darken.gif');
	background-position: 3px 0px;
	/*background-color: <?=$bgColor1;?>;*/
	border:2px solid <?=$borderColor1;?>;
}

div.time {
	z-index:10;
	left:0px;
	top:0px;
	width:135px;

	padding:5px;
	margin-left:10px;

	text-align:left;
	background-image:url('img/design_leftbarbg_darken.gif');
	background-position: 3px 0px;
	/*background-color: <?=$bgColor1;?>;*/
	border:2px solid <?=$borderColor1;?>;
	
}

div.forum {
	overflow:hidden;
	z-index:10;
	left:0px;
	top:0px;
	width:135px;

	padding:5px;
	margin-left:10px;

	text-align:left;
	background-image:url('img/design_leftbarbg_darken.gif');
	background-position: 3px 0px;
	/*background-color: <?=$bgColor1;?>;*/
	border:2px solid <?=$borderColor1;?>;
	
}



div.certification {
	z-index:10;
	left:0px;
	top:0px;
	width:135px;

	padding:5px;
	margin-left:10px;

	text-align:center;
	background-color: <?=$bgColor1;?>;
	border:2px solid <?=$borderColor1;?>;
	
}

div.main {
	overflow:auto;
	z-index:1;
	left:150px;
	top:70px;
	width:800px;

	margin:0px;
	padding:10px;
	margin-left:10px;

	text-align:left;
}

div.version{
	position:absolute;
	z-index:15;
	left:120px;
	top:45px;
	text-align:center;
	color:#FFFFFF;
	font-size:14pt;
}

div.footer{
	top:auto;
	clear:both;
	margin-top:20px;
}



/* ############################################################### */
/* ### ZONES et TABLEAUX */


/** Positionnement, style et taille par défaut d'un tableau
*/
div.tlb_center, table.tbl_center{
	border:1px solid <?=$borderColor1;?>;
	background-color:<?=$bgColor1;?>;
	font-size:10pt;
	margin-right:auto;
	margin-left:auto;
	width:700px;
}



/** Style d'un titre de tableau (généralement la 1iere ligne du tableau)
*/
div.title, td.title {
	color:<?=$valueColor;?>;
	border:1px solid <?=$borderColor1;?>;
	background-color: #070f25;
	font-size:10px;
	font-weight:bold;
	text-align:right;
	background-image:url('img/title_bg.png');
	background-repeat:repeat-y;
	background-position:right;
	padding:2px 0px 2px 1px;
}

/** Style d'une cellule qui identifie une valeur
*/
div.name, td.name {
	border:1px solid <?=$borderColor1;?>;
	background-color: #001632;
	font-size:12px;
	font-weight:bold;
	text-align:left;
	padding:3px 2px 3px 1px;
	margin:1px 1px 0px 0px;
}

/** Style d'une cellule contenant une valeur
*/
div.value, td.value {
	border:0px;
	font-size:11px;
	padding:2px;
	margin:1px 0px 0px 1px;
	background-color: #001122;
}


/** Style du conteneur des boutons d'actions du formulaire
*/
div.send, td.send {
	font-size:10px;
	font-weight:bold;
	vertical-align:top;
	text-align:center;
	padding:2px;
}

/** Style générale d'une zone ( menu, he, panel, etc..)
*/
div.panel {
	background-color:<?=$bgColor1;?>;
	border:1px solid <?=$borderColor1;?>;
	margin:0px auto 0px auto;
	padding:2px;
}

div#ajaxLogin_error
{
	display:none;
	color:#FF0000;
	font-weight:bold;
}
div.plzwait_ombre{
	position:fixed;
	z-index:997;
	display:none;
	opacity:0.40;
	top:0px;
	left:0px;
	background-color:#000000;
	height:100%;
	width:100%;
}

div.plzwait_content{
	position:fixed;
	z-index:998;
	display:none;
	top:100px;
	width:100%;
}






/* ############################################################### */
/* ### FORMULAIRE */

input[type="button"], input[type="submit"] {
	font-family: Georgia, "MS Serif", "New York", serif;
	padding: 2px;
	font-size: 10px;
	color: #FFFFFF;
	background-color: #001632;
	border: solid 1px <?=$borderColor1;?>;
	-moz-border-radius:15px 2px;
	-webkit-border-radius:15px 2px;
	
	margin:1px;
	width:140px;
}
input[type="button"]:hover, input[type="submit"]:hover {
	background-color: #323264;
	border: solid 1px #9999FF;
}


input[type="text"], input[type="password"] {
	border: 1px <?=$borderColor1;?> solid;
	font: bold 11px Georgia, "MS Serif", "New York", serif;
	color: #CCCCCC;
	padding: 2px;
	background: #000000;
	-moz-border-radius:15px 2px;
	-webkit-border-radius:15px 2px;
}

input[type="text"]:hover, input[type="password"]:hover {
	border: 1px #FFDDAA solid;
	color: #FFFFFF;
}

/*
input:disabled {
	color: #444;
	border-color: #444;
}
*/

textarea {
	border: 1px <?=$borderColor1;?> solid;
	font: 11px Georgia, "MS Serif", "New York", serif;
	color: #CCCCCC;
	padding: 2px;
	background: #000000;
}
textarea:hover {
	border: 1px #FFDDAA solid;
	color: #FFFFFF;
}

select {
	border: 2px <?=$borderColor1;?> solid;
	color: #FFddaa;
	background-color: #000000;
}

option { /* Elements de la select list */
	border: 1px <?=$borderColor1;?> solid;
	border-top:0px;
	padding:0px;
	margin:0px;
	padding-right:3px;
	color: #FFddaa;
	background-color: #111133;
}






/* ############################################################### */
/* ### STYLES applicables au TEXTE */


.txtStyle_risque, .txtStyle_risque:hover
{
	text-decoration:none;
	font-weight:bold;
	color:#A3A405;
}
.txtStyle_critique, .txtStyle_critique:hover
{
	text-decoration:none;
	font-weight:bold;
	color:#A41605;
}
.txtStyle_grayed, .txtStyle_grayed:hover{
	text-decoration:none;
	color:#666;
}
.txtStyle_system, .txtStyle_system:hover {
	text-decoration:none;
	color: #FFDDAA;
}
.txtStyle_homme, .txtStyle_homme:hover {
	text-decoration:none;
	color:#AAAAFF;
}
.txtStyle_femme, .txtStyle_femme:hover {
	text-decoration:none;
	color:#FFAAFF;
}
.txtStyle_autre, .txtStyle_autre:hover {
	text-decoration:none;
	color:#FF0000;
}
.txtStyle_date, .txtStyle_date:hover {
	text-decoration:none;
	font-size:8pt;
	font-family:courier new;
	color: <?=$valueColor;?>;
}
.txtStyle_valeur, .txtStyle_valeur:hover {
	text-decoration:none;
	font-size: 11pt;
	color: <?=$valueColor;?>;
}

.txtStyle_heHj, .txtStyle_heHj:hover {
	color:#AA8855;
}

.txtStyle_heDesc, .txtStyle_heDesc:hover {
	color:#CCBB77;
}




















/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ### S-P-É-C-I-F-I-C-I-T-É-S --- P-A-G-E-S : VISITEURS ### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */


div.visitor_titre{
	font-size:18pt;
	margin:20px 0px 20px 0px;
}


/* ### index_full */

img.index_full_imgAnnuaire {
	border:0;
	width:88px;
	height:31px;
}

div.index_full_erreur{
	background-color:#330000;
}

div.index_full_erreurName{
	background-color:#990000;
	color:#CCCCCC;
}

div.forumtext{
	text-align:center;
	font-size:8pt;
}

div.time_gametime, div.time_sessioncountdown{
	text-align:center;
}

div.footer p{
	font-size:8pt;
	line-height:9pt;
	margin:2px;
}


/* ### index_lite */

div.footer span{
	font-size:6pt;
	color:#999999;
}
div.index_lite_erreur{
	background-color:#330000;
}

div.index_lite_erreurName{
	background-color:#990000;
	color:#CCCCCC;
}


/* ### VISITOR - Login */

div.login_panel{
	margin: 100px auto 0px auto;
	width:300px;
	border:0px;
}
div.login_panel div.content{
	width:200px;
	margin:10px auto;
}

div.login_lineUser, div.login_linePass, div.login_lineSubmit{
	clear:both;
	margin-right:0px;
}

div.login_lineSubmit{
	margin-top:10px;
}

div.login_col1User, div.login_col1Pass{
	float:left;
	width:35px;
}

div.login_col2User, div.login_col2Pass{
	padding-right:0px;
	text-align:right;
}

div.login_note{
	margin: 100px auto;
	width:400px;
}

div#login_navs_pub{
	border:#CCC;
	background-color:#FFF;
	margin:10px auto;
	width:500px;
	color:#000;
}
div#login_navs_pub div#navs{
	margin:0 auto;
	text-align:center;
	width:370px;
}
div#login_navs_pub div#navs div.nav{
	float:left;
	margin:0 30px;
}

/* ### VISITOR - login_wrong */

div.login_wrong_error{
	margin-top:100px;
	margin-bottom:40px;
}


/* ### VISITOR - About */

div.about{
	width:400px;
	margin-left:auto;
	margin-right:auto;
}
div.about_categorie{
	clear:both;
	font-weight:bold;
	text-decoration:underline;
	width:100%;
	text-align:center;
}

div.about_poste{
	float:left;
	width:200px;
	text-align:right;
}
div.about_nom{
	float:left;
	width:175px;
	margin-left:15px;
}

/* ### VISITOR - background & faq */
div.background_contenu, div.regles_contenu{
	text-align:justify;
}

div.background_contenu p{
	margin-bottom:0px;
	margin-top:0px;
	
}

div.background_contenu p:first-letter{
	padding-left:20px;
}

div.background_contenu p.first:first-letter{
	font-weight: bold;
	font-size:24pt;
	font-family:Times;
	line-height:12pt;
}

div.background_chapitre{
	width:100%;
	text-align:center;
	font-size:14pt;
	font-weight:bold;
	margin-top:30px;
	margin-bottom:10px;
}

div.faq_contenu{
	text-align:justify;
}

div.background_menu, div.faq_menu{
	padding:5px;
}
span.faq_highligh
{
	color: <?=$valueColor;?>;
	font-weight:bold;
}
div.background_warning{
	font-style:italic;
	font-size:9pt;
	margin-top:15px;
}

/* ### VISITOR - inscription1 */

div.inscription_info{
	margin-bottom:20px;
	text-align:justify;
}

div.inscription_notice{
	font-size:8pt;
	text-align:center;
}

div.inscription_name{
	width:200px;
	float:left;
	margin:1px 5px 0px 1px;
}

p.inscription_note{
	margin-top:0px;
	font-weight:normal;
	font-size:8pt;
	color:#CCBB77;
}

div.inscription_condition{
	min-height:150px;
}


/* ### VISITOR - main */

div.main_zoneAll{
	float:left;
	width:650px;
	
	padding:10px;
}

div.main_zoneLogo{
	width:100%;
	text-align:center;
	margin:20px 0px 20px 0px;
}

div.main_zoneTxt{
	text-align:justify;
}

div.main_zoneCitation{
	margin-top:20px;
	
	font-size:8pt;
	right:0px;
	margin-right:0px;
	padding:5px;
}

div.main_zoneCitation span.aquo{
	font-size:15pt;
	margin:5px;
}

div.main_zoneCitationCitation{
	margin-left:15px;
	font-style:italic;
}


/* ### VISITOR - passRecover (1 & 2) */

div.passreco_panel{
	width:400px;
	margin-left:auto;
	margin-right:auto;
}

div.passreco_value{
	padding-bottom:10px;
}




/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ### S-P-É-C-I-F-I-C-I-T-É-S --- P-A-G-E-S : MEMBRES ### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */

/* ### MEMBER - pub mp iframe */


div.pubMp
{
	margin-top:20px;
	width:100%;
	text-align:center;
}


/* ### MEMBER - news */
div.news_panel1, div.news_panel2{
	width:400px;
	margin:50px auto 50px auto;
}

div.news_choix{
	float:right;
}

/* ### MEMBER - Abonnement */
table#abo_progress
{
	margin:0 auto;
}
table#abo_progress td.sep
{
	font-size:18pt;
	padding:0px 20px;
}

/* ### MEMBER - index */

div.member_index_topmenu{
	margin:auto 0px auto 0px;
	height:12px;
}


div.menuTab {
	text-decoration:none;
}

div.menu_action_panel, div.member_index_topmenu_options_menu
{
	background-color:#000;
	width:200px;
}
div.menu_action_panel a, div.member_index_topmenu_options_menu a{
	display: block;
	margin:0px;
	padding-left:5px;
	
	font-size: 10pt;
	text-decoration: none;
	text-align:left;
	color: <?=$valueColor;?>;

	border-top:1px solid #000000;
	border-bottom:1px solid <?=$borderColor3;?>;
}

div.menu_action_panel a:hover, div.member_index_topmenu_options_menu a:hover{
	padding-left:10px;
	
	font-style: oblique;
	color:#FFFFFF;
	text-decoration: none;

	border-top:1px solid #8888FF;
	border-bottom:1px solid #8888FF;
}


div.menu_action_panel a.menu_action_PPA { 
	margin-top:5px;
	
	font-weight:bold;
	text-align:center;
}
div.menu_action_panel a.menu_action_PPA:hover { 
	font-style: oblique;
	text-align:center;
	color:#FFFFFF;
	
	border-top:1px solid #8888FF;
	border-bottom:1px solid #8888FF;
}



div.member_index_topmenu_options{
	float:left;
}

div.member_index_topmenu_options_menu{
	width:150px;
}

div.member_index_topmenu_action_menu{
	float:right;
	width:200px;
}


table.infopj{ 
	margin:20px auto 0px auto;
	text-align:center;
}

div.member_index_panel{
	height:auto;
	width:694px; /* div.panel ajoute (1px de bordure + 2px de padding)*2==6px */
	margin:20px auto 0px auto;
}

div.member_index_panel_title{
	float:left;
}

div.member_index_panel_button{
	float:right;margin-right:5px;
}

div.member_index_panel_content{
	width:808px;
}

/* ### MEMBER - ContactMJ */

div.member_contactMj_topMenu{
	top:0px;
	width:700px;
	margin: 0px auto 0px auto;
}

div.ongletOn, div.ongletOff
{
	float:left;
	width:145px;
	padding:5px;
	border:2px solid;
	border-bottom:0px;
	cursor:pointer;
	border:1px solid <?=$borderColor3;?>;
}
div.ongletOn
{
	height:20px;
	margin-top:0px;
	background-color: #222244;
	
	-moz-border-radius-topleft:15px;
	-moz-border-radius-topright:15px;
	-webkit-border-radius-topleft:15px;
	-webkit-border-radius-topright:15px;
}
div.ongletOn span
{
	font-weight:bold;
}
div.ongletOff
{
	height:15px;
	margin-top:5px;
	background-color: <?=$bgColor3;?>;
	
	-moz-border-radius-topleft:5px;
	-moz-border-radius-topright:5px;
	-webkit-border-radius-topleft:5px;
	-webkit-border-radius-topright:5px;
}
div.ongletOff:hover
{
	background-color:<?=$bgColor2;?>;
}
div.ongletOff span
{
	font-weight:normal;
}

div.member_contactMj_content{
	clear:both;
	border:2px solid <?=$borderColor3;?>;
	width:700px;
	height:500px;
	margin: 0px auto 0px auto;
	padding-top:15px;
}

div.member_contactMjMod_notice{
	margin: 15px auto 15px auto;
	text-align:center;
	font-weight:bold;
	font-style:italic;
}

div.member_contactMjMod_messageContainer{
	text-align:center;
}


/* ### MEMBER - ContactMjMod */

div.member_contactMjMod_name{
	float:left;
	width:150px;
}


/* ### MEMBER - DelPerso */

div.member_delPerso{
	font-size:16pt;
}


/* ### MEMBER - CreerPerso */
div.member_creerPerso_text{
	text-align:justify;
}


/* ### MEMBER - CreerPerso2 */

span.member_creerPerso2_refusPanel{
	margin:0px auto 0px auto;
}


/* ### MEMBER - He Header */

/* ############################################################### */
/* ### HISTORIQUE DES ÉVÈNEMENTS */



/* ### Header du HE */

div.member_he_toplink{
	float:right;
}
div.member_he_header {
	width:700px;
	margin:20px auto 0px auto;
	font-size:12pt;
}
div.member_he_header_gauche {
	float:left;
	margin-top:2px;
	width:140px;
	height:100px;
	padding:5px;
	border:1px solid <?=$borderColor1;?>;
	background-color: #001632;
	overflow-y:scroll;
}
div.member_he_header_gauche p{
	font-size:10pt;
	margin:5px 0px 5px 0px;
}
div.member_he_header_droite{
	 float:right;
	 width:540px;
}

div.member_he_header_droite_taille{
	float:left;
	font-weight:bold;
}
div.member_he_header_droite_pub{
	float:right;
	margin-top:5px;
	font-size:10pt;
	font-weight:bold;
}

div.member_he_header_bouttons_gauche{
	float:left;
	width:50%;
	text-align:center;
	vertical-align:top;
}
div.member_he_header_bouttons_droit{
	float:right;
	width:50%;
	text-align:center;
	vertical-align:top;
}

table.member_he_header_bar {
	width:100%;
	margin:10px 0px 10px 0px;
	border: 1px solid <?=$borderColor1;?>;
	height:10px;
}
td.member_he_header_bar_full{
	background-color:#00FF00;
}

td.member_he_header_bar_empty{
	background-color:#003300;
}




/* ### Items du HE */

div.he_leftbar{
	float:left;
	width:150px;
	
	border:1px solid <?=$borderColor1;?>;
	background-color: #001632;
	font-size:12px;
	font-weight:bold;
	text-align:left;
}
div.he_rightbar{
	float:left;
	margin-left:5px;
	width:535px;

	border-top:1px solid <?=$borderColor1;?>;
	font-size:12px;
	vertical-align: top;
	text-align:justify;
}

span.member_he_item_de, span.member_he_item_a{
	float:left;
	padding-left:3px;
}
div.member_he_item_de_liste{
	margin:5px 0px 0px 55px;
}
div.member_he_item_a_liste{
	margin-left:30px;
}

.he_item{
	margin: 5px auto 0px auto;
	width:700px;
	/*overflow:auto;*/
}

/* Style spéficique selon le type de message */
div.he_type_parlerbadge{
	border:1px dashed #995555;
}



/* ### Infobulles du header du HE */

img.imgbg { /* INFO-BULLE, cadrage de l'image */
	border:1px solid <?=$borderColor1;?>;
	padding:3px;
	margin-right:5px;
	background-color: #001632;
	text-align:left;
}



/* ############################################################### */
/* ### INVENTAIRE */

/*Afficher un icone lorsque le curseur passe au dessus d'une zone draggable, optionnel*/
.dragable{ /*IE*/
	position:absolute;
	cursor:pointer;
} 
.dragable:hover{ /*FF*/
	cursor:pointer;
} 
.dropable_off{
	border:0px;
	padding:1px;
	margin-left:auto;margin-right:auto;
}
.dropable_on{
	border:1px solid gray;
	background-color:#333333;
	opacity: 0.5;
	/* filter: alpha(opacity=50); */
	margin-left:auto;margin-right:auto;
	padding:0px;
}

		/* INUTILE ? background d'un item 
		div.inv_itemBg, td.inv_itemBg { 
			/*Propriétés additionnelles /
			width:60px;
			height:60px;
			/*background-repeat:no-repeat;/
			/*background-image:url(darkblue/inventaire/slot.gif);/
			/*Propriétés de td.name/
			border:1px solid <?=$borderColor1;?>;
			background-color: #001632;
			font-size:12px;
			font-weight:bold;
			text-align:center;
		}
		*/


div.inv_fiche { /*Anciennement #subcenter*/
	border:1px solid <?=$borderColor1;?>;
	background-color:<?=$bgColor1;?>;
	font-size:10pt;
	margin-right:auto;
	margin-left:auto;
	width:650px;
}




/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ### S-P-É-C-I-F-I-C-I-T-É-S --- P-A-G-E-S : MEMBRES ACTIONS ### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */
/* ############################################################### */


div.member_action_panel{
	width:600px;
}
div.member_action_persoContainer, div.member_action_itemContainer{
	overflow:auto;
}

/* ############################################################### */
/* ### ITEM - MENOTTER */

div.member_action_menotter_left{
	float:left;
	width:300px;
}
div.member_action_menotter_right{
	float:right;
	width:300px;
}

span.member_action_menotter_aucunItem{
	font-style:italic;
}

textarea.member_action_menotter_msg{
	width:550px;
	height:75px;
}


/* ############################################################### */
/* ### ITEM - SAC */

div.member_action_sac_left{
	float:left;
	width:298px;
}
div.member_action_sac_middle{
	width:600px;
}
div.member_action_sac_right{
	float:right;
	width:273px;
}

div.member_action_sac_linkoff{
	border-left:4px solid <?=$borderColor1;?>;
	cursor:pointer;
	padding-left:10px;
}
div.member_action_sac_linkoff:hover{
	background-color:#222266;
}
div.member_action_sac_linkon{
	background-color:<?=$borderColor1;?>;
	padding-left:10px;
	font-weight:bold;
}

div.member_action_sac_itemContainer{
	border:4px solid <?=$borderColor1;?>;
	padding:10px;
}

/* ############################################################### */
/* ### ITEM - FICHE PERSO */

table#member_action_fichecomp td.lvl
{
	padding:0px;
	width:18px;
	height:18px;
	text-align:center;
}
table#member_action_fichecomp td.normal
{
	font-size:6pt;
	color:#999;
}
table#member_action_fichecomp td.bonus
{
	font-size:6pt;
	border-color:#114411;
	background-color:#003300;
}
table#member_action_fichecomp td.malus
{
	font-size:5pt;
	border-color:#330000;
	background-color:#250000;
}
table#member_action_fichecomp td.invisible
{
	border-color:transparent;
	background-color:transparent;
}

table#member_action_fichecomp td.current
{
	font-size:8pt;
	color:#FFF;
}

table#member_action_fichestat td.lvl
{
	height:7px;
	width:10px;
}
