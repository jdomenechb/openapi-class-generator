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
use Jdomenechb\OpenApiClassGenerator\Model\Schema\SchemaValueValidation;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\VectorSchema;
use JsonSerializable;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;

class NetteObjectSchemaCodeGenerator
{
    /**
     * @var ClassFileWriter
     */
    private $fileWriter;

    public function __construct(ClassFileWriter $fileWriter)
    {

        $this->fileWriter = $fileWriter;
    }

    public function generate(
        ObjectSchema $schema,
        string $namespaceName,
        string $format,
        string $namePrefix = ''
    ): string {
        $name = Inflector::classify($namePrefix . '-' . $schema->name());

        $classRef = new ClassType($name);
        $classRef->setFinal();

        $construct = $classRef->addMethod('__construct');

        foreach ($schema->properties() as $property) {
            $propertyName = $property->name();
            $propertySchema = $property->schema();

            // Property
            $classRef->addProperty($propertyName)
                ->setVisibility('private')
                ->setComment('@var ' . $propertySchema->getPhpType() . (!$property->required() ? '|null' : ''));

            // Getter
            $classRef->addMethod($propertyName)
                ->setVisibility('public')
                ->setBody(sprintf('return $this->%s;', $propertyName))
                ->setReturnType($propertySchema->getPhpType())
                ->setReturnNullable(!$property->required());

            // Constructor
            $construct->addParameter($propertyName)
                ->setTypeHint($propertySchema->getPhpType())
                ->setNullable(!$property->required());

            if ($propertySchema instanceof SchemaValueValidation) {
                $construct->addBody($propertySchema->getPhpValidation('$' . $propertyName) . "\n");
            }

            $construct->addBody(sprintf('$this->%s = $%s;', $propertyName, $propertyName));

            if ($propertySchema instanceof ObjectSchema) {
                $this->generate($propertySchema, $namespaceName, $format, $name);
            }

            if ($propertySchema instanceof VectorSchema && $propertySchema->wrapped() instanceof ObjectSchema) {
                $this->generate($propertySchema->wrapped(), $namespaceName, $format, $name);
            }
        }

        if ($format === 'json') {
            $classRef->addImplement(JsonSerializable::class);

            $serializeMethod = $classRef->addMethod('jsonSerialize')
                ->setReturnType('array')
                ->addBody('return [');

            foreach ($schema->properties() as $property) {
                $serializedValue = $property->schema()->getPhpSerializationValue("\$this->{$property->name()}");
                $serializeMethod->addBody("    '{$property->name()}' => $serializedValue,");
            }

            $serializeMethod->addBody('];');
        }

        $file = new PhpFile();
        $namespace = $file->addNamespace($namespaceName . '\\Dto');
        $namespace->add($classRef);

        $this->fileWriter->write((string)$file, $classRef->getName(), $namespace->getName());

        return '\\' . $namespace->getName() . '\\' . $classRef->getName();
    }

}