<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\DtoCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class NetteDtoCodeGenerator implements DtoCodeGenerator
{
    public function generate(ObjectSchema $dto)
    {
        $namaspace = new PhpNamespace($dto->namespace());

        $classGenerator = new ClassType($dto->name() );
        $classGenerator
            ->setFinal();

        foreach ($dto->properties() as $property) {
            $classGenerator->addProperty($property->name())
                ->setVisibility('private');
        }
    }

}