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

$borderColor1 = '#BABAC6';
$bgColor1 = 	'#211610';
$borderColor2 = '#AA856E';
$bgColor2 = 	'#400F08';
$borderColor3 = '#9A4F16';
$bgColor3 = 	'#600806';
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
	color: #CCB88B;
	margin:0px;
	font-family: Georgia, serif, "New York"; 
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

.center{
	margin:0 auto;
	text-align:center;
}


/* ############################################################### */
/* ### LAYOUT */

div#site
{
	position:relative;
	width:980px;
	margin:0 auto;
}

div#site div#header
{
	position:relative;
	width:980px;
	height:161px;
	background-image:url('img/header.jpg');
}

div#site div#header div#revision
{
	position:absolute;
	left:268px;
	top:115px;
	width:53px;
	text-align:center;
}
div#site div#header div#aboutus
{
	position:absolute;
	left:30px;
	top:135px;
	width:80px;
	text-align:center;
	font-weight:bold;
	font-size:12px;
}

div#site div#header div#modedebug
{
	position:absolute;
	left:325px;
	top:140px;
}

div#site div#header div#infos
{
	position:absolute;
	left:318px;
	top:24px;
	width:95px;
	height:65px;
}
div#site div#header div#foruminfos
{
	position:absolute;
	left:465px;
	top:10px;
	width:475px;
	height:140px;
}
div#site div#header div#foruminfos div.forumtitre
{
        font-weight:bold;
	color:black;
}
div#site div#header div#foruminfos div.forumsujet a
{
        font-size:10pt;
	font-family:Courier, monospace;
        color:black;
	padding-left:10px;
}
div#site div#header div#foruminfos hr.forumsep
{
	display:none;
}



div#site div#content
{
	position:relative;
	width:944px;
	padding:0 18px;
	background-image:url('img/background.jpg');
	background-repeat:repeat-y;
}
div#site div#content div#menu
{
	width:944px;
	height:41px;
}
div#site div#content div#menu div.bouton
{
	float:left;
	display:block;
	width:102px;
	height:23px;
	padding:8px 8px 10px 8px;
	background-image:url('img/bouton.jpg');
	text-align:center;
}

div#site div#content div#footer
{
	padding:20px 10px 0px 10px;
	clear:both;
}

div#site div#content div#content2
{
	padding:0 10px;
	clear:both;	
}
div#site div#content div#footer div#footernote
{
	font-size:10px;
}
div#site div#content div#footer div#links
{
	margin:0 auto;
	text-align:center;
}
div#site div#realfooter
{
	position:relative;
	width:980px;
	height:40px;
	background-image:url('img/footer.jpg');
}

/* ############################################################### */
/* ### ZONES et TABLEAUX */

div#site div#header div#revision span
{
	font-family:arial;
	color:#EDF1F4;
	font-size:9px;
}
div#site div#header div#modedebug span
{
	color:#F00;
	font-size:8px;
	font-weight:bold;
}
div#site div#header div#infos div.titre
{
	font-size:10px;
	color:#999;
}
div#site div#header div#infos div.propname
{
	clear:both;
	float:left;
	font-size:10px;
	padding-left:5px;
	color:#999;
}
div#site div#header div#infos div.valeur
{
	float:right;
	font-size:10px;
	font-weight:bold;
	color:<?=$valueColor;?>;
}
div#site div#header div#infos div.sep
{
	clear:both;
	width:100%;
	height:6px;
}
div#site div#content div#menu div.bouton span
{
	padding-top:3px;
	color:<?=$bgColor3;?>;
	font-size:14px;
	font-weight:bold;
	font-family:arial;
}



div#site div#content div#menu div.bouton:hover,
div#site div#content div#menu div.bouton:hover span
{
	cursor:pointer;
	color:#810503;
}

/** Positionnement, style et taille par défaut d'un tableau
*/
div.tlb_center, table.tbl_center{
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
	font-size:10pt;
	margin-right:auto;
	margin-left:auto;
	width:700px;
}



/** Style d'un titre de tableau (généralement la 1iere ligne du tableau)
*/
div.title, td.title {
	color:#FEFDFF;
	border:1px solid <?=$borderColor3;?>;
	background-color: <?=$bgColor3;?>;
	font-size:10px;
	font-weight:bold;
	text-align:right;
	background-image:url('img/title_bg.png');
	background-repeat:repeat-y;
	background-position:bottom right;
	padding:2px 10px 2px 10px;

	border-top-left-radius:12px;
	border-top-right-radius:12px;
	
	-moz-border-radius-topright:12px;
	-moz-border-radius-topleft:12px;
	
	-webkit-border-top-left-radius:12px;
	-webkit-border-top-right-radius:12px;
}

/** Style d'une cellule qui identifie une valeur
*/
div.name, td.name {
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
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
	background-color: <?=$bgColor1;?>;
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
div.panel, div.infobulle {
	/*position:relative;*/
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
	margin:0px auto 0px auto;
	padding:2px;
	padding-bottom:15px;
	border-radius:15px;
	
	-moz-border-radius:15px;
	
	-webkit-border-radius:15px;
}


div.plzwait_ombre{
	position:absolute;
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
	position:absolute;
	z-index:998;
	display:none;
	top:100px;
	width:100%;
}






/* ############################################################### */
/* ### FORMULAIRE */

input[type="button"], input[type="submit"],
input[type="text"], input[type="password"],
textarea, select, option
{
	border: solid 1px #B65F1A;
}

input[type="text"], input[type="password"],
textarea, select, option
{
	background-color: #0E0701;
}


input[type="button"], input[type="submit"] {
	font-family: Georgia, "MS Serif", "New York", serif;
	padding: 2px;
	font-size: 10px;
	color: #FFFFFF;
	background-color: #321B0B;
	
	border-top-left-radius:15px;
	border-bottom-right-radius:15px;
	border-top-right-radius:2px;
	border-bottom-left-radius:2px;

	-moz-border-radius-topleft:15px;
	-moz-border-radius-bottomright:15px;
	-moz-border-radius-topright:2px;
	-moz-border-radius-bottomleft:2px;
	
	-webkit-border-top-left-radius:15px;
	-webkit-border-bottom-right-radius:15px;
	-webkit-border-top-right-radius:2px;
	-webkit-border-bottom-left-radius:2px;
	
	margin:1px;
	width:140px;
}
input[type="button"]:hover, input[type="submit"]:hover {
	background-color: #492A15;
}


input[type="text"], input[type="password"] {
	font: bold 11px Georgia, "MS Serif", "New York", serif;
	color: #CCCCCC;
	padding: 2px;

	border-top-left-radius:15px;
	border-bottom-right-radius:15px;
	border-top-right-radius:2px;
	border-bottom-left-radius:2px;

	-moz-border-radius-topleft:15px;
	-moz-border-radius-bottomright:15px;
	-moz-border-radius-topright:2px;
	-moz-border-radius-bottomleft:2px;
	
	-webkit-border-top-left-radius:15px;
	-webkit-border-bottom-right-radius:15px;
	-webkit-border-top-right-radius:2px;
	-webkit-border-bottom-left-radius:2px;
	
}

input[type="text"]:hover, input[type="password"]:hover {
	color: #FFFFFF;
}

/*
input:disabled {
	color: #444;
	border-color: #444;
}
*/

textarea {
	font: 11px Georgia, "MS Serif", "New York", serif;
	color: #CCC;
	padding: 2px;
}
textarea:hover {
	color: #FFFFFF;
}

select {
	color: #CCC;
}

option { /* Elements de la select list */
	border-top:0px;
	padding:0px;
	margin:0px;
	padding-right:3px;
	color: #FFF;
}






/* ############################################################### */
/* ### STYLES applicables au TEXTE */

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

div#eXTReMe
{
	display:inline;
}

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
	padding-bottom:15px;
}
div.about_categorie{
	clear:both;
	font-weight:bold;
	text-decoration:underline;
	width:100%;
	margin-top:15px;
	text-align:center;
}

div.about_poste{
	float:left;
	width:185px;
	text-align:right;
}
div.about_nom{
	float:right;
	width:185px;
	margin-left:15px;
}

/* ### VISITOR - background */
div.background_contenu, div.regles_contenu{
	text-align:justify;
}

div.background_contenu p{
	margin:8px 30px;
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
	margin-top:50px;
	margin-bottom:10px;
	background-image:url('img/chapter_sep.png');
	background-position:center bottom;
	background-repeat:no-repeat;
	height:55px;
}

div.background_menu{
	padding:5px;
	text-align:center;
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

div.inscription_info div.content
{
	margin:10px;
}

div.inscription_notice{
	font-size:8pt;
	text-align:center;
}

div.inscription_name{
	width:200px;
	float:left;
	margin:1px 5px 0px 1px;
	padding:5px;
}
div.inscription_value{
	padding-top:10px;
}

p.inscription_note{
	margin-top:0px;
	padding-left:5px;
	font-weight:normal;
	font-size:8pt;
	color:#CCBB77;
}

div.inscription_condition{
	min-height:150px;
}


/* ### VISITOR - main */

div.main_zoneAll{
	width:750px;
	
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
	margin-right:30px;
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


div.pubMp{
	margin-top:20px;
	width:100%;
	text-align:center;
	position:relative;
	z-index:1;
}

/* ### MEMBER - abonnement */
table#abo_progress
{
	margin:0 auto;
}
table#abo_progress td.sep
{
	font-size:18pt;
	padding:0px 20px;
}

/* ### MEMBER - news */
div.news_panel1, div.news_panel2, div.news_panel3{
	width:400px;
	margin:50px auto 50px auto;
}

div.news_panel3
{
	padding-top:15px;
	text-align:center;
}

div.news_choix{
	float:right;
}



/* ### MEMBER - index */

div.member_index_topmenu{
	margin:auto 0px auto 0px;
	height:12px;
}


div.menuTab {
	position:relative;
	text-decoration:none;
}

div.menuTab div.infobulle {
	display: none;
	z-index:999;
}

.ie7 div.menuTab div.infobulle {
	margin-top:30px;
	margin-left:-80px;
}

div.menuTab:hover div.infobulle{
	position: absolute;
	display: block;
	
	text-align: left;
	text-decoration:none;
}

div.menuTab:hover div.infobulle a{
	display: block;
	margin:0px;
	padding-left:5px;
	
	font-size: 10pt;
	text-decoration: none;
	text-align:left;
}

div.menuTab:hover div.infobulle a:hover {
	padding-left:10px;
	
	font-style: oblique;
	color:#FFFFFF;
	text-decoration: none;
}


div.menuTab:hover div.infobulle a.menu_action_PPA { 
	margin-top:5px;
	
	font-weight:bold;
	text-align:center;
}
div.menuTab:hover div.infobulle a.menu_action_PPA:hover { 
	font-style: oblique;
	text-align:center;
	color:#FFFFFF;
	
}



div.member_index_topmenu_options{
	float:left;
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
	background-color: <?=$borderColor3;?>;
	
	border-top-left-radius:15px;
	border-top-right-radius:15px;

	-moz-border-radius-topleft:15px;
	-moz-border-radius-topright:15px;
	
	-webkit-border-top-left-radius:15px;
	-webkit-border-top-right-radius:15px;
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
	
	border-top-left-radius:5px;
	border-top-right-radius:5px;

	-moz-border-radius-topleft:15px;
	-moz-border-radius-topright:15px;
	
	-webkit-border-top-left-radius:5px;
	-webkit-border-top-right-radius:5px;
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
	background-color: <?=$bgColor1;?>;
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
	background-color: <?=$bgColor1;?>;
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
div.member_he_header_droite_pub a.infobulle div.infobulle{
	margin:5px 0px 0px -170px;
	width:250px;
}
.ie div.member_he_header_droite_pub a.infobulle div.infobulle{
	margin:20px 0px 0px -270px;
}



/* ### Infobulles du HE */
a.infobulle, span.infobulle {
	/*position:relative;*/
	text-decoration:none;
}

a.infobulle:hover, span.infobulle:hover {
	text-decoration:none;
	z-index:998; /* Sous les menu pj */
}

a.infobulle div.infobulle, span.infobulle div.infobulle {
	display: none;
	z-index:998; /* Sous les menu pj */
}

a.infobulle:hover div.infobulle, span.infobulle:hover div.infobulle{
	position: absolute;
	display: block;
	width:400px;
	overflow:hidden;
		
	text-align: left;
	text-decoration:none;
	font-size:8pt;
	font-weight: bold;
	
	color: #FFDDAA;
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
}

img.imgbg { /* INFO-BULLE, cadrage de l'image */
	border:1px solid <?=$borderColor1;?>;
	padding:3px;
	margin-right:5px;
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



div.inv_fiche { /*Anciennement #subcenter*/
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
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

div.member_action_sac_linkoff
{
	border-left:4px solid <?=$borderColor1;?>;
	cursor:pointer;
	padding-left:10px;
	font-size:14px;
}
div.member_action_sac_linkon{
	background-color:<?=$borderColor1;?>;
	color:#000;
	padding-left:10px;
	font-weight:bold;
	font-size:13px;
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
