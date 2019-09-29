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

use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\MediaType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use RuntimeException;

class NetteRequestBodyFormatCodeGenerator
{
    /** @var NetteAbstractSchemaCodeGenerator */
    private $abstractSchemaCodeGenerator;

    /**
     * @var NetteGuzzleBodyCodeGenerator
     */
    private $guzzleBodyCodeGenerator;

    /**
     * NetteRequestBodyFormatCodeGenerator constructor.
     *
     * @param NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator
     * @param NetteGuzzleBodyCodeGenerator     $guzzleBodyCodeGenerator
     */
    public function __construct(NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator, NetteGuzzleBodyCodeGenerator $guzzleBodyCodeGenerator)
    {
        $this->abstractSchemaCodeGenerator = $abstractSchemaCodeGenerator;
        $this->guzzleBodyCodeGenerator = $guzzleBodyCodeGenerator;
    }

    public function generate(
        Method $method,
        PhpNamespace $namespace,
        Path $path,
        MediaType $format
    ): void {
        $requestTypeHint = $this->abstractSchemaCodeGenerator->generate(
            $format->schema(),
            $namespace->getName(),
            $format->format(),
            $method->getName()
        );

        $requestBody = $path->requestBody();

        if (!$requestBody) {
            throw new RuntimeException('Expected requestBody');
        }

        $requestBodyRequired = $requestBody->required();
        $requestBodyDescription = $requestBody->description();

        $method
            ->addComment(
                '@param ' . $requestTypeHint . (!$requestBodyRequired ? '|null' : '') . ' $requestBody'
                . ($requestBodyDescription ? ' ' . $requestBodyDescription : '')
            )
            ->addParameter('requestBody')
            ->setTypeHint($requestTypeHint)
            ->setNullable(!$requestBodyRequired);

        $this->guzzleBodyCodeGenerator->generate($method, $path, $format->format());
    }
}
