<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use RuntimeException;

class NetteSchemaCodeGenerator
{
    public function generate(AbstractSchema $schema, PhpNamespace $namespace, string $namePrefix = ''): ClassType
    {
        if ($schema instanceof ObjectSchema) {
            $name = Inflector::classify($namePrefix . '-' . $schema->name());

            $classRef = new ClassType($name);
            $classRef
                ->setFinal();

            $construct = $classRef->addMethod('__construct');

            foreach ($schema->properties() as $property) {
                $propertyName = $property->name();

                // Property
                $classRef->addProperty($propertyName)
                    ->setVisibility('private')
                    ->setComment('@var ' . $property->schema()->getPhpType() . (!$property->required()? '|null' : ''));

                // Getter
                $classRef->addMethod($propertyName)
                    ->setVisibility('public')
                    ->setBody(sprintf('return $this->%s;', $propertyName))
                    ->setReturnType($property->schema()->getPhpType())
                    ->setReturnNullable(!$property->required());

                // Constructor
                $construct->addParameter($propertyName)
                    ->setTypeHint($property->schema()->getPhpType())
                    ->setNullable(!$property->required());

                switch (get_class($property->schema())) {
                    case EmailSchema::class:
                        $construct->addBody(sprintf('if (!filter_var($%s, FILTER_VALIDATE_EMAIL)) {', $propertyName));
                        $construct->addBody(sprintf('throw new \InvalidArgumentException(\'Invalid %s\');', $propertyName));
                        $construct->addBody('}');
                        break;
                }

                $construct->addBody(sprintf('$this->%s = $%s;', $propertyName, $propertyName));
            }

            $namespace->add($classRef);

            return $classRef;
        }

        throw new RuntimeException('Not an object');
    }

}