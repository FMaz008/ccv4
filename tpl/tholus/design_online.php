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
$bgColor1 = 	'#1D262F';
$borderColor2 = '#AA856E';
$bgColor2 = 	'#400F08';
$borderColor3 = '#9A4F16';
$bgColor3 = 	'#4E88B9';
$valueColor = 	'#EEEEDD';
$darkValueColor='#e9f3fb';
$shadowColor=	'#333';
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
	color: #ccc;
	margin:0px;
	font-family: Georgia, serif, "New York"; 
	font-size: 12pt;
	
	background-image:url('img/bg_tile.jpg');
	background-position:center 448px;
	background-repeat:repeat-y;
	
	background-color:#000;
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

.infobulle
{
	position: absolute;
	display:none;
	background-color:<?=$bgColor1;?>;
	padding:2px 2px 15px;
	border:1px solid <?=$borderColor1;?>;
	
	border-radius:15px;
	-moz-border-radius:15px;
	-webkit-border-radius:15px;

	z-index:100;
}
.hovermenu
{
	position: absolute;
	display:none;
	z-index:100;
}


.hovermenu .menu_action_panel
{
	border-top-left-radius:5px;
	-moz-border-radius-topleft:5px;
	-webkit-border-top-left-radius:5px;
}
.hovermenu .menu_action_panel .title
{
	border-top-left-radius:3px;
	-moz-border-radius-topleft:3px;
	-webkit-border-top-left-radius:3px;
}

h1 {
	font-size:15px;
	font-family: Trebuchet MS, Tahoma, Verdana, sans-serif;
	margin : 0;
	margin : 0px;
	padding : 0px;
	line-height : 25px;
	font-weight : 500;
}

/* ############################################################### */
/* ### LAYOUT */





div#bgHeader
{
	position:absolute;
	top:0;
	left:0px;
	z-index:-2;
	
	width:100%;
	height:448px;
	
	background-image:url('img/header_bg.jpg');
	background-position:top center;
	background-repeat:no-repeat;
	
}

div#bgHeader div#bgHeaderContainer
{
	position:absolute;
	
	width:1000px;
	height:165px;
	
	left:50%;
	margin-left:-500px;
}

div#siteLogoContainer
{
	position:absolute;
	width:1000px;
	left:50%;
	margin-left:-500px;
}
div#siteLogo
{
	position:absolute;
	z-index:95;
	width:400px;
	height:87px;
	
	top:92px;
	left:60px;
	
	background-image:url('img/logo.png');
	background-repeat:no-repeat;
}

div#bgFooter
{
	position:fixed;
	bottom:0px;
	left:0px;
	z-index:-1;

	width:100%;
	height:207px;

	background-image:url('img/footer_bg.png');
	background-position:top center;
	background-repeat:no-repeat;
}


div#bodySite
{
	position:absolute;
	width:100%;
	top:0px;
}

div#bodySite div#header
{
	position:relative;
	
	width:1000px;
	height:176px;
	
	margin:0 auto;
	margin-top:147px;

	background-image:url('img/header.png');
	background-position:top center;
	background-repeat:no-repeat;
}

div#bodySiteCentered
{
	/*padding-top:323px;*/ /* 176 + 147 */
	width:1000px;
	margin:0 auto;
}

div#bodySite div#header div#infos
{
	position:absolute;
	top:116px;
	left:110px;
	color:<?=$darkValueColor;?>;
	font-family:verdana;
	font-size:0.80em;
}
div#bodySite div#header div#foruminfos
{
	position:absolute;
	top:35px;
	left:680px;
	
	width:250px;
	height:65px;
	color:<?=$darkValueColor;?>;
}
div#bodySite div#header div#menu
{
	position:absolute;
	top:37px;
	left:77px;
	
	width:564px;
	height:50px;
}

div#bodySite div#header div#aboutUs
{
	position:absolute;
	top:118px;
	left:610px;
	
	width:100px;
	
	font-weight:bold;
}
div#aboutUs a{
	color:<?=$darkValueColor;?>;
}
div#content
{
	

	margin-left:46px;
	margin-right:32px;
	
	padding-left:50px;
	padding-right:50px;
	padding-bottom:50px;
	padding-top:1px; /* Nécéssaire afin d'éviter un gap si un élément enfant à une margin-top. */

	background-image:url('img/content_bg.jpg');
	background-repeat:repeat-y;
	
}

div#footerLeft
{
	position:fixed;
	bottom:0px;
	
	width:72px;
	height:143px;
	
	left:50%;
	margin-left:-500px;
	
	
	background-image:url('img/footer_left.png');
	background-position:top left;
	background-repeat:no-repeat;
}

div#footerRight
{
	position:fixed;
	bottom:0px;
	
	width:55px;
	height:132px;

	left:50%;
	margin-left:446px;
	
	
	background-image:url('img/footer_right.png');
	background-position:top left;
	background-repeat:no-repeat;
}
div#footerCenter
{
	position:fixed;
	z-index:98;
	bottom:0px;
	
	width:878px;
	height:36px;

	left:50%;
	margin-left:-431px;
	
	
	background-image:url('img/footer_center.png');
	background-position:top left;
	background-repeat:no-repeat;
}

/* ############################################################### */
/* ### ZONES et TABLEAUX */


div#revision
{
	position:absolute;
	color:<?=$darkValueColor;?>;
	left:390px;
}
.outline{
	text-shadow: -1px 0 <?=$shadowColor;?>, 0 1px <?=$shadowColor;?>, 1px 0 <?=$shadowColor;?>, 0 -1px <?=$shadowColor;?>;
}
div#site div#header div#modedebug span
{
	color:#F00;
	font-size:8px;
	font-weight:bold;
}



div#menu div.bouton
{
	float:left;
	display:block;
	width:139px;
	height:32px;
	
	margin:1px;
	cursor:pointer;
}
div#menu div.bouton span
{
	display:none;
}
div#menu div.bouton:hover div.hovermenu
{
	display:block;
	margin-top:32px;
}

div#menu div.boutonAccueil				{ background-image:url(img/bouton/btn_accueil_off.png); }
div#menu div.boutonAccueil:hover		{ background-image:url(img/bouton/btn_accueil_on.png); }
div#menu div.boutonJouer				{ background-image:url(img/bouton/btn_jouer_off.png); }
div#menu div.boutonJouer:hover			{ background-image:url(img/bouton/btn_jouer_on.png); }
div#menu div.boutonInscription			{ background-image:url(img/bouton/btn_inscription_off.png); }
div#menu div.boutonInscription:hover	{ background-image:url(img/bouton/btn_inscription_on.png); }
div#menu div.boutonBackground			{ background-image:url(img/bouton/btn_historiquel_off.png); }
div#menu div.boutonBackground:hover		{ background-image:url(img/bouton/btn_historiquel_on.png); }
div#menu div.boutonRegles				{ background-image:url(img/bouton/btn_reglements_off.png); }
div#menu div.boutonRegles:hover			{ background-image:url(img/bouton/btn_reglements_on.png); }
div#menu div.boutonVisite				{ background-image:url(img/bouton/btn_visite_off.png); }
div#menu div.boutonVisite:hover			{ background-image:url(img/bouton/btn_visite_on.png); }
div#menu div.boutonChat					{ background-image:url(img/bouton/btn_clavardage_off.png); }
div#menu div.boutonChat:hover			{ background-image:url(img/bouton/btn_clavardage_on.png); }
div#menu div.boutonForum				{ background-image:url(img/bouton/btn_forum_off.png); }
div#menu div.boutonForum:hover			{ background-image:url(img/bouton/btn_forum_on.png); }

div#menu div.boutonAction				{ background-image:url(img/bouton/btn_action_off.png); }
div#menu div.boutonAction:hover			{ background-image:url(img/bouton/btn_action_on.png); }
div#menu div.boutonCompte				{ background-image:url(img/bouton/btn_compte_off.png); }
div#menu div.boutonCompte:hover			{ background-image:url(img/bouton/btn_compte_on.png); }
div#menu div.boutonPerso				{ background-image:url(img/bouton/btn_personnage_off.png); }
div#menu div.boutonPerso:hover			{ background-image:url(img/bouton/btn_personnage_on.png); }
div#menu div.boutonFaq					{ background-image:url(img/bouton/btn_faq_off.png); }
div#menu div.boutonFaq:hover			{ background-image:url(img/bouton/btn_faq_on.png); }


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
	background-image:url('img/title_bg.jpg');
	background-repeat:repeat-y;
	background-position:top right;
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
div.panel {
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
	margin:0px auto 0px auto;
	padding:2px;
	padding-bottom:15px;
	border-radius:15px;
	
	-moz-border-radius:15px;
	
	-webkit-border-radius:15px;
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
.ie div.plzwait_ombre{
	filter:alpha(opacity=40); 
}
div.plzwait_content{
	position:fixed;
	z-index:998;
	display:none;
	top:45%;
	left:0px;
	width:100%;
}






/* ############################################################### */
/* ### FORMULAIRE */

input[type="button"], input[type="submit"],
input[type="text"], input[type="password"],
textarea, select, option
{
	border: solid 1px #FA9409;
}

input[type="text"], input[type="password"],
textarea, select, option
{
	background-color: #080E13;
}


input[type="button"], input[type="submit"] {
	font-family: Georgia, "MS Serif", "New York", serif;
	padding: 2px;
	font-size: 10px;
	color: #FFFFFF;
	background-color: #718A9E;
	
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
	width:300px;
	margin:10px auto;
	padding-top:10px;
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
	width:70px;
	margin-left:30px;
	text-align:right;
	padding-right:10px;
}

div.login_col2User, div.login_col2Pass{
	padding-right:30px;
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

/* ### VISITOR - background & FAQ*/
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

div.background_chapitre, div.faq_chapitre{
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

div.faq_contenu{
	text-align:justify;
}
div.background_menu, div.faq_menu{
	padding:5px;
	text-align:center;
}
span.faq_highligh
{
	color: <?=$valueColor;?>;
	font-weight:bold;
}
div.background_warning, div.faq_warning{
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

div.main_zoneLogo{
	margin-left:30px;
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
	text-decoration:none;
}



div.menu_action_panel
{
	border:1px solid <?=$borderColor1;?>;
	background-color: <?=$bgColor1;?>;
	font-size:12px;
	font-weight:bold;
	text-align:left;
	padding:2px 2px 15px;
	border-radius:15px;
	-moz-border-radius:15px;
	-webkit-border-radius:15px;
}
div.menu_action_panel a{
	display: block;
	margin:0px;
	padding-left:5px;
	
	font-size: 10pt;
	text-decoration: none;
	text-align:left;
}

div.menu_action_panel a:hover {
	padding-left:10px;
	
	font-style: oblique;
	color:#FFFFFF;
	text-decoration: none;
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
	margin-left:auto;margin-right:auto;
	padding:0px;
}
.ie .dropable_on{
	filter:alpha(opacity=50); 
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
