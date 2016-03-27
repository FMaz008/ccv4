<?php
/**
 * Classe Factory servant à facilité l'instanciation des items.
 */
class Member_ItemFactory
{

	/**
	 * Créer un item à partir de son ID.
	 *
	 * Exemple:
	 * <code>
	 * $item = Member_ItemFactory::createFromInvId(23);
	 * </code>
	 *
	 * @param int $id Id de l'item
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return Member_Item Instance de l'Item.
	 */
	public static function createFromInvId($id, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_id=:id'
					. ' LIMIT 1;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arr = $prep->fetch();
		$prep->closeCursor();
		$prep = NULL;
		
		if($arr===false)
		{
			throw new Exception('Ce item n\'existe pas (' . $id . ')');
			return;
		}
		
		return self::loadType($arr, $listDrogueConso);
	}


	/**
	 * Créer un tableau d'item à partir d'un Id de casier.
	 *
	 * Exemple:
	 * <code>
	 * $arrItem = Member_ItemFactory::createFromCasierId(23);
	 * </code>
	 *
	 * @param int $id Id du casier
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return array tableau d'items.
	 */
	public static function createFromCasierId($id, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_idcasier=:id'
					. ' ORDER BY db_type ASC, db_soustype ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		return self::loadArray($arrAll, $listDrogueConso);
	}



	/**
	 * Créer un tableau d'item à partir d'un Id d'item.
	 *
	 * Exemple:
	 * <code>
	 * $arrItem = Member_ItemFactory::createFromItemId(23);
	 * </code>
	 *
	 * @param int $id Id de l'item
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return array tableau d'items.
	 */
	public static function createFromItemId($id, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_itemid=:id'
					. ' ORDER BY db_type ASC, db_soustype ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		return self::loadArray($arrAll, $listDrogueConso);
	}


	/**
	 * Créer un tableau d'item à partir d'un Id de perso.
	 *
	 * Exemple:
	 * <code>
	 * $arrItem = Member_ItemFactory::createFromPersoId(23);
	 * </code>
	 *
	 * @param int $id Id du perso
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return array tableau d'items.
	 */
	public static function createFromPersoId($id, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_persoid=:id'
					. ' ORDER BY db_type ASC, db_soustype ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':id',			$id,	PDO::PARAM_INT);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		return self::loadArray($arrAll, $listDrogueConso);
	}



	/**
	 * Créer un tableau d'item à partir d'un nom technique de lieu.
	 *
	 * Exemple:
	 * <code>
	 * $arrItem = Member_ItemFactory::createFromNomTechLieu('cv.lieu');
	 * </code>
	 *
	 * @param string $nomTech Nom technique du lieu
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return array tableau d'items.
	 */
	public static function createFromNomTechLieu($nomTech, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_lieutech=:lieu'
					. ' ORDER BY db_type ASC, db_soustype ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieu',		$nomTech,	PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		return self::loadArray($arrAll, $listDrogueConso);
	}


	/**
	 * Créer un tableau d'item à partir d'un nom technique de boutique.
	 *
	 * Exemple:
	 * <code>
	 * $arrItem = Member_ItemFactory::createFromNomTechBoutique('cv.lieu');
	 * </code>
	 *
	 * @param string $nomTech Nom technique de la boutique
	 * @param bool $listDrogueConso Charger aussi les drogues actuellement consommées.
	 * @return array tableau d'items.
	 */
	public static function createFromNomTechBoutique($nomTech, $listDrogueConso=false)
	{
		$dbMgr = DbManager::getInstance(); //Instancier le gestionnaire
		$db = $dbMgr->getConn('game'); //Demander la connexion existante
		
		$query = 'SELECT *'
					. ' FROM ' . DB_PREFIX . 'item_inv'
					. ' LEFT JOIN ' . DB_PREFIX . 'item_db'
						. ' ON (db_id = inv_dbid)'
					. ' WHERE inv_boutiquelieutech=:lieu'
					. ' ORDER BY db_type ASC, db_soustype ASC;';
		$prep = $db->prepare($query);
		$prep->bindValue(':lieu',		$nomTech,	PDO::PARAM_STR);
		$prep->executePlus($db, __FILE__,__LINE__);
		$arrAll = $prep->fetchAll();
		$prep->closeCursor();
		$prep = NULL;

		
		return self::loadArray($arrAll, $listDrogueConso);
	}

	/* todo : créer un item */
	
	
	private static function loadArray($arrAll, $listDrogueConso=false)
	{
		if(count($arrAll)==0)
			return array();

		$arrItem = array();
		foreach($arrAll as &$arr)
		{
			$tmp = self::loadType($arr, $listDrogueConso);

			if($tmp) //Sera FALSE pour les items non-implantés
				$arrItem[] = $tmp;
			
		}
		
		return $arrItem;
	}
	
	private static function loadType(&$arr, $listDrogueConso=false)
	{
		switch($arr['db_type'])
		{
			case 'arme':
				switch($arr['db_soustype'])
				{
					case 'arme_feu':
						return new Member_ItemArmeFeu($arr);
					case 'arme_blanche':
						return new Member_ItemArmeBlanche($arr);
					case 'arme_lancee':
						return new Member_ItemArmeLancee($arr);
					case 'arme_lourde':
						//return new Member_ItemArmeLourde($arr);
						break;
					case 'arme_paralysante':
						return new Member_ItemArmeParalysante($arr);
					case 'arme_explosif':
						//return new Member_ItemArmeExplosive($arr);
						break;
				}
				break;
			case 'autre':
				return new Member_ItemAutre($arr);
			case 'badge':
				return new Member_ItemBadge($arr);
			case 'cartebanque':
				return new Member_ItemCartebanque($arr);
			case 'cartememoire':
				return new Member_ItemCartememoire($arr);
			case 'clef':
				return new Member_ItemClef($arr);
			case 'defense':
				switch($arr['db_soustype'])
				{
					case 'def_tete':
						return new Member_ItemDefenseTete($arr);
					case 'def_torse':
						return new Member_ItemDefenseTorse($arr);
					case 'def_bras':
						return new Member_ItemDefenseBras($arr);
					case 'def_main':
						return new Member_ItemDefenseMain($arr);
					case 'def_jambe':
						return new Member_ItemDefenseJambe($arr);
					case 'def_pied':
						return new Member_ItemDefensePied($arr);
				}
				break;
			case 'drogue':
				switch($arr['db_soustype'])
				{
					case 'drogue_drogue':
						if($listDrogueConso || ($arr['inv_qte']!=0 && $arr['inv_equip']!='1'))
							return new Member_ItemDrogueDrogue($arr);
						break;
					case 'drogue_substance':
						return new Member_ItemDrogueSubstance($arr);
					case 'drogue_antirejet':
						//return new Member_ItemDrogueAntirejet($arr);
						break;
					case 'drogue_autre':
						//return new Member_ItemDrogueAutre($arr);
						break;
				}
				break;
			case 'livre':
				return new Member_ItemLivre($arr);
			case 'munition':
				return new Member_ItemMunition($arr);
			case 'nourriture':
				return new Member_ItemNourriture($arr);
			case 'ordinateur':
				return new Member_ItemOrdinateur($arr);
			case 'sac':
				return new Member_ItemSac($arr);
			case 'talkiewalkie':
				return new Member_ItemRadio($arr);
			case 'telephone':
				return new Member_ItemTelephone($arr);
			case 'trousse':
				return new Member_ItemTrousse($arr);
			case 'media':
				return new Member_ItemMedia($arr);
		}

		return false;
	}
}
