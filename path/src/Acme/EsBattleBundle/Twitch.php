<?php

namespace Acme\EsBattleBundle;

/**
 * https://github.com/justintv/twitch-api
 */
class Twitch
{
	const TWITCH_URL = "https://api.twitch.tv";

	/**
	 * {@inheritDoc}
	 */
	public function __construct($apiversion, $doctrine)
	{
		$this->apiversion = $apiversion;
		$this->doctrine = $doctrine;
	}

	private function _curl($url)
	{
		// initialisation de la session
		$ch = curl_init();

		// configuration des options
		curl_setopt($ch, CURLOPT_URL, self::TWITCH_URL . $url);

		$headers = [];
		$headers[] = "x-api-version:" . $this->apiversion;
		$headers[] = "Client-ID::" . '1231531313';

		$timeout = 5;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

		curl_setopt($ch, CURLOPT_HEADER, 0);

		// exécution de la session
		$return = curl_exec($ch);

		// fermeture des ressources
		curl_close($ch);

		$result = json_decode($return);

		return $result;
	}


	public function getUser($username)
	{
		$url = '/kraken/search/channels?q='.$username;
		return $this->_curl($url);
	}
}
