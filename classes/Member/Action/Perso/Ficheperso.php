<?php
/** Gestion de l'interface des déplacement
*
* @package Member_Action
*/
class Member_Action_Perso_Ficheperso
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		
		//Générer la liste des compétances
		$arrComp=$perso->getComp();
		$comp = array();
		$i=0;
		foreach($arrComp as $compId)
		{
			$rawXp = $perso->getCompXp($compId);
			$rawLvl = $perso->getCompRawLevel($compId);
			$realLvl = $perso->getCompRealLevel($compId);
			$pCompXp = Member_Perso::convCompLevelToXp($rawLvl);
			$nCompXp = Member_Perso::convCompLevelToXp($rawLvl+1);
			
			$comp[$i]['code']		= $perso->getCompCode($compId);
			$comp[$i]['nom']		= $perso->getCompName($compId);
			$comp[$i]['totalXp']	= $nCompXp - $pCompXp;
			$comp[$i]['currentXp']	= $rawXp - $pCompXp;
			$comp[$i]['lvl']		= $rawLvl;
			$comp[$i]['reallvl']	= $realLvl;
			$comp[$i]['xp']			= $rawXp;
			$i++;
		}
		$tpl->set('COMP',$comp);
		
		
		
		//Générer la liste des statistiques
		$arrStat=$perso->getStat();
		$stat = array();
		$i=0;
		foreach($arrStat as $statId)
		{
			$statCode = $perso->getStatCode($statId);
			$rawXp = $perso->getStatRawXp($statId);
			$rawLvl = $perso->getStatRawLevel($statId);
			$statName = $perso->getStatName($statId);
			
			$prevLvlMinXp = Member_Perso::convStatLevelToXp($rawLvl-1);
			$prevLvlMaxXp = Member_Perso::convStatLevelToXp($rawLvl);
			$nextLvlMinXp = Member_Perso::convStatLevelToXp($rawLvl);
			$nextLvlMaxXp = Member_Perso::convStatLevelToXp($rawLvl+1);
			
			
			//Si le niveau est négatif
			if($rawLvl<0)
			{
				$prevBarMax = abs($prevLvlMinXp) - abs($prevLvlMaxXp);
				$prevBarPosition = abs($rawXp) - abs($prevLvlMaxXp)+1; 
				
				if($prevBarMax==0) //Si le niveau minimal est atteint
				{
					$nextBarMax = $prevBarPosition;
					$nextBarPosition = 1;
				}
				else
				{
					$nextBarMax = abs($prevLvlMinXp) - abs($prevLvlMaxXp);
					$nextBarPosition = $prevBarMax - $prevBarPosition;
				}
			}
			
			//Si le niveau est positif
			if($rawLvl>0)
			{
				
				$nextBarMax = abs($nextLvlMaxXp) - abs($nextLvlMinXp);
				$nextBarPosition = abs($rawXp) - abs($nextLvlMinXp)+1;
					
				if($nextBarMax==0) //Si le niveau maximal est atteint
				{
					$prevarMax = $nextBarPosition;
					$prevBarPosition = 1;
				}
				else
				{
					$prevBarMax = abs($nextLvlMaxXp) - abs($nextLvlMinXp);
					$prevBarPosition = $nextBarMax - $nextBarPosition;
				}
				
			}
			
			//Si le niveau est celui du centre
			if($rawLvl == 0)
			{
				$prevBarMax = abs($prevLvlMinXp) + abs($nextLvlMaxXp);
				$nextBarMax = abs($prevLvlMinXp) + abs($nextLvlMaxXp);
				
				$prevBarPosition = $rawXp + abs($prevLvlMinXp);
				$nextBarPosition = $prevBarMax - $prevBarPosition;
			}
			
			
			$stat[$i]['code']		= $statCode;
			$stat[$i]['nom']		= $statName;
			$stat[$i]['lvl']		= $rawLvl;
			$stat[$i]['xp']			= $rawXp;
			$stat[$i]['prevBarMax']	= $prevBarMax;
			$stat[$i]['nextBarMax']	= $nextBarMax;
			$stat[$i]['prevBarPos']	= $prevBarPosition;
			$stat[$i]['nextBarPos']	= $nextBarPosition;
			
			$i++;
		}
		//die(nl2br(var_export($stat, true)));
		$tpl->set('STAT',$stat);
		
		//Trouver les caractéristiques du perso
		$query = 'SELECT p.*, c.nom, t.nom as cat'
				. ' FROM ' . DB_PREFIX . 'perso_caract as p'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract as c ON (c.id = p.caractid)'
				. ' LEFT JOIN ' . DB_PREFIX . 'caract as t ON (t.id = c.catid)'
				. ' WHERE p.persoid=:persoId;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		foreach($arrAll as &$arr)
		{
			$arr['cat'] = stripslashes($arr['cat']);
			$arr['nom'] = stripslashes($arr['nom']);
			$arr['desc'] = stripslashes($arr['desc']);
		}
		
		$tpl->set('CARACT', $arrAll);
		
		//Retourner le template complété/rempli
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/Action/Perso/FichePerso.htm',__FILE__,__LINE__);
	}
}


