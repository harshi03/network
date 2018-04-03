<?php

require '../vendor/autoload.php';
require "../src/config/config.php";

$app = new \Slim\App([
	'settings' => [
 		'displayErrorDetails' => true, 
        'db' => [ 
           'driver' => 'mysql', 
           'host' => DB_HOST, 
           'database' => DB_NAME, 
           'username' => DB_USER, 
           'password' => DB_PASS, 
           'charset' => 'utf8', 
           'collation' => 'utf8_unicode_ci',
        ]
    ],
]);

//get all container items
$container = $app->getContainer();

//boot eloquent connection
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container['settings']['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

//pass the connection to global container (created in previous article)
$container['db'] = function ($container) use ($capsule){
   return $capsule;
};

require '../src/autoload.php';

$app->run();

?>
