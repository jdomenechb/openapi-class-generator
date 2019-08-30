<?php

use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteAbstractSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteGuzzleBodyCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathParameterCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestBodyFormatCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteObjectSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/../vendor/autoload.php';

$fileWriter = new ClassFileWriter('output');
$abstractSchemaCodeGenerator = new NetteAbstractSchemaCodeGenerator(new NetteObjectSchemaCodeGenerator($fileWriter));

$app = new Application();

$app->add(
    new GenerateCommand(
        new CebeOpenapiApiBuilder(new CebeOpenapiFileReader(), new CebeOpenapiSchemaFactory()),
        new NetteApiCodeGenerator($fileWriter, new NettePathCodeGenerator(new NetteRequestBodyFormatCodeGenerator(
            $abstractSchemaCodeGenerator,
            new NetteGuzzleBodyCodeGenerator()
        ), new NettePathParameterCodeGenerator($abstractSchemaCodeGenerator)))
    )
);

$app->run();