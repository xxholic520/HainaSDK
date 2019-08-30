<?php
require_once __DIR__ . '/vendor/autoload.php';

$app = new \Sammy1992\Haina\Application([
    'bucket_id'     => '',
    'bucket_secret' => '',
    'agent_id'      => ''
]);

var_dump($app->access_token->getToken());
