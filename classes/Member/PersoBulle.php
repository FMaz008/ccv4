<?php
/** Gestion des infobulles du HE
 *
 * @package Member
 * @subpackage HE
 */

class Member_PersoBulle
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		if(!isset($_POST['id']))
			return fctErrorMSG('Paramètre requis manquant.');

		$id = explode('_', $_POST['id']);

		if(count($id) != 3)
			return fctErrorMSG('Paramètre malformé.');
		
		$msgId = $id[1];
		$persoId = $id[2];

		//Todo: Valider si le perso qui demande à avoir une description
		//		possède le message en question.
		
		$query = 'SELECT d.description, p.imgurl, s.expiration, p.id'
				. ' FROM ' . DB_PREFIX . 'he_fromto as ft'
				. ' LEFT JOIN ' . DB_PREFIX . 'he_description as d ON (d.id = ft.id_description)'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p	ON (p.id = ft.persoid)'
				. ' LEFT JOIN ' . DB_PREFIX . 'session as s	ON (s.userId = p.userId)'
				. ' WHERE ft.msgid =:msgId'
					. ' AND ft.persoid =:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':msgId',		$msgId,		PDO::PARAM_INT);
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;

		if(empty($arr['description']))
			$arr['description'] = '<i> - Description non disponible - </i>';
		else
			$arr['description'] = BBCodes(self::getShortDescription($arr['description']));

		if(is_numeric($arr['id']))
			$arr['expiration'] = $arr['expiration']===NULL ? 'déconnecté' : 'récente'; //fctToGameTime($arr['expiration']-SESSION_TIMEOUT*60);
		else
			$arr['expiration']= 'Ce personnage à été supprimé.';

		
		if(!empty($arr['imgurl'])) //Une image du perso 
		{ 
			$imgurl = str_replace(' ','%20',$arr['imgurl']);
			if (substr($imgurl,0,4)!=='http')
				$imgurl = SITEPATH_ROOT . 'images/perso/' . $imgurl;
		
			$arr['imgurl']= $imgurl;
		}

		$tpl->set('PERSO_INFO', $arr);


		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . '/html/Member/he_persoBulle.htm');
		die();
	}




	
	/** Retourne le début d'une chaine fournie suivi d'un message dirigeant vers "jeter un coup d'oeil"
	 * @return string
	 */
    public static function getShortDescription($message)
    {
		if ( strlen($message) >= 300 )
		{
			$posToCut = strpos($message, ' ', 300);
			$message = substr($message, 0, $posToCut);
			$message .= '...<br /><br />[La suite dans "Jeter un coup d\'oeil"...]';
		}
		return $message;
	}
}
