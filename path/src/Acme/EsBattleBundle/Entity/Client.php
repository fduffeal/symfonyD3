<?php
// src/Acme/EsBattleBundle/Entity/Client.php

namespace Acme\EsBattleBundle\Entity;

use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
* @ORM\Entity
*/
class Client extends BaseClient
{
/**
* @ORM\Id
* @ORM\Column(type="integer")
* @ORM\GeneratedValue(strategy="AUTO")
*/
protected $id;

public function __construct()
{
parent::__construct();
}
}