<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Exception;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Model\MediaType;
use Jdomenechb\OpenApiClassGenerator\Model\Response;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Psr\Http\Message\ResponseInterface;
use function count;

class NetteResponseCodeGenerator
{
    /** @var ClassFileWriter */
    private $fileWriter;

    /**
     * @var NetteAbstractSchemaCodeGenerator
     */
    private $abstractSchemaCodeGenerator;

    /**
     * NetteResponseExceptionCodeGenerator constructor.
     *
     * @param ClassFileWriter $fileWriter
     * @param NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator
     */
    public function __construct(ClassFileWriter $fileWriter, NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator)
    {
        $this->fileWriter = $fileWriter;
        $this->abstractSchemaCodeGenerator = $abstractSchemaCodeGenerator;
    }

    /**
     * @param Response $response
     * @param string $namespaceName
     * @param string $methodName
     *
     * @return array
     */
    public function generate(Response $response, string $namespaceName, string $methodName) :array
    {
        $toReturn = [];

        $mediaTypes = $response->mediaTypes();

        foreach ($mediaTypes as $mediaType) {
            $toReturn[$mediaType->format()] = $this->generateFile($namespaceName, $methodName, $mediaType);
        }

        if (!count($mediaTypes)) {
            $toReturn[''] = $this->generateFile($namespaceName, $methodName);
        }

        return $toReturn;
    }

    /**
     * @param string $namespaceName
     * @param string $methodName
     * @param MediaType|null $mediaType
     *
     * @return array
     */
    private function generateFile(
        string $namespaceName,
        string $methodName,
        ?MediaType $mediaType = null
    ): array {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($namespaceName);

        $classRepName = ucfirst($methodName) . ($mediaType ? ucfirst($mediaType->format()) : '') . 'Response';

        $classRep = new ClassType($classRepName);
        $classRep->addImplement($namespaceName . '\\ResponseInterface');
        $namespace->add($classRep);

        if ($mediaType && ($schema = $mediaType->schema())) {
            $constructor = $classRep->addMethod('__construct')
                ->setVisibility('public');

            $schemaClass = $this->abstractSchemaCodeGenerator->generate(
                $schema,
                $namespaceName . '\\Dto',
                $mediaType->format(),
                $methodName . ucfirst($mediaType->format())
            );

            $schemaType = $schema->getPhpType();

            if ($schemaType === 'object') {
                $schemaType = $schemaClass;
            }

            $classRep->addProperty('content')
                ->addComment('@var ' . $schemaClass);

            $classRep->addMethod('content')
                ->setVisibility('public')
                ->setReturnType($schemaType)
                ->addBody('return $this->content;');


            $constructor->addBody('$this->content = $content;')
                ->addParameter('content')
                ->setTypeHint($schemaType);
        } else {
            $schemaClass = 'null';

            $classRep->addMethod('content')
                ->setVisibility('public')
                ->setReturnType('void')
                ->addBody('return null;');
        }

        $printer = new PsrPrinter();

        $this->fileWriter->write($printer->printFile($file), $classRepName, $namespaceName);

        return [
            'class' => $namespaceName . '\\' . $classRepName,
            'dtoClass' => $schemaClass,
        ];
}
}