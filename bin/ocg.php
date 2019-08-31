<?php

use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();
$app->add(new GenerateCommand());
$app->run();