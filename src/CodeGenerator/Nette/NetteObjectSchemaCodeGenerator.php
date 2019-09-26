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
use Serializable;

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
                ->setBody(\sprintf('return $this->%s;', $propertyName))
                ->setReturnType($propertySchema->getPhpType())
                ->setReturnNullable(!$property->required());

            // Constructor
            $construct->addParameter($propertyName)
                ->setTypeHint($propertySchema->getPhpType())
                ->setNullable(!$property->required());

            if ($propertySchema instanceof SchemaValueValidation) {
                $construct->addBody($propertySchema->getPhpValidation('$' . $propertyName) . "\n");
            }

            // TODO: Implement validation for elements of array

            $construct->addBody(\sprintf('$this->%s = $%s;', $propertyName, $propertyName));

            if ($propertySchema instanceof VectorSchema) {
                $propertySchema = $propertySchema->wrapped();
            }

            if ($propertySchema instanceof ObjectSchema) {
                $this->generate($propertySchema, $namespaceName, $format, $name);
            }
        }

        $classRef->addImplement(Serializable::class);

        $serializeMethod = $classRef->addMethod('serialize')
            ->setReturnType('array')
            ->addBody('return [');

        foreach ($schema->properties() as $property) {
            $serializedValue = $property->schema()->getPhpSerializationValue("\$this->{$property->name()}");
            $serializeMethod->addBody("    '{$property->name()}' => ${serializedValue},");
        }

        $serializeMethod->addBody('];');

        $classRef->addMethod('unserialize')
            ->addBody('throw new \\RuntimeException(\'No unserializable class\');')
            ->setReturnType('void')
            ->addParameter('serialized');

        if ('json' === $format) {
            $classRef->addImplement(JsonSerializable::class);

            $classRef->addMethod('jsonSerialize')
                ->setReturnType('array')
                ->addBody('return $this->serialize();');
        }

        $file = new PhpFile();
        $namespace = $file->addNamespace($namespaceName . '\\Request');
        $namespace->add($classRef);

        $this->fileWriter->write((string) $file, $name, $namespace->getName());

        return '\\' . $namespace->getName() . '\\' . $name;
    }
}
