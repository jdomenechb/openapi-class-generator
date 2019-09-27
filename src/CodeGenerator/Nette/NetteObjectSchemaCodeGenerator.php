<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
use Nette\PhpGenerator\PsrPrinter;

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
        ?string $format = null,
        string $classNamePrefix = ''
    ): string {
        $className = Inflector::classify($classNamePrefix . '-' . $schema->name());

        $classRef = new ClassType($className);
        $classRef->setFinal();

        $constructMethod = $classRef->addMethod('__construct');

        foreach ($schema->properties() as $property) {
            $propertyName = $property->name();
            $propertySchema = $property->schema();

            // Getter
            $classRef->addMethod($propertyName)
                ->setVisibility('public')
                ->setBody(\sprintf('return $this->%s;', $propertyName))
                ->setReturnType($propertySchema->getPhpType())
                ->setReturnNullable(!$property->required());

            // Constructor
            $constructMethod->addParameter($propertyName)
                ->setTypeHint($propertySchema->getPhpType())
                ->setNullable(!$property->required());

            if ($propertySchema instanceof SchemaValueValidation) {
                $constructMethod->addBody($propertySchema->getPhpValidation('$' . $propertyName) . "\n");
            }

            // TODO: Implement validation for elements of array

            $constructMethod->addBody(\sprintf('$this->%s = $%s;', $propertyName, $propertyName));

            $wasVector = false;

            if ($propertySchema instanceof VectorSchema) {
                $propertySchema = $propertySchema->wrapped();
                $wasVector = true;
            }

            if ($propertySchema instanceof ObjectSchema) {
                $schemaTypeName = $this->generate($propertySchema, $namespaceName, $format, $className);
            } else {
                $schemaTypeName = $propertySchema->getPhpType();
            }

            if ($wasVector) {
                $schemaTypeName .= '[]';
            }

            // Property
            $classRef->addProperty($propertyName)
                ->setVisibility('private')
                ->setComment('@var ' . ($schemaTypeName ?: $propertySchema->getPhpType()) . (!$property->required() ? '|null' : ''));
        }

        $toArrayMethod = $classRef->addMethod('toArray')
            ->setReturnType('array')
            ->addBody('return [');

        foreach ($schema->properties() as $property) {
            $propertyName = $property->name();
            $classPropertyVar = "\$this->{$propertyName}";

            $phpToArrayValue = $property->schema()->getPhpToArrayValue($classPropertyVar);

            if (
                !$property->required()
                && ($property->schema() instanceof ObjectSchema || $property->schema() instanceof VectorSchema)
            ) {
                $phpToArrayValue = $classPropertyVar . ' !== null? ' . $phpToArrayValue . ': null';
            }

            $toArrayMethod->addBody("    '{$propertyName}' => {$phpToArrayValue},");
        }

        $toArrayMethod->addBody('];');

        if ('json' === $format) {
            $classRef->addImplement(JsonSerializable::class);

            $classRef->addMethod('jsonSerialize')
                ->setReturnType('array')
                ->addBody('return $this->toArray();');
        }

        $file = new PhpFile();
        $namespace = $file->addNamespace($namespaceName . '\\Request');
        $namespace->add($classRef);

        $printer = new PsrPrinter();

        $this->fileWriter->write($printer->printFile($file), $className, $namespace->getName());

        return '\\' . $namespace->getName() . '\\' . $className;
    }
}
