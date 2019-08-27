<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use function count;

class NettePathCodeGenerator
{
    /**
     * @var NetteRequestBodyFormatCodeGenerator
     */
    private $apiOperationFormatGenerator;

    /**
     * NettePathCodeGenerator constructor.
     *
     * @param NetteRequestBodyFormatCodeGenerator $apiOperationFormatGenerator
     */
    public function __construct(NetteRequestBodyFormatCodeGenerator $apiOperationFormatGenerator)
    {
        $this->apiOperationFormatGenerator = $apiOperationFormatGenerator;
    }


    /**
     * @param ClassType $classRep
     * @param PhpNamespace $namespace
     * @param Path $path
     */
    public function generate(
        ClassType $classRep,
        PhpNamespace $namespace,
        Path $path
    ): void {
        $referenceMethodName = $path->method() . $path->path();

        $requestBody = $path->requestBody();
        $nFormats = $requestBody ? count($requestBody->formats()) : 0;

        if ($nFormats === 0) {
            $this->apiOperationFormatGenerator->generate($classRep, $namespace, $path);
        } else {

            foreach ($requestBody->formats() as $format) {
                $this->apiOperationFormatGenerator->generate(
                    $classRep,
                    $namespace,
                    $path,
                    $format,
                    $nFormats > 1
                );
            }
        }
    }
}