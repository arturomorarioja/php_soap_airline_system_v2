<?php

declare(strict_types=1);

require __DIR__ . '/AirlineService.php';

$wsdl = __DIR__ . '/AirlineService.wsdl';

$options = [
    'trace'      => true,
    'exceptions' => true,
];

$server = new SoapServer($wsdl, $options);
$server->setClass(AirlineService::class);
$server->handle();
