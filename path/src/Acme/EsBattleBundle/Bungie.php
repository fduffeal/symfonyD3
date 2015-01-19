<?php

namespace Acme\EsBattleBundle;

use Acme\EsBattleBundle\Entity\UserGame;

/**
 * https://www.bungie.net/platform/destiny/help/
 */
class Bungie
{
	const BUNGIE_URL = "https://www.bungie.net";

	public $classMapping = array(0 => 'titan', 1 => 'hunter', 2 => 'warlock');

	public $player;
	public $characters;
	public $apikey;
	public $destinyGameId;
	public $doctrine;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($apikey, $destinyGameId, $doctrine)
	{
		$this->apikey = $apikey;
		$this->destinyGameId = $destinyGameId;
		$this->doctrine = $doctrine;
	}

	public function getDestinyGameId()
	{
		return $this->destinyGameId;
	}

	private function _curl($url)
	{
		// initialisation de la session
		$ch = curl_init();

		// configuration des options
		curl_setopt($ch, CURLOPT_URL, self::BUNGIE_URL . "/platform/destiny/" . $url);

		$headers = [];
		$headers[] = "X-API-Key:" . $this->apikey;

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch, CURLOPT_HEADER, 0);

		// exécution de la session
		$return = curl_exec($ch);

		// fermeture des ressources
		curl_close($ch);

		$result = json_decode($return);

		return $result;
	}

	public function getPlayer($membershipType, $displayName)
	{
		$searchDestinyPlayerCurl = $this->_SearchDestinyPlayer($membershipType, $displayName);

		if (!isset($searchDestinyPlayerCurl->Response) || !array_key_exists(0, $searchDestinyPlayerCurl->Response)) {
			return null;
		}

		$this->player = $searchDestinyPlayerCurl->Response[0];

		return $this->player;
	}

	public function getAccount($membershipType, $destinyMembershipId)
	{
		return $this->_Account($membershipType, $destinyMembershipId);
	}

	public function getCharacters($membershipType, $displayName)
	{

		$player = $this->getPlayer($membershipType, $displayName);
		if ($player === null) {
			return null;
		}
		$account = $this->_Account($membershipType, $player->membershipId);

		return $this->formatCharacters($account, $displayName);
	}

	public function formatCharacters($account, $displayName)
	{
		$clanName = '';

		if (isset($account->Response->data->clanName)) {
			$clanName = $account->Response->data->clanName;
		}
		$characters = $account->Response->data->characters;

		$this->characters = [];
		foreach ($characters as $key => $value) {

			$this->characters[] = array(
				'gamerTag' => $displayName,
				'level' => $value->characterLevel,
				'class' => $this->classMapping[$value->characterBase->classType],
				'clan' => $clanName,
				'backgroundPath' => self::BUNGIE_URL . $value->backgroundPath,
				'emblemPath' => self::BUNGIE_URL . $value->emblemPath,
				'characterId' => $value->characterBase->characterId
			);
		}

		return $this->characters;
	}

	public function getDoctrine()
	{
		return $this->doctrine;
	}

	/**
	 * @param $character
	 * @param \Acme\EsBattleBundle\Entity\User $user
	 */
	public function saveGameUserInfo($character, $user = null, $plaform = null, $game = null)
	{
		/**
		 * @var \Acme\EsBattleBundle\Entity\UserGame $userGame
		 */
		$userGame = $this->getDoctrine()
			->getRepository('AcmeEsBattleBundle:UserGame')
			->findOneBy(
				array('ext_id' => $character['characterId'], 'user' => $user)
			);

		if ($userGame === null) {
			$userGame = new UserGame();
		}

		$userGame->setGameProfilName($character['class'] . ' ' . $character['level']);
		$userGame->setGameUsername($character['gamerTag']);
		$userGame->setData1($character['class']);
		$userGame->setData2($character['level']);
		$userGame->setData3($character['clan']);
		$userGame->setData4($character['backgroundPath']);
		$userGame->setData5($character['emblemPath']);
		$userGame->setExtId($character['characterId']);
		$userGame->setUser($user);
		$userGame->setPlateform($plaform);
		$userGame->setGame($game);

		$em = $this->getDoctrine()->getManager();
		$em->persist($userGame);
		$em->flush();

		return $userGame;
	}

	/*
	 * Returns a list of Destiny memberships given a full Gamertag or PSN ID.
	 */
	private function _SearchDestinyPlayer($membershipType, $displayName)
	{

		$url = '/SearchDestinyPlayer/' . $membershipType . '/' . $displayName . '/';

		return $this->_curl($url);
	}

	private function _Account($membershipType, $destinyMembershipId)
	{
		$url = $membershipType . '/Account/' . $destinyMembershipId . '/';

		return $this->_curl($url);
	}

	public function getCharacter($membershipType,$destinyMembershipId,$characterId){
		return $this->_Character($membershipType,$destinyMembershipId,$characterId);
	}

	/**
	 * @desc Returns a character summary for the supplied membership.
	 * @param $membershipType
	 * @param $destinyMembershipId
	 * @param $characterId
	 * @return mixed
	 */
	private function _Character($membershipType,$destinyMembershipId,$characterId){
		$url = '/'.$membershipType.'/Account/'.$destinyMembershipId.'/Character/'.$characterId.'/';
		return $this->_curl($url);
	}

	public function getCharacterInventory($membershipType,$destinyMembershipId,$characterId){
		return $this->_CharacterInventory($membershipType,$destinyMembershipId,$characterId);
	}

	/**
	 * Retrieve the details of a Destiny Item.
	 * @param $membershipType
	 * @param $destinyMembershipId
	 * @param $characterId
	 * @param $itemInstanceId
	 */
	private function _CharacterInventory($membershipType,$destinyMembershipId,$characterId){
		$url = '/'.$membershipType.'/Account/'.$destinyMembershipId.'/Character/'.$characterId.'/Inventory/';
		return $this->_curl($url);
	}

	public function getItems(){
		return $this->_Items();
	}
	/**
	 * Gets a page list of Destiny items.
	 * @return mixed
	 */
	private function _Items(){
		$url = '/Explorer/Items/';
		return $this->_curl($url);
	}

	public function sortCharacters($characters){
		return $this->_array_sort($characters,'level',SORT_DESC);
	}

	private function _array_sort($array, $on, $order = SORT_ASC)
	{
		$new_array = array();
		$sortable_array = array();

		if (count($array) > 0) {
			foreach ($array as $k => $v) {
				if (is_array($v)) {
					foreach ($v as $k2 => $v2) {
						if ($k2 == $on) {
							$sortable_array[$k] = $v2;
						}
					}
				} else {
					$sortable_array[$k] = $v;
				}
			}

			switch ($order) {
				case SORT_ASC:
					asort($sortable_array);
					break;
				case SORT_DESC:
					arsort($sortable_array);
					break;
			}

			foreach ($sortable_array as $k => $v) {
				$new_array[$k] = $array[$k];
			}
		}

		return $new_array;
	}
}
