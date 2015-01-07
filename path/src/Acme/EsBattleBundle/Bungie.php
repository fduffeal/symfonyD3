<?php

namespace Acme\EsBattleBundle;

/**
 * https://www.bungie.net/platform/destiny/help/
 */
class Bungie
{
    private $apikey2;
    /**
     * {@inheritDoc}
     */
    public function __construct($args)
    {
        $this->apikey = $args;
    }

    /*
     * Returns a list of Destiny memberships given a full Gamertag or PSN ID.
     */
    public function SearchDestinyPlayer($membershipType,$displayName){

        $url = '/SearchDestinyPlayer/'.$membershipType.'/'.$displayName.'/';
        // initialisation de la session
        $ch = curl_init();

        // configuration des options
        curl_setopt($ch, CURLOPT_URL,  "https://www.bungie.net/platform/destiny/".$url);

        $headers = [];
        $headers[] = "X-API-Key:".$this->apikey;

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        // exécution de la session
        $return = curl_exec($ch);

        // fermeture des ressources
        curl_close($ch);

        return $return;
    }
}
