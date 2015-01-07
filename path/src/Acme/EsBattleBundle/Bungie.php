<?php

namespace Acme\EsBattleBundle;

/**
 * https://www.bungie.net/platform/destiny/help/
 */
class Bungie
{
    private $apikey2;
    public $player;
    public $characters;
    /**
     * {@inheritDoc}
     */
    public function __construct($args)
    {
        $this->apikey = $args;
    }

    private function _curl($url){
        // initialisation de la session
        $ch = curl_init();

        // configuration des options
        curl_setopt($ch, CURLOPT_URL,  "https://www.bungie.net/platform/destiny/".$url);

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

        $this->player = $searchDestinyPlayerCurl->Response[0];

        return $this->player;
    }

    public function getCharacters($membershipType,$destinyMembershipId){
        $curl = $this->_Account($membershipType,$destinyMembershipId);

        $this->characters = $curl->Response->data->characters;

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
