<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = new \Sammy1992\Haina\Application([
    'bucket_id'     => 'hnzDZ2LVUMZzVQzlMD',
    'bucket_secret' => '3NBEDKJv8Qb1kKslmNz6NWlXnd4wAayG',
    'agent_id'      => 'agyB1Lz1HngDqPN53Z'
]);

var_dump($app->access_token->getToken());
