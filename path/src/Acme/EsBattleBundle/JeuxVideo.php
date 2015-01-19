<?php

namespace Acme\EsBattleBundle;


use MyProject\Proxies\__CG__\stdClass;
use Symfony\Component\Config\Definition\Exception\Exception;

class JeuxVideo
{

//    const URL = "http://www.jeuxvideo.com/forums/1-22853-149350-4444-0-1-0-ps4-joueurs-pour-raids-c-est-ici.htm";
    const URL = "http://www.jeuxvideo.com/forums/";

    const INDEX_MESSAGE_START = 7;

    const MAX_ESSAI = 2;

    public $tag = [];
    public $plateform = null;
    public $plateformBungie = null;

    public $ps3PlaformId = 1;
    public $ps4PlaformId = 2;
    public $xbox360PlaformId = 3;
    public $xboxOnePlaformId = 4;

    public $plateformBungieXboxLive = 1;
    public $plateformBungiePSN = 2;

    public $methode = null;

    public function __construct()
    {
        $this->tag = ['caveau','cropta','atheon','raid','assaut','30','26','nuit','noire','épique','epique','semaine'];
    }

    private function _curl($url){
        // initialisation de la session
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL,  $url);

        $headers = [];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        // exécution de la session
        $return = curl_exec($ch);

        // fermeture des ressources
        curl_close($ch);

        return $return;
    }

    private function _getLast($url){

        $aUrl = explode('-',$url);

        $return = $this->_curl(self::URL.$url);

        $dom = new \DOMDocument();
        @$dom->loadHTML($return);

        $xpath = new \DOMXpath($dom);

        $elements = $xpath->query('//*[@href]');

        $lastUrl = null;
        $lastPageNum = 0;
        foreach($elements as $element){


            $urlTemp = $element->getAttribute("href");
            $aUrlTemp = explode('/',$urlTemp);

            $urlTemp = $aUrlTemp[sizeof($aUrlTemp)-1];
            $aUrlTemp = explode('-',$urlTemp);

            if($aUrlTemp[0] == $aUrl[0] && $aUrlTemp[1] == $aUrl[1] && $aUrlTemp[2] == $aUrl[2]){
                $currentPageIndex = intval($aUrlTemp[3]);
                if($lastPageNum < $currentPageIndex){
                    $lastUrl = $urlTemp;
                    $lastPageNum = $currentPageIndex;
                }
            }
        }

        return $lastUrl;
    }

    public function getLastPage($url){

        return $this->_getLast($url);
    }


    private function _getPost($url){

        $return = $this->_curl(self::URL.$url);

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
                    $aPost[] = $this->_createObj($node,$element->getAttribute('id'));
                }
            }
        }

        return $aPost;
    }

    public function getPage($url){

        $this->findPlateform($url);
        return $this->_getPost($url);
    }

    private function _trimUltime($chaine){
        $chaine = trim($chaine);
        $chaine = str_replace("\t", " ", $chaine);
        $chaine = preg_replace("/[ ]{1,}/", " ", $chaine);
        return $chaine;
    }

    private function _findClass($message){
        $message = strtolower($message);
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

    private function _findGamerTagPSN($comment,$essai){
        $gamerTag = null;
        $testPSN = preg_match('/psn/i',$comment, $matches, PREG_OFFSET_CAPTURE);

        if($testPSN === 1){
//            var_dump($matches[0][1]);die();
            $subComment = substr($comment,$matches[0][1]);

            $subComment = str_replace(":"," ",$subComment);
            $subComment = $this->_trimUltime($subComment);
            $aSubComment = explode(' ',$subComment);

            $gamerTag = $aSubComment[$essai];
//            var_dump($gamerTag);die();
            $this->methode = 2;
        }

        return $gamerTag;
    }

    private function _findGamerTagXbox($comment,$essai){
        $gamerTag = null;
        $testId = preg_match('/gt/i',$comment, $matches, PREG_OFFSET_CAPTURE);

        if($testId === 1){
//            var_dump($matches[0][1]);die();
            $subComment = substr($comment,$matches[0][1]);

            $subComment = str_replace(":"," ",$subComment);
            $subComment = $this->_trimUltime($subComment);
            $aSubComment = explode(' ',$subComment);

            $sizeOfSubComment = sizeof($aSubComment);

            $aGamerTag = [];
            for($i = 1; $i < $essai+1 && $i<$sizeOfSubComment; $i++){
                $aGamerTag[] = $aSubComment[$i];
            }
            $gamerTag = implode(' ',$aGamerTag);
//            var_dump($gamerTag);die();
            $this->methode = 3;
        }

        return $gamerTag;
    }

    private function _findTagAtEnd($aComment,$aCommentSize,$essai){

        $aGamerTag = [];

        for($i = $essai; $i > 0; $i--){
            $aGamerTag[] =  $aComment[$aCommentSize-$i];
        }
        $gamerTag = implode(' ',$aGamerTag);

        $this->methode = 1;

        return $gamerTag;
    }

    private function _findGamerTag($aComment,$aCommentSize,$essai){

        $gamerTag = null;
        $comment = implode(' ',$aComment);

        if(($this->plateform === $this->ps4PlaformId || $this->plateform === $this->ps3PlaformId) && $gamerTag === null){
            $gamerTag = $this->_findGamerTagPSN($comment,$essai);
        }

        if(($this->plateform === $this->xbox360PlaformId || $this->plateform === $this->xboxOnePlaformId) && $gamerTag === null){
            $gamerTag = $this->_findGamerTagXbox($comment,$essai);
        }

        if($gamerTag === null){
            $gamerTag = $this->_findTagAtEnd($aComment,$aCommentSize,$essai);
        }



//        if($gamerTag !== null){
//            $gamerTagUrl = str_replace(' ','%20',$gamerTag);
//            $urlVerif = 'http://'.$_SERVER['HTTP_HOST'].'/bungie/characters/'.$this->plateform.'/'.$this->plateformBungie.'/'.$gamerTagUrl.'/null/null';
//            $result = $this->_curl($urlVerif);
//
//            if($result === '{"msg":"characters not found"}' && $essai < self::MAX_ESSAI){
//                $essai++;
//                $gamerTag = $this->_findGamerTag($aComment,$aCommentSize,$essai);
//            }
//        }

        return $gamerTag;
    }

    private function _getFrenchMonth($mois){
        $arrayMois = [
            'janvier' => 1,
            'février' => 2,
            'mars' => 3,
            'avril' => 4,
            'mai' => 5,
            'juin' => 6,
            'juillet' => 7,
            'août' => 8,
            'septembre' => 9,
            'octobre' => 10,
            'novembre' => 11,
            'décembre' => 12,
        ];

        return $arrayMois[strtolower($mois)];
    }

    private function _formatDate($aComment){

        $day = $aComment[2];
        $month = $this->_getFrenchMonth($aComment[3]);
        $year = $aComment[4];
        $aHeure = explode(':',$aComment[6]);

        return mktime($aHeure[0],$aHeure[1],$aHeure[2],$month,$day,$year);
    }

    public function findPlateform($url){
        $testPS3 = preg_match('/ps3/',$url, $matches, PREG_OFFSET_CAPTURE);
        if($testPS3 === 1){
            $this->plateform = $this->ps3PlaformId;
            $this->plateformBungie = $this->plateformBungiePSN;
        }
        $testPS4 = preg_match('/ps4/',$url, $matches, PREG_OFFSET_CAPTURE);
        if($testPS4 === 1){
            $this->plateform = $this->ps4PlaformId;
            $this->plateformBungie = $this->plateformBungiePSN;
        }
        $test360 = preg_match('/-360-/',$url, $matches, PREG_OFFSET_CAPTURE);
        if($test360 === 1){
            $this->plateform = $this->xbox360PlaformId;
            $this->plateformBungie = $this->plateformBungieXboxLive;
        }

        $testOne = preg_match('/one/',$url, $matches, PREG_OFFSET_CAPTURE);
        if($testOne === 1){
            $this->plateform = $this->xboxOnePlaformId;
            $this->plateformBungie = $this->plateformBungieXboxLive;
        }
    }

    private function _formatId($id){
        return str_replace('post_','',$id);
    }

    private function _createObj($node,$id){
        $comment = $this->_trimUltime($node->nodeValue);

        $aComment = preg_split('/[\s,]+/',$comment);
        $aCommentSize = sizeof($aComment);
        if($aCommentSize < 2){
            return null;
        }

        try{
            $obj = new \stdClass();
            $obj->author = $aComment[0];
            $obj->date = $this->_formatDate($aComment);
            $obj->heure = $aComment[6];
            $obj->message = $this->_formatMessage($aComment,$aCommentSize);
            $obj->class = $this->_findClass($obj->message);
            $obj->tags = $this->_findTag($aComment,$aCommentSize);
            $obj->gamerTag = $this->_findGamerTag($aComment,$aCommentSize,1);
            $obj->id = $this->_formatId($id);
        }catch (Exception $e ){

        }



        return $obj;
    }
}
