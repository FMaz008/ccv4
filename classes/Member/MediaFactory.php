<?php
/**
 * Classe factory pour gérer les médias
 */
 class Member_MediaFactory
 {
	/*
	 * Fonction qui retourne la liste des couples (mediaType, channelId) où des émissions sont présentes.
	 * S'il n'existe aucune émission, la liste est vide.
	 *
	 * @return array liste des couples
	 */
	public static function getExistMediaList()
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$query = 'SELECT DISTINCT `mediaType`, `canalId`'
				. ' FROM `' . DB_PREFIX . 'media`;';
		$prep = $db->prepare($query);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		$i = 0;
		while($i < count($arr))
		{
			if($arr[$i]['mediaType'] == 'tele')
				$arr[$i]['mediaTypeAff'] = 'Télé';
			else
				$arr[$i]['mediaTypeAff'] = 'Radio';
			$i++;
		}
		
		return $arr;
	}
	
	/*
	 * Retourne la liste des messages appartenant au couple (mediaType, canalId) passé en paramêtre.
	 * 
	 * @param $mediaType type du média
	 * @param $canalId canal du média
	 * 
	 * @return array liste des messages
	 */
	public static function getAllMessage($mediaType, $canalId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$titreEmission = Member_MediaFactory::getAllEmissionName($mediaType, $canalId);
		$result = array();
		
		$query = 'SELECT *'
				. ' FROM `' . DB_PREFIX . 'media`'
				. ' WHERE `mediaType` = :mediaType'
				. ' AND `canalId` = :canalId'
				. ' AND `titre` = :titre'
				. ' ORDER BY `date` DESC;';	
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType', $mediaType, PDO::PARAM_STR);
		$prep->bindValue(':canalId', $canalId, PDO::PARAM_INT);	
		
		foreach($titreEmission as $titre)
		{
			$prep->bindValue(':titre', $titre['titre'], PDO::PARAM_STR);
			$prep->execute($db, __FILE__,__LINE__);
			$arrAll = $prep->fetchAll();
		
			foreach($arrAll as $arr)
			{
				$arr['date'] = fctToGameTime($arr['date']);
				$arr['message'] = BBCodes($arr['message'], false, true, true);
				$result[] = $arr;
			}
		}
		
		$prep->closeCursor();
		$prep = NULL;
		
		return $result;
	}
	
	public static function getAllEmissionName($mediaType, $canalId)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$query = 'SELECT DISTINCT `titre`'
				. ' FROM `' . DB_PREFIX . 'media`'
				. ' WHERE `mediaType` = :mediaType'
				. ' AND `canalId` = :canalId'
				. ' ORDER BY `date` DESC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType', $mediaType, PDO::PARAM_STR);
		$prep->bindValue(':canalId', $canalId, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;
		
		return $arr;
	}
	
	/*
	 * Ajoute un message dans le média déterminé par le tuple ($mediaType, $canalId, $emissionNom).
	 * 
	 * @param $mediaType type du média
	 * @param $canalId canal du média
	 * @param $emissionNom nom de l'émission à laquelle le message est attaché
	 * @param $message message à ajouter
	 */
	public static function addMessage($mediaType, $canalId, $emissionNom, $message)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$timestamp = time();
		
		$query = 'INSERT INTO `' . DB_PREFIX . 'media`'
				. ' (`mediaType`, `canalId`, `date`, `titre`, `message`)'
				. ' VALUES '
				. ' (:mediaType, :canalId, :date, :titre, :msg);';	
		$prep = $db->prepare($query);
		$prep->bindValue(':mediaType',	$mediaType, PDO::PARAM_STR);
		$prep->bindValue(':canalId',	$canalId, PDO::PARAM_INT);
		$prep->bindValue(':titre',	$emissionNom, PDO::PARAM_STR);
		$prep->bindValue(':msg',	$message, PDO::PARAM_STR);
		$prep->bindValue(':date',	$timestamp, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
	
	/*
	 * Supprimer un message des médias.
	 *
	 * @param $id id du message à supprimé
	 */
	public static function deleteMessage($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$query = 'DELETE'
				. ' FROM `' . DB_PREFIX . 'media`'
				. ' WHERE `id` = :id;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id', $id, PDO::PARAM_INT);
		$prep->execute($db, __FILE__,__LINE__);
		$prep->closeCursor();
		$prep = NULL;
	}
 }