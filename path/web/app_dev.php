<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;

umask(0000);

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/book/installation.html#configuration-and-setup for more information
//umask(0000);
// This check prevents access to debug front controllers that are deployed by accident to production servers.
// Feel free to remove this, extend it, or make something more sophisticated.
/*if (isset($_SERVER['HTTP_CLIENT_IP'])
    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
    || !in_array(@$_SERVER['REMOTE_ADDR'], array('92.103.149.238','83.113.164.241','127.0.0.1', 'fe80::1', '::1'))
) {
    header('HTTP/1.0 403 Forbidden');
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}*/

//if($_SERVER['SERVER_ADDR'] === '195.154.119.183' || $_SERVER['SERVER_ADDR'] === '62.210.252.220'){
////	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
////	header('Access-Control-Allow-Origin: http://www.esbattle.com');
//} else {
////	header('Access-Control-Allow-Origin: *');
//}

//
//header('Allow: POST, GET, HEAD, PUT, DELETE, OPTIONS');
//
//header('Access-Control-Allow-Headers: Origin, content-type, accept, User, Authorization, X-Requested-With');
//header('Access-Control-Allow-Credentials: true');
//header('Access-Control-Allow-Methods: POST, GET, HEAD, PUT, DELETE, OPTIONS');

//if($_SERVER['SERVER_NAME'] === 'lfg.esbattle.com'){
//    header('Access-Control-Allow-Origin: http://localhost:8000');
//} else {
//    header('Access-Control-Allow-Origin: http://www.esbattle.com');
//}

//if (isset($_SERVER['HTTP_CLIENT_IP'])
//    || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
//    || !in_array(@$_SERVER['REMOTE_ADDR'], array('195.154.119.183'))
//) {
//    header('HTTP/1.0 403 Forbidden');
//    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
//}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
Debug::enable();

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
// encapsule le AppKernel par défaut avec AppCache
$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
