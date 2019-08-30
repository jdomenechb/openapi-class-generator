<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;

class NetteAbstractSchemaCodeGenerator
{
    /**
     * @var NetteObjectSchemaCodeGenerator
     */
    private $objectSchemaCodeGenerator;

    public function __construct(NetteObjectSchemaCodeGenerator $schemaCodeGenerator)
    {
        $this->objectSchemaCodeGenerator = $schemaCodeGenerator;
    }

    public function generate(
        AbstractSchema $schema,
        string $namespaceName,
        ?string $format = null,
        string $namePrefix = ''
    ): string {
        if ($schema instanceof ObjectSchema) {
            $requestClassName = $this->objectSchemaCodeGenerator->generate(
                $schema,
                $namespaceName,
                $format,
                $namePrefix
            );
        } else {
            $requestClassName = $schema->getPhpType();
        }

        return $requestClassName;
    }

}