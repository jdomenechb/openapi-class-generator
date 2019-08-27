<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use RuntimeException;

class NetteRequestBodyFormatCodeGenerator
{
    /**
     * @var NetteObjectSchemaCodeGenerator
     */
    private $schemaCodeGenerator;

    public function __construct(NetteObjectSchemaCodeGenerator $schemaCodeGenerator)
    {
        $this->schemaCodeGenerator = $schemaCodeGenerator;
    }

    public function generate(
        Method $method,
        PhpNamespace $namespace,
        Path $path,
        RequestBodyFormat $format
    ): void {
        $requestClassName = $this->schemaCodeGenerator->generate(
            $format->schema(),
            $namespace->getName(),
            $format->format(),
            $method->getName()
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
                    [$path->method(), $path->path()]
                );
        } else {
            throw new RuntimeException('Unrecognized format ' . $format->format());
        }

        $method
            ->addBody('return $response;');

    }
}