<?php

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'pgsql',
    'host'      => 'localhost',
    'database'  => 'prueba_tecnica',
    'username'  => 'postgres',
    'password'  => 'Camilo1037',
    'charset'   => 'utf8',
    'collation' => 'utf8_general_ci',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();
