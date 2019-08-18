<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\DateTimeSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use JsonSerializable;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class NetteObjectSchemaCodeGenerator
{
    public function generate(
        ObjectSchema $schema,
        ClassFileWriter $fileWriter,
        string $namespaceName,
        string $format,
        string $namePrefix = ''
    ): ClassType {
        $name = Inflector::classify($namePrefix . '-' . $schema->name());

        $classRef = new ClassType($name);
        $classRef->setFinal();

        $construct = $classRef->addMethod('__construct');

        foreach ($schema->properties() as $property) {
            $propertyName = $property->name();

            // Property
            $classRef->addProperty($propertyName)
                ->setVisibility('private')
                ->setComment('@var ' . $property->schema()->getPhpType() . (!$property->required() ? '|null' : ''));

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
                    $construct->addBody(
                        sprintf('    throw new \InvalidArgumentException(\'Invalid %s\');', $propertyName)
                    );
                    $construct->addBody('}');
                    break;
            }

            $construct->addBody(sprintf('$this->%s = $%s;', $propertyName, $propertyName));
        }

        if ($format === 'json') {
            $classRef->addImplement(JsonSerializable::class);

            $serializeMethod = $classRef->addMethod('jsonSerialize')
                ->setReturnType('array')
                ->addBody('return [');

            foreach ($schema->properties() as $property) {
                $serializedValue = "\$this->{$property->name()}";

                if ($property->schema() instanceof DateTimeSchema) {
                    $serializedValue .= "->format('c')";
                }

                $serializeMethod->addBody("    '{$property->name()}' => $serializedValue,");
            }

            $serializeMethod->addBody('];');
        }

        $file = new PhpFile();
        $namespace = $file->addNamespace($namespaceName . '\\Dto');
        $namespace->add($classRef);

        $fileWriter->write((string)$file, $classRef->getName(), $namespace->getName());

        return $classRef;
    }

}