<?php

namespace Acme\EsBattleBundle;

/**
 * https://www.bungie.net/platform/destiny/help/
 */
class Bungie
{
    const BUNGIE_URL = "https://www.bungie.net";

    public $classMapping = array(0 => 'titan',1 => 'hunter',2 => 'warlock');

    public $player;
    public $characters;
    public $apikey;
    public $destinyGameId;
    /**
     * {@inheritDoc}
     */
    public function __construct($apikey,$destinyGameId)
    {
        $this->apikey = $apikey;
        $this->destinyGameId = $destinyGameId;
    }

    public function getDestinyGameId(){
        return $this->destinyGameId;
    }

    private function _curl($url){
        // initialisation de la session
        $ch = curl_init();

        // configuration des options
        curl_setopt($ch, CURLOPT_URL,  self::BUNGIE_URL."/platform/destiny/".$url);

        $headers = [];
        $headers[] = "X-API-Key:".$this->apikey;

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

    public function getPlayer($membershipType,$displayName){
        $searchDestinyPlayerCurl = $this->_SearchDestinyPlayer($membershipType,$displayName);

        if (!isset($searchDestinyPlayerCurl->Response) || !array_key_exists(0,$searchDestinyPlayerCurl->Response)){
            return null;
        }

        $this->player = $searchDestinyPlayerCurl->Response[0];

        return $this->player;
    }

    public function getAccount($membershipType,$destinyMembershipId){
        return $this->_Account($membershipType,$destinyMembershipId);
    }

    public function getCharacters($membershipType,$displayName){

        $player = $this->getPlayer($membershipType,$displayName);
        if($player === null){
            return null;
        }
        $account = $this->_Account($membershipType,$player->membershipId);

        $characters = $account->Response->data->characters;
        $clanName = $account->Response->data->clanName;

        $this->characters = [];
        foreach($characters as $key => $value){

            $this->characters[] = array(
                'gamerTag' => $displayName,
                'level' => $value->characterLevel,
                'class' => $this->classMapping[$value->characterBase->classType],
                'clan' => $clanName,
                'backgroundPath' => self::BUNGIE_URL.$value->backgroundPath,
                'emblemPath' => self::BUNGIE_URL.$value->emblemPath,
                'characterId' => $value->characterBase->characterId
            );
        }

        return $this->characters;
    }

    /*
     * Returns a list of Destiny memberships given a full Gamertag or PSN ID.
     */
    private function _SearchDestinyPlayer($membershipType,$displayName){

        $url = '/SearchDestinyPlayer/'.$membershipType.'/'.$displayName.'/';

        return $this->_curl($url);
    }

    private function _Account($membershipType,$destinyMembershipId){
        $url = $membershipType.'/Account/'.$destinyMembershipId.'/';

        return $this->_curl($url);
    }
}
