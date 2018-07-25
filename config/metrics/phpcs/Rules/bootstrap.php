<?php
declare(strict_types=1);

namespace PHPCodeSniffer;

include __DIR__ . '/PS4AutoloaderClass.php';
$autoloader = new Psr4AutoloaderClass();
//$autoloader->addNamespace('Test', __DIR__ . '../../../../../src/Test'); Give example of namespace to add.
$autoloader->register();
