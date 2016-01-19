<?php
/**
 * Classe Factory servant à facilité l'instanciation des lieux.
 */
class Member_LieuFactory
{

	/**
	 * Créer un lieu à partir de son ID.
	 *
	 * Exemple:
	 * <code>
	 * $lieu = Member_LieuFactory::createFromId(23);
	 * </code>
	 *
	 * @param int $id Id du lieu
	 * @return Member_Lieu Instance du lieu.
	 */
	public static function createFromId($id)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante

		$query = 'SELECT *'
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE `id`=:id'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$id,	PDO::PARAM_INT);

		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr===false)
		{
			throw new Exception('Ce lieu n\'existe pas (' . $id . ')');
			return;
		}
		
		return new Member_Lieu($arr);
	}

	/**
	 * Créer un lieu à partir de son nom technique.
	 *
	 * Exemple:
	 * <code>
	 * $lieu = Member_LieuFactory::createFromNomTech('CV.lieu');
	 * </code>
	 *
	 * @param string $nomTech Nom technique du lieu
	 * @return Member_Lieu Instance du lieu.
	 */
	public static function createFromNomTech($nomTech)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT * '
				. ' FROM ' . DB_PREFIX . 'lieu'
				. ' WHERE `nom_technique`=:nomTech'
				. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':nomTech',	$nomTech,	PDO::PARAM_STR);

		$prep->execute($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr===false)
		{
			throw new Exception('Ce lieu n\'existe pas (' . $nomTech . ')');
			return;
		}
		
		return new Member_Lieu($arr);
	}
}
