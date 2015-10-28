<?php

namespace Acme\EsBattleBundle;

/**
 * https://github.com/justintv/twitch-api
 */
class Twitch
{
	const TWITCH_URL = "https://api.twitch.tv";
	const ClIENT_ID = 84813000;
	const REDIRECT_URL = "http://apidev.esbattle.com/app_dev.php/oauth/twitch";
	const SCOPE = "user_read";
	const TOKEN = "dzqdlnqzidé13123RAD3254VT3R3ZR3Zqdzdzlposlkv2432qjdblfkejvzlk453DSF2";
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

	/**
	 * Follows , Get a user's list of followed channels
	 * @param $username
	 * @return mixed
	 *
	 */
	public function getChannelListUser($username)
	{
		$url = '/kraken/users/'.$username.'/follows/channels';
		return $this->_curl($url);
	}

	public function searchStream($hote){
		$url = '/kraken/search/streams?q='+$hote;
		return $this->_curl($url);
	}

	public function getStream($hote){
		$url = '/kraken/streams/mllesundyrose';
		return $this->_curl($url);
	}
}
