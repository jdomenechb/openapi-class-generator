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
use Jdomenechb\OpenApiClassGenerator\Model\ApiOperation;
use Jdomenechb\OpenApiClassGenerator\Model\ApiOperationFormat;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

class NetteApiOperationFormatGenerator
{
    /**
     * @var NetteObjectSchemaCodeGenerator
     */
    private $schemaCodeGenerator;

    /** @var ClassFileWriter */
    private $fileWriter;

    public function __construct(NetteObjectSchemaCodeGenerator $schemaCodeGenerator, ClassFileWriter $fileWriter)
    {
        $this->schemaCodeGenerator = $schemaCodeGenerator;
        $this->fileWriter = $fileWriter;
    }

    public function generate(
        ClassType $classRep,
        PhpNamespace $namespace,
        ApiOperation $operation,
        ?ApiOperationFormat $format = null,
        bool $formatSuffix = false
    ): void
    {
        $methodName = $operation->method() . $operation->path();

        if ($formatSuffix && $format) {
            $methodName .= ' ' . $format->format();
        }

        $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $methodName));

        $method = $classRep->addMethod($methodName)
            ->setVisibility('public')
            ->setReturnType(ResponseInterface::class);

        if ($operation->description()) {
            $method->addComment($operation->description());
            $method->addComment('');
        }

        if ($operation->summary()) {
            $method->addComment($operation->summary());
            $method->addComment('');
        }

        $method
            ->addComment('Endpoint URL: ' . $operation->path())
            ->addComment('Method: ' . strtoupper($operation->method()))
            ->addComment('');

        if ($format) {
            $requestClassName = $this->schemaCodeGenerator->generate(
                $format->schema(),
                $namespace->getName(),
                $format->format(),
                $methodName
            );

            $method
                ->addComment('@param ' . $requestClassName . ' $requestBody')
                ->addParameter('requestBody')
                ->setTypeHint($requestClassName);

            if ($format->format() === 'json') {
                $method
                    ->addBody('$serializedRequestBody = \json_encode($requestBody);')
                    ->addBody(
                        '$response = $this->client->request(?, ?, [\'body\' => $serializedRequestBody, \'headers\' => [\'Content-Type\' => \'application/json\']]);',
                        [$operation->method(), $operation->path()]
                    );
            } else {
                throw new RuntimeException('Unrecognized format ' . $format->format());
            }

            $method
                ->addBody('return $response;');

        } else {
            $method->addBody('return $this->client->request(?, ?);', [$operation->method(), $operation->path()]);
        }

        $method
            ->addComment('@return ResponseInterface')
            ->addComment('@throws GuzzleException')
        ;
    }
}