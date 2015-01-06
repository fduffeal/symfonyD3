<?php

namespace Acme\EsBattleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Response;
use Acme\EsBattleBundle\DependencyInjection\Configuration as Configuration;

use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;

class BungieController extends Controller
{

    public function load(array $configs, ContainerBuilder $container)
    {
        // préparer votre variable $config

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        if (isset($config['enabled']) && $config['enabled']) {
            $loader->load('services.xml');
        }
    }

    public function indexAction(ContainerBuilder $container){
//        $conf = new Configuration();
//        $tree = $conf->getConfigTreeBuilder()->root('bungie');
//        var_dump($tree);die();


        // préparer votre variable $config

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

//        if (!isset($config['my_type'])) {
//            throw new \InvalidArgumentException('The "my_type" option must be set');
//        }
//
//        $container->setParameter('acme_hello.my_service_type', $config['my_type']);

        var_dump('test');die();
    }
    public function searchDestinyPlayerAction($membershipType,$displayName){

        $ch = curl_init("http://www.example.com/");
        $fp = fopen("example_homepage.txt", "w");

        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_exec($ch);
        curl_close($ch);
        fclose($fp);
    }
}
