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

    /** @var NetteAbstractSchemaCodeGenerator */
    private $abstractSchemaCodeGenerator;

    /**
     * NetteRequestBodyFormatCodeGenerator constructor.
     *
     * @param NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator
     */
    public function __construct(NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator)
    {
        $this->abstractSchemaCodeGenerator = $abstractSchemaCodeGenerator;
    }

    public function generate(
        Method $method,
        PhpNamespace $namespace,
        Path $path,
        RequestBodyFormat $format
    ): void {
        $requestClassName = $this->abstractSchemaCodeGenerator->generate(
            $format->schema(),
            $namespace->getName(),
            $format->format(),
            $method->getName()
        );

        $requestBody = $path->requestBody();

        if (!$requestBody) {
            throw new \RuntimeException('Expected requestBody');
        }

        $requestBodyRequired = $requestBody->required();
        $requestBodyDescription = $requestBody->description();

        $method
            ->addComment(
                '@param ' . $requestClassName . (!$requestBodyRequired ? '|null' : '') . ' $requestBody'
                . ($requestBodyDescription ? ' ' . $requestBodyDescription : '')
            )
            ->addParameter('requestBody')
            ->setTypeHint($requestClassName)
            ->setNullable(!$requestBodyRequired);

        if ($format->format() === 'json') {
            $serializeBody = '\json_encode($requestBody);';
            $extraGuzzleReqParams =  '\'headers\' => [\'Content-Type\' => \'application/json\']';
        } else {
            throw new RuntimeException('Unrecognized format ' . $format->format());
        }

        $method
            ->addBody('if ($requestBody !== null) {')
            ->addBody('    $serializedRequestBody = ' . $serializeBody)
            ->addBody(
                '    $response = $this->client->request(?, ?, [\'body\' => $serializedRequestBody' . ($extraGuzzleReqParams? ', ' . $extraGuzzleReqParams: '') . ']);',
                [$path->method(), $path->path()]
            )
            ->addBody('} else {')
            ->addBody(
                '    $response = $this->client->request(?, ?, ['. $extraGuzzleReqParams . ']);',
                [$path->method(), $path->path()]
            )
            ->addBody('}')
            ->addBody('')
            ->addBody('return $response;');

    }
}