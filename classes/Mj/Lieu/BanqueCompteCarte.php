<?php
/** Gestion de l'interface d'affichage des historiques bancaire
*
* @package Mj
*/

class Mj_Lieu_BanqueCompteCarte
{
	public static function generatePage(&$tpl, &$session, &$account, &$mj)
	{
		
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT compte_banque, compte_compte'
				. ' FROM ' . DB_PREFIX . 'banque_comptes'
				. ' WHERE compte_id=:compteId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':compteId',	$_GET['id'],	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		$banque_no = $arr['compte_banque'];
		$compte_no = $arr['compte_compte'];
		
		$tpl->set('COMPTE', $banque_no . '-' . $compte_no);
		

		//Lister toutes les cartes
		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'banque_cartes'
				. ' WHERE	carte_banque=:banqueNo'
					. ' AND carte_compte=:compte;';
		$prep = $db->prepare($query);
		$prep->bindValue(':banqueNo',	$banque_no,		PDO::PARAM_INT);
		$prep->bindValue(':compte',		$compte_no,		PDO::PARAM_STR);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		if (count($arrAll)>0)
		{
			$i=0;
			$CARTES = array();
			foreach($arrAll as &$arr)
			{
				$CARTES[$i]['id']		= $arr['carte_id'];
				$CARTES[$i]['carte_no']	= $arr['carte_banque'] . '-' . $arr['carte_compte'] . '-' . $arr['carte_id'];
				$CARTES[$i]['nom']		= stripslashes($arr['carte_nom']);
				$CARTES[$i]['nip']		= $arr['carte_nip'];
				$CARTES[$i]['valide']	= (($arr['carte_valid']==0) ? 'Non': 'Oui');
				$i++;
			}
			$tpl->set('CARTES',$CARTES);
		}
		
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Mj/Lieu/BanqueCompteCarte.htm',__FILE__,__LINE__);
	}
}
