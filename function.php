<?php
/** 
 * Fonctions diverses d'usage courrant.
 * 
 * Ces fonctions ont une porté globale et peuvent être apellée n'importe où.
 *
 * @author Francois Mazerolle <admin@maz-concept.com>
 * @copyright Copyright (c) 2009, Francois Mazerolle
 * @version 1.1
 * @package CyberCity_2034
 */

/**
 * Génère une chaine de caractère aléatoire (A-Z0-9)
 *
 * @param int $length Longueur de la chaine à générer
 * @return string Format affichable
 */
function fctRandomString($length=32)
{
	$randstr='';
	srand((double)microtime()*1000000);
	//our array add all letters and numbers if you wish
	$chars = array ( 'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','0','1','2','3','4','5','6','7','8','9');
	for ($rand = 0; $rand < $length; $rand++) {
		$random = rand(0, count($chars) -1);
		$randstr .= $chars[$random];
	}
	return $randstr;
}


/** 
 * Remplace les BBCodes par du code HTML.
 *
 * Cette fonction s'occupe aussi de la conversion des \n en <br>
 * Cette fonction n'effectue aucun stripslashes() ou addslashes()
 * @param string $in chaine brute à BBCoder
 * @param bool $domnet Effectuer les remplacements de liens pour le système DomNet
 * @param bool $br Effectuer les remplacements des saut de lignes nl par br
 * @param bool $img Effectuer les remplacements des images pour les médias
 * @return string Code HTML
 */
function BBCodes($in, $domnet=false, $br=true, $img=false)
{

	$find = array(
		'/\[i\](.*?)\[\/i\]/is',
		'/\[u\](.*?)\[\/u\]/is',
		'/\[b\](.*?)\[\/b\]/is',
		'/\[s\](.*?)\[\/s\]/is',
		'/\[hr\]/is',
		'/\[size=(.*?)\](.*?)\[\/size\]/is',
		'/\[color=(.*?)\](.*?)\[\/color\]/is',
		'/\[HJ:(.*?)\\]/is'
	);

	$replace = array(
		'<i>${1}</i>',
		'<u>${1}</u>',
		'<strong>${1}</strong>',
		'<del>${1}</del>',
		'<hr />',
		'<font size="${1}px">${2}</font>',
		'<font color="${1}">${2}</font>',
		'<span class="txtStyle_heHj">[HJ:${1}]</span>'
	);
	
	if ($domnet){ //BBcodes uniquement disponibles sur Dom.net
		$find[count($find)] = '/\[link\](.*?)\[\/link\]/';
		$find[count($find)] = '/\[link=(.*?)\](.*?)\[\/link\]/';
		
		$replace[count($replace)] = '<a href="#" onclick="goTo(\'${1}\');return false;">${1}</a>';
		$replace[count($replace)] = '<a href="#" onclick="goTo(\'${1}\');return false;">${2}</a>';
	}
	
	if($img) //BBCodes uniquement disponibles sur les médias
	{
		$find[count($find)] = '/\[img\](.*?)\[\/img\]/';
		$replace[count($replace)] = '<img src="${1}">';
	}
	
	$find[count($find)] = '/\[(?!HJ)(.*?)\\]/is';
	$replace[count($replace)] = '<span class="txtStyle_heDesc">[${1}]</span>';

	if($br)
		return nl2br(preg_replace($find,$replace,$in));
	else
		return preg_replace($find,$replace,$in);
	
}



/**
 * Fonction qui affiche une erreur
 *
 * Exemple d'utilisation - afficher un avertissement
 * <code>
 * if(!isset($_POST['carteid']))
 * 	$erreur = fctErrorMSG('Aucune carte sécifiée.');
 * ....
 * return $erreur . $tpl->fetch(...);
 * </code>
 *
 * Exemple d'utilisation - Stoper la génération de la page actuelle, afficher une autre page (le formulaire précédent) avec une erreur en haut
 *<code>
 * if(!isset($_POST['carteid']))
 * {
 * 	return fctErrorMSG('Aucune carte sécifiée.',
 *						'?popup=1&m=Guichet2',
 *						array('carteid' => 12);
 * }
 * </code>
 *
 * @param string $msg Message d'erreur à afficher
 * @param string $redirectToPage Si non vide, le visiteur sera redirigé vers cette page ou l'erreur sera affichée
 * @param array $postParam Si non vide, ces paramêtres seront envoyé par POST à la page
 * @return string Retourne la source de la page générée par displayPage, avec l'erreur avant.
 */
function fctErrorMSG($msg, $redirectToPage=null, $postParam=null, $ajaxSubmit=true)
{
	
	if (isset($redirectToPage))
	{ 
		//Retourner l'erreur + la source d'une page
		$src = "<form id='ErrorForm' method='post' action='" . $redirectToPage . "'>";
		$src .= "<input type='hidden' name='erreur' value='" . htmlentities(utf8_decode($msg), ENT_QUOTES) . "' />";
		if (isset($postParam))
		{
			$params = array_keys($postParam);
			foreach($params as $param)
				$src .= "<input type='hidden' name='" . utf8_decode($param) . "' value='" . htmlentities(utf8_decode(strval($postParam[$param])), ENT_QUOTES) . "' />";
		}
		$src .= "</form>";
		$src .= "<script type='text/javascript'>";
		if($ajaxSubmit)
			$src .= "ajaxSubmitForm($('ErrorForm'));";
		else
			$src .= "$('ErrorForm').submit();";
		$src .= "</script>";
		return $src;
	}
	else
	{
		//Retourner uniquement l'erreur
		$src = "<table class=\"tbl_center\">";
		$src .= "<tr><td class=\"name\">Erreur</td></tr>";
		$src .= "<tr><td class=\"value\">" . $msg . "</td></tr>";
		$src .= "</table><br />";
		return $src;
	}
}


/**
 * Fonction qui traite (enregistre) les bugs détectés.
 *
 * Exemple d'utilisation - Stoper la génération de la page actuelle,
 * envoyer un rapport d'erreur.
 * <code>
 * if ($var==0)
 * {
 * 	fctBugReport('Une carte ne semble pas être lié à un compte (' . $item->getNoCarte() . ')',
 *					array($query, $item),
 *					__FILE__,
 *					__LINE__);
 * }
 * </code>
 *
 * @param string $msg Message d'erreur
 * @param array $arrVarDump Tableau contenant toutes les variables (objet, tableau, variable ou autre)
 * @param int $file Nom du fichier dans lequel l'erreur s'est produite (Généralement __FILE__)
 * @param int $line Numéro de la ligne dans laquelle l'erreur s'est produite (Généralement __LINE__)
 * @param int $function Nom de la fonction dans laquelle l'erreur s'est produite (Généralement __FUNCTION__)
 * @param int $class Nom de la classe dans laquelle l'erreur s'est produite (Généralement __CLASS__)
 * @param int $method Nom de la méthode dans laquelle l'erreur s'est produite (Généralement __METHOD__)
 * @param bool $mailReport Si l'erreur doit être envoyée par email (Défaut = false)
 * @param bool $visible Afficher une erreur sur la page (Défaut = true)
 * @param bool $die Arrêter la génération de la page (Défaut = true)
 */
function fctBugReport($msg, $arrVarDump, $file='', $line=0, $function='', $class=null, $method=null, $mailReport=false, $visible=true, $die=true)
{
	
	//S'il s'agit d'un tableau ou d'un objet, le convertir au format affichable
	if(is_array($arrVarDump) || is_object($arrVarDump))
		$arrVarDump = var_export($arrVarDump,true);
	
	if(strlen($arrVarDump)>500000)
		$arrVarDump = substr($arrVarDump, 0, 500000);
	
	//$file et $line ne peuvent être nul
	if(empty($file))
		$file = 'fichier inconnu';
    if(empty($line))
        $line = -1;
	
	//Insérer le rapport dans la DB
	if (DEBUG_MODE)
	{
		//En mode debug = afficher les erreurs
		if($visible)
		{
			echo "<font color=red>/!\\ ERREUR /!\\</font><font color=red> [" . $file . ", L." . $line . "]:<br />";
			echo $msg . "<br />";
			echo "<pre>" . stripslashes($arrVarDump) . "</pre></font>";
		}
	}
	else
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
			
		//En mode non-debug = logger les erreurs
		$query = 'INSERT INTO `cc_buglog`'
				. ' ( `id` , `date` , `ip` , `vardump` , `msg` , `file` , `line`)'
				. ' VALUES'
				. ' ("",UNIX_TIMESTAMP(), :ip, :dump, :msg, :file, :line);';
		$prep = $db->prepare($query);
		$prep->bindValue(':ip',		$_SERVER['REMOTE_ADDR'],	PDO::PARAM_STR);
		$prep->bindValue(':dump',	$arrVarDump,				PDO::PARAM_STR);
		$prep->bindValue(':msg',	$msg,						PDO::PARAM_STR);
		$prep->bindValue(':file',	$file,						PDO::PARAM_STR);
		$prep->bindValue(':line',	$line,						PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$prep->closeCursor();
		$prep = NULL;
				
		//Notifier l'utilisateur
		if($visible){
			echo "<table style='background-color:black;'>";
			echo "<tr><td style='color:red;'>Une erreur majeure s'est produite</td></tr>";
			echo "<tr><td style='color:red;'>";
			echo $msg . '<br />';
			echo 'Un rapport d\'erreur à été créé et soumis.';
			echo "</td></tr>";
			echo "</table><br />";
		}
		
		if($mailReport){
			//ToDo: Envoyer un email avec les infos
		}
	}
	
	//Arrêter de force la génération de la page actuelle
	if($die)
		die();
}


/**
 * Converti un temps au format numérique en format affichable.
 * 
 * Cette fonction effectue la conversion au niveau du décalage temporel du jeu.
 * 
 * @param int $mkTime Temps au format numérique (timestamp) à convertir
 * @param bool $inclureSecondes TRUE = inclu les secondes dans le temps affichable
 * @return string Format affichable ( Y/m/d H:i)
 */
function fctToGameTime($mkTime, $inclureSecondes=false)
{
	if($inclureSecondes)
		return (date('Y', $mkTime)+GAMETIME_DECAL) . date('/m/d H:i:s', $mkTime);
	else
		return (date('Y', $mkTime)+GAMETIME_DECAL) . date('/m/d H:i', $mkTime);
}


/**
 * Si les magics quotes sont activées, les supprimes
 *
 * Cette fonction prend un tableau ou une string
 *
 * @param mixed &$input Données à déprotéger
 * @return string retour
 */
function fctStripMagicQuote(&$input)
{
	if(!get_magic_quotes_gpc())
		return;
	
	if(is_array($input))
	{
		foreach(array_keys($input) as $key)
			if(is_array($input[$key]))
				fctStripMagicQuote($input[$key]);
			else
				$input[$key] = stripslashes($input[$key]);
	}
	else
	{
		$input = stripslashes($input);
	}
}

/**
 * Converti un entier ou un float en format monétaire.
 *
 * @param float $amount Montant à convertir
 * @param bool $showDevise Affiche la devise
 * @return string Format monétaire affichable
 */
function fctCreditFormat($amount, $showDevise=false, $virgule=0)
{
	if($showDevise)
		return sprintf("%.{$virgule}f",$amount) . '&nbsp;' . GAME_DEVISE;
	
	return sprintf("%.{$virgule}f",$amount);
}

/**
 * Retire tout les caractère non Alpha numérique (A-Za-z0-9) d'une string
 *
 * @param string $strInput Chaine en entrée
 * @return string Chaine épurée des caractères non alphanumérique
 */
function fctRemoveAllNonAlpha($strInput)
{
	return preg_replace('/[^a-zA-Z0-9_]/', '', $strInput);
}

/**
 * Change les <> pour «», ce qui désactive les script potentiellement dangereux.
 *
 * @param string $strInput Chaine en entrée
 * @return string Chaine modifiée
 */
function fctScriptProtect($strInput)
{
	return preg_replace(array('/</','/>/'), array('«','»'), $strInput);
}


/**
 * Ajouter une information au fichier de log global. Sert au débuggage.
 *
 * @param string $chaine à ajouter.
 */
function fctAddLog($txt, $file='', $line='')
{
	$handle = fopen('log.txt', 'a+');
	fwrite($handle, "\n" . date('Y-m-d H:i:s') . ' - ' . $txt . " [$file, L.$line]");
}

function fctFormatBytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
  
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
  
    $bytes /= pow(1024, $pow);
  
    return round($bytes, $precision) . ' ' . $units[$pow];
}
