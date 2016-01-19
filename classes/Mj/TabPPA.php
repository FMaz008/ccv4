<?php
/**
 * Génère le contenu de l'onglet PPA
 * @package Mj
 * @subpackage Ajax
 */

class Mj_TabPPA
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
	
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		
		//Trouver les PPA associé au MJ
		$query = 'SELECT m.*, p.nom as perso, r.`date` as rep_date, r.mjid as rep_mj'
				. ' FROM ' . DB_PREFIX . 'ppa as m'
				. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = m.persoid)' 
				. ' LEFT JOIN ('
					. ' SELECT rep.*'
					. ' FROM ('
						. ' SELECT *'
						. ' FROM cc_ppa_reponses'
						. ' ORDER BY `date` DESC'
					. ' ) AS rep'
					. ' GROUP BY rep.sujetid'
				. ' ) as r ON (r.sujetid=m.id)'
				. ' WHERE	m.mjid=:mjId'
					. ' AND m.statut="ouvert"'
				. ' ORDER BY m.`date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mjId',			$mj->getId(),			PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$PPA_MJ = array();
		if(count($arrAll) != 0)
		{
			$i=0;
			foreach($arrAll as &$arr)
			{
				$PPA_MJ[$i] = $arr;
				$PPA_MJ[$i]['date'] = fctToGameTime($arr['date']);
				
				//NULL = pas de réponse
				//0 = réponse de joueur
				//>0 = id du MJ de la dernière réponse
				if($arr['rep_mj']!=NULL && $arr['rep_mj']>0)
				{
					//Une réponse du joueur est attendu
					$PPA_MJ[$i]['replyDate'] = 'Att. du joueur';
				}
				else
				{
					//Date de la réponse du joueur
					$PPA_MJ[$i]['replyDate'] = fctToGameTime($arr['rep_date']);
				}
				
				$i++;
			}
		}
		$tpl->set('PPA_MJ', $PPA_MJ);
		
		
		if($mj->accessPpa())
		{
			//Trouver les PPA en traitement rapide || traité trop lentement par un MJ
			$query = 'SELECT m.*, p.nom as perso, r.`date` as rep_date, r.mjid as rep_mj'
					. ' FROM ' . DB_PREFIX . 'ppa as m'
					. ' LEFT JOIN ' . DB_PREFIX . 'perso as p ON (p.id = m.persoid)'
					. ' LEFT JOIN ('
						. ' SELECT rep.*'
						. ' FROM ('
							. ' SELECT *'
							. ' FROM cc_ppa_reponses'
							. ' ORDER BY `date` DESC'
						. ' ) AS rep'
						. ' GROUP BY rep.sujetid'
					. ' ) as r ON (r.sujetid=m.id)'
					. ' WHERE'
						. ' ('
							. ' m.mjid=0' //	# Non-Attribué
							. ' OR ('
								. ' r.`date` <= :time' // # Expirée >3 jours
								. ' AND  r.mjid = 0' //	# Dernière réponse de la part du perso
							. ' )'
						. ' )'
						. ' AND m.statut="ouvert"'
					. ' ORDER BY m.`date` DESC;';
			$prep = $db->prepare($query);
			$prep->bindValue(':time',			time() - mktime(0,0,0,0,3),			PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$arrAll = $prep->fetchAll();
			$prep->closeCursor();
			$prep = NULL;
			
			$PPA = array();
			if(count($arrAll) != 0)
			{
				
				$i=0;
				foreach($arrAll as &$arr)
				{
					$PPA[$i] = $arr;
					$PPA[$i]['date'] = fctToGameTime($arr['date']);
					
					if($arr['mjid']==0)
					{
						//Le PPA n'est pas attribué
						$PPA[$i]['replyDate'] = 'Non-Attribué...';
					}
					elseif(!empty($arr['rep_date']))
					{
						//La réponse du joueur date de
						$PPA[$i]['replyDate'] = fctToGameTime($arr['rep_date']);
					}
					else
					{
						//Aucune réponse encore
						$PPA[$i]['replyDate'] = 'En Attente...';
					}
					$i++;
				}
			}
			$tpl->set('PPA', $PPA);
		}
		
		echo $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/tabPPA.htm');
		die();
	}
	
}

