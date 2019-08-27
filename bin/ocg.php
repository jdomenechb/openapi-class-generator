<?php

use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestBodyFormatCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteObjectSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$fileWriter = new ClassFileWriter('output');

$app = new Application();

$app->add(
    new GenerateCommand(
        new CebeOpenapiApiBuilder(new CebeOpenapiFileReader(), new CebeOpenapiSchemaFactory()),
        new NetteApiCodeGenerator($fileWriter, new NettePathCodeGenerator(new NetteRequestBodyFormatCodeGenerator(new NetteObjectSchemaCodeGenerator($fileWriter))))
    )
);

$app->run();