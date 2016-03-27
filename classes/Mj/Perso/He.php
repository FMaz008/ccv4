<?php
/** Affichage d'un Historique des Évènements.
* 
*
* @package Mj
*/
class Mj_Perso_He
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		$maxMsg = 50;
		$nbrMsg = isset($_GET['hepage']) ? $maxMsg*($_GET['hepage']-1) : 0;
		
		//Charger le perso
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'perso'
				. ' WHERE id=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', 	$_GET['id'],	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$perso = new Member_Perso($arr);
		
		//Lister les messages du HE
		$heMsg = self::listMessages($nbrMsg, $maxMsg, $_GET['id']);
		$code='';
        while($msg=array_shift($heMsg))
        {
            $tpl2 = new Template($account);
			$tpl2->set('MSG',$msg);
            $tpl2->set('SKIN_VIRTUAL_PATH', SITE_VIRTUAL_PATH);
			$code .= $tpl2->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/He_item.htm');
            unset($tpl2);
		}
		$tpl->set('HE_MESSAGES',$code);
		
		
		//Trouver les sur la taille du HE
		$heSize = self::calculateHeSize((int)$_GET['id']);
		$tpl->set('HE_SIZE', $heSize);
		$tpl->set('HE_MSGPERPAGE', $maxMsg);
		$tpl->set('HE_PAGE', (isset($_GET['hepage']) ? (int)$_GET['hepage'] : 1));
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Perso/He.htm');
	}
	
	
	
	
	
	private static function listMessages($from, $nbr, $persoId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query= 'SELECT DISTINCT he.msg, he.date, he.id AS hid, sq.`show`, he.type, p.nom, ft.fromto, ft.persoid, d.description, p.sexe, p.imgurl, s.expiration'
			. ' FROM	('
				. ' SELECT msgid, `show`'
				. ' FROM cc_he_fromto'
				. ' WHERE persoid = :persoId'
				. ' ORDER BY `msgid` DESC'
				. ' LIMIT :from,:nbr'
			. ' ) as sq'
		. ' LEFT JOIN '.DB_PREFIX.'he AS he ON (he.id=sq.msgid)'
		. ' LEFT JOIN '.DB_PREFIX.'he_fromto AS ft ON ( ft.msgid = he.id )'
		. ' LEFT JOIN '.DB_PREFIX.'perso AS p ON ( p.id = ft.persoid )'
		. ' LEFT JOIN '.DB_PREFIX.'session AS s ON (s.userId = p.userId )'
		. ' LEFT JOIN '.DB_PREFIX.'he_description AS d ON (d.id = ft.id_description)'
		. ' ORDER BY he.`date` DESC, hid ASC, ft.fromto ASC , p.nom ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$persoId,	PDO::PARAM_INT);
		$prep->bindValue(':from',		$from,		PDO::PARAM_INT);
		$prep->bindValue(':nbr',		$nbr,		PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		$heMsg = array();
		$i=0;
		$lastMsgId = -1; //Id du dernier message
		$lastPersoId = -1; //Id du dernier perso From/To
		foreach($arrAll as &$arr)
		{
			if ($arr['hid']!=$lastMsgId) //Il s'agit d'un nouveau message
			{
				$heMsg[$i++] = new Mj_Perso_HeMessage($arr, $persoId);
				$lastMsgId = $arr['hid'];
				$lastPersoId = $arr['persoid'];
			}
			else
			{
				if ($lastPersoId != $arr['persoid'])
				{
					$heMsg[$i-1]->addFromTo($arr);
					$lastPersoId = $arr['persoid'];
				}
			}
		}
		return $heMsg;
	}
	
	private static function calculateHeSize($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT COUNT(msgid) as c'
				. ' FROM ' . DB_PREFIX . 'he_fromto'
				. ' WHERE persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
			
		return (int)$arr['c'];
	}
	
}



class Mj_Perso_HeMessage
{

	private $msg;
	
	private $from;
	
	private $to;
	
	private $date;
	
	private $id;
	
	private $style;
	
	private $show;
	
	
	function __construct(&$arr, $persoId)
	{
		$this->msg = BBCodes(stripslashes($arr['msg']));
		
		$this->date = mktime (
							date('H',$arr['date']),
							date('i',$arr['date']),
							date('s',$arr['date']),
							date('m',$arr['date']),
							date('d',$arr['date']),
							date('Y',$arr['date']) + GAMETIME_DECAL
						);
						
		$this->id = $arr['hid'];
		$this->style = (isset($arr['style'])) ? $arr['style'] : '';
		$this->show = $arr['show'];
		
		$this->from = array();
		$this->to = array();
		
		//Ajouter les FROM/TO
		$this->addFromTo($arr);
	}
	
	public function addFromTo(&$arr)
	{
		$i = count($this->{$arr['fromto']});
		
		
		
		$this->{$arr['fromto']}[$i]['id'] 				= $arr['persoid'];
		
		
		if ($arr['persoid']==0) //  Message du système
		{
			$this->{$arr['fromto']}[$i]['nom']			= 'Système';
			$this->{$arr['fromto']}[$i]['sexe']			= '';
			
		}
		else //Message d'un joueur
		{
				
			//Informations générales
			$this->{$arr['fromto']}[$i]['description']	= stripslashes($arr['description']);
			$this->{$arr['fromto']}[$i]['sexe'] 		= $arr['sexe'];
			$this->{$arr['fromto']}[$i]['expiration'] 	= $arr['expiration'];
			
			
			
			$this->{$arr['fromto']}[$i]['nom']		= stripslashes($arr['nom']);
			
			
			
			if(!empty($arr['imgurl']))
			{ 
				//Une image du perso 
				$imgurl = str_replace(' ','%20',$arr['imgurl']);
				if (substr($imgurl,0,4)!='http')
					$imgurl = SITEPATH_ROOT . 'images/perso/' . $imgurl;
				
				$this->{$arr['fromto']}[$i]['imgurl']	= $imgurl;
			}
		}
	}
	
	public function getId()			{ return $this->id; }
	public function getDate()		{ return $this->date; }
	public function getDateTxt()	{ return date('Y/m/d H:i:s' , $this->date); }
	public function getMsg()		{ return $this->msg; }
	public function getFrom()		{ return $this->from; }
	public function getTo()			{ return $this->to; }
	public function getStyle()		{ return $this->style; }
	public function getShow()		{ return ($this->show==1) ? true : false; }
}


