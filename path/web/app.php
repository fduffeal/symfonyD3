<?php

use Symfony\Component\ClassLoader\ApcClassLoader;
use Symfony\Component\HttpFoundation\Request;

umask(0000);

// Use APC for autoloading to improve performance.
// Change 'sf2' to a unique prefix in order to prevent cache key conflicts
// with other applications also using APC.
/*
$loader = new ApcClassLoader('sf2', $loader);
$loader->register(true);
*/

if($_SERVER['SERVER_ADDR'] === '195.154.119.183' || $_SERVER['SERVER_ADDR'] === '62.210.252.220'){
//	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
//	header('Access-Control-Allow-Origin: http://www.esbattle.com');
} else {
//	header('Access-Control-Allow-Origin: http://localhost:8000');
}

//header('Access-Control-Allow-Methods',"POST, GET, PUT, DELETE, OPTIONS");

//if($_SERVER['SERVER_NAME'] === 'lfg.esbattle.com'){
//	header('Access-Control-Allow-Origin: http://localhost:8000');
//} else {
//	header('Access-Control-Allow-Origin: http://www.esbattle.com');
//}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';
require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
// encapsule le AppKernel par défaut avec AppCache
$kernel = new AppCache($kernel);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
