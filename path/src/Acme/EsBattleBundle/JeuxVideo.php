<?php

namespace Acme\EsBattleBundle;


use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;

class JeuxVideo
{

//    const URL = "http://www.jeuxvideo.com/forums/1-22853-149350-4444-0-1-0-ps4-joueurs-pour-raids-c-est-ici.htm";
    const URL = "http://www.jeuxvideo.com/forums/";

    const INDEX_MESSAGE_START = 7;

    public $tag = [];

    public function __construct()
    {
        $this->tag = ['caveau','cropta','atheon','raid','assaut','30','26'];
    }


    private function _curl($url){
        // initialisation de la session
        $ch = curl_init();

//        var_dump(self::URL.$url.'.html');die();
        // configuration des options
        curl_setopt($ch, CURLOPT_URL,  self::URL.$url.'.htm');

        $headers = [];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        // exécution de la session
        $return = curl_exec($ch);

        // fermeture des ressources
        curl_close($ch);

        $dom = new \DOMDocument();
        @$dom->loadHTML($return);

        $xpath = new \DOMXpath($dom);

        // example 1: for everything with an id
        $elements = $xpath->query("//*[@id]");

        // example 2: for node data in a selected id
//        $elements = $xpath->query("/html/body/div[@id='forum-main-col']");

        // example 3: same as above with wildcard
//        $elements = $xpath->query("*/div[@id='forum-main-col']");

//        var_dump($elements);

        $aPost = [];
        if (!is_null($elements)) {
            foreach ($elements as $element) {

                if(preg_match ('/^post/',$element->getAttribute('id')) !== 1){
                    continue;
                }

                $nodes = $element->childNodes;
                foreach ($nodes as $node) {
//                    if($node->nodeValue){
//                        continue;
//                    }
//                    echo "VALUE:".$node->nodeValue. "<br/>";
//
                    $aPost[] = $this->_createObj($node);
//                    var_dump($obj);


                }
            }
        }

        return $aPost;
    }

    public function getPage($url){
        return $this->_curl($url);
    }

    private function _trimUltime($chaine){
        $chaine = trim($chaine);
        $chaine = str_replace("\t", " ", $chaine);
        $chaine = preg_replace("/[ ]{1,}/", " ", $chaine);
        return $chaine;
    }

    private function _findClass($message){
        if(preg_match('/arca/',$message)){
            return 'warlock';
        }
        if(preg_match('/titan/',$message)){
            return 'titan';
        }
        if(preg_match('/hunter/',$message) || preg_match('/chasseur/',$message)){
            return 'hunter';
        }

        return null;
    }

    private function _formatMessage($aComment,$aCommentSize){
        $message = '';

        for($key = self::INDEX_MESSAGE_START; $key < $aCommentSize; $key++){
            $message .= ' '.$aComment[$key];
        }

        return $message;
    }

    private function _findTag($aComment,$aCommentSize){
        $aTag = [];
        for($key = self::INDEX_MESSAGE_START; $key < $aCommentSize; $key++){
            $tagLower = strtolower($aComment[$key]);
            if(in_array($tagLower,$this->tag) && !in_array($tagLower,$aTag)){
                $aTag[] = $tagLower;
            }
        }

        return implode(' ',$aTag);
    }

    private function _createObj($node){
        $comment = $this->_trimUltime($node->nodeValue);

        $aComment = preg_split('/[\s,]+/',$comment);
        $aCommentSize = sizeof($aComment);
        if($aCommentSize < 2){
            return null;
        }

        try{
            $obj = new \stdClass();
            $obj->author = $aComment[0];
            $obj->date = $aComment[2].' '.$aComment[3].' '.$aComment[4];
            $obj->heure = $aComment[6];
            $obj->message = $this->_formatMessage($aComment,$aCommentSize);
            $obj->class = $this->_findClass($obj->message);
            $obj->tags = $this->_findTag($aComment,$aCommentSize);
            $obj->gamerTag = $aComment[$aCommentSize-1];
        }catch (Exception $e ){

        }



        return $obj;
    }
}
