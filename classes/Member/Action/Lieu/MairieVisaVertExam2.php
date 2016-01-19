<?php
/** Correction de l'examen du VISA VERT
*
* @package Member_Action
*/
class Member_Action_Lieu_MairieVisaVertExam2
{
	public static function generatePage(&$tpl, &$session, &$account, &$perso)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$errorUrl = '?popup=1&amp;m=Action_Lieu_MairieVisaVert';
		

		//Vérifier si le perso possède déjà son visa vert
		if($perso->getVisaPerm()=="1")
			return fctErrorMSG('Vous avez déjà votre Visa Vert.', $errorUrl);

		
		//Vérifier l'état du perso
		if(!$perso->isNormal())
			return fctErrorMSG('Votre n\'êtes pas en état d\'effectuer cette action.', $errorUrl);
		
		
		//Valider si un # d'examen (tag) est recu (# servant à la validation anti-précédent)
		if(!isset($_POST['no_exam']) || !is_numeric($_POST['no_exam']) || strlen($_POST['no_exam'])!=10)
			return fctErrorMSG('Merci de passer par le formulaire (2).', $errorUrl);
		
		
		//Valider si les réponses aux questions sont recus
		if(!isset($_POST['Q']))
			return fctErrorMSG('Merci de passer par le formulaire (3).', $errorUrl);
		
		
		
		//VÉRIFICATION ANTI-PRÉCÉDENT
		//Vérifier le tag de l'examen (pour éviter de refaire la même copie après la correction)
		$query = 'SELECT `visa_perm`'
				. ' FROM `' . DB_PREFIX . 'perso`'
				. ' WHERE `id`=:persoId'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':persoId', $perso->getId(),		PDO::PARAM_INT);
		$prep->execute($db, __FILE__, __LINE__);
		$arr = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		
		$no = $arr['visa_perm'];
		
		
		//Valider si on tente de repasser un examen
		if($no == $_POST['no_exam'])
			return fctErrorMSG('Vous devez reprendre l\'examen et repayer (Vous avez fait "précédent" après la correction).', $errorUrl);
		
		
		//Mettre a jour le #
		if($no!=1)
		{ //Ne pas supprimer un visa existant
			$query = 'UPDATE `' . DB_PREFIX . 'perso`'
					. ' SET `visa_perm`=:noExam'
					. ' WHERE `id`=:persoId;';
			$prep = $db->prepare($query);
			$prep->bindValue(':noExam', 	$_POST['no_exam'],	PDO::PARAM_STR);
			$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
		
		}
		
		
		
		
	
		
		
		//Valider si les questions sont au format tableau ( name="Q[]" )
		if(!is_array($_POST['Q']))
			return fctErrorMSG('Merci de passer par le formulaire (4).', $errorUrl);


		
		
		//Trouver les bonnes réponses
		$query = 'SELECT `id`, `reponse_tech`, `section`'
				. ' FROM `' . DB_PREFIX . 'mairie_question`'
				. ' WHERE `id` IN (';

		$strQ = array();
		foreach($_POST['Q'] as &$val)
			$strQ[] = '?';

		$query .= implode(',', $strQ) . ');';
		$prep = $db->prepare($query);
		
		$i=1;
		foreach($_POST['Q'] as &$val)
			$prep->bindValue($i++, 	$val, 	PDO::PARAM_INT);
		
		$prep->execute($db, __FILE__, __LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		//Valider si le compte des question soumises et des réponses recu correspond
		if(count($arrAll) == 0 || count($arrAll) != count($_POST['Q']))
			return fctErrorMSG('Le compte des réponses dans la base de données ne correspond pas au nombre de réponses soumises.', $errorUrl);
		
		
		//Passer chaque réponse et la valider.
		
		$reponses = array(); //Compiler les résultats dans cette grille de correction
		foreach($arrAll as &$arr)
		{
		
			//Valider si l'ID de la réponse de la base de données correspond à une question répondue
			if(!isset($_POST['Q' . $arr['id']]))
				return fctErrorMSG('Merci de passer par le formulaire (5).', $errorUrl);
				
			
			//Réponse tentée par le joueur
			$essaie = $_POST['Q' . $arr['id']];
			
			
			//Si la section du questionnaire n'est pas défini dans la grille de correction, la créer
			if(!isset($reponses[$arr['section']]))
				$reponses[$arr['section']] = array(
												'section' => $arr['section'],
												'total' => 0,
												'good' => 0
												);
			
			//Si la réponse est bonne, incrémenter le total des bonnes réponses
			if($essaie==$arr['reponse_tech'])
				$reponses[$arr['section']]['good']++;
				
			//Incrémenter le total des réponses
			$reponses[$arr['section']]['total']++;
		}
		
		
		//Valider si réussite ou échec
		$REUSSITE = true;
		$SECTIONS = array();
		$keys = array_keys($reponses);
		for($i=0;$i<count($keys);$i++)
		{
			$score = round($reponses[$keys[$i]]['good']*100/$reponses[$keys[$i]]['total']);
			
			$SECTIONS[$i] = array(
								'section' => $reponses[$keys[$i]]['section'],
								'score'	=> $score,
								'statut' => (($score >= 50) ? true : false)
							);
			
			if ($SECTIONS[$i]['statut']==false)
				$REUSSITE = false;
		}
		
		$scoreTotal = 0;
		$scoreSection = array();
		for($i=0;$i<count($SECTIONS);$i++)
		{
			$scoreTotal += $SECTIONS[$i]['score'];
			$scoreSection[] = $SECTIONS[$i]['score'] . '%';
		}
		$scoreTotal = round($scoreTotal/count($SECTIONS));
		
		if($scoreTotal < 60)
			$REUSSITE = false;
		
		
		
		//Si réussite, appliquer les privilèges
		if($REUSSITE)
		{
			$query = 'UPDATE `' . DB_PREFIX . 'perso`'
					. ' SET `visa_perm`=1'
					. ' WHERE `id`=:persoId'
					. ' LIMIT 1;';
			$prep = $db->prepare($query);
			$prep->bindValue(':persoId',	$perso->getId(),	PDO::PARAM_INT);
			$prep->execute($db, __FILE__, __LINE__);
			$prep->closeCursor();
			$prep = NULL;
			
			
			$perso->changeCash('+', 20000);
			$perso->setCash();
			
			Member_He::add($perso->getId(), 0, 'mairie', "Vous obtenez votre Visa Vert avec un résultat à l'examen de $scoreTotal% (" . implode(', ', $scoreSection) . "). De plus, le préposé vous remet 20 000" . GAME_DEVISE . ".");
		}
		else
		{
			Member_He::add($perso->getId(), 0, 'mairie', "Vous échouez votre Visa Vert avec un résultat à l'examen de $scoreTotal% (" . implode(', ', $scoreSection) . ").");
		}
		
		
		
		//Rafraichir le HE
		return $tpl->fetch($account->getSkinRemotePhysicalPath() . 'html/Member/herefresh.htm',__FILE__,__LINE__);
	}
}
