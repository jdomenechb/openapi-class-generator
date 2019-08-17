<?php

use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\CebeOpenapiApiParser;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiServiceCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app->add(
    new GenerateCommand(new CebeOpenapiApiParser(new CebeOpenapiFileReader()), new NetteApiServiceCodeGenerator())
);

$app->run();