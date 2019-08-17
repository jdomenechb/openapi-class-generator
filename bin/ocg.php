<?php

use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenApiTypeFactory;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$app = new Application();

$app->add(
    new GenerateCommand(new CebeOpenapiApiBuilder(new CebeOpenapiFileReader(), new CebeOpenApiTypeFactory()), new NetteApiCodeGenerator())
);

$app->run();