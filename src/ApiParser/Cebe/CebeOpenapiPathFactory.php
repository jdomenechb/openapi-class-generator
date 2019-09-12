<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;


use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody as CebeRequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;

class CebeOpenapiPathFactory
{
    /**
     * @var CebeOpenapiSchemaFactory
     */
    private $typeFactory;

    /**
     * CebeOpenapiSecurityFactory constructor.
     *
     * @param CebeOpenapiSchemaFactory $typeFactory
     */
    public function __construct(CebeOpenapiSchemaFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function generate(
        Operation $contractOperation,
        $method,
        $path,
        array $securities
    ) :Path {
        $parameters = [];

        foreach ($contractOperation->parameters as $parameter) {
            $parameters[] = new PathParameter(
                $parameter->name,
                $parameter->in,
                $parameter->description,
                $parameter->required,
                $parameter->deprecated,
                $parameter->schema ? $this->typeFactory->build($parameter->schema, 'parameter') : null
            );
        }

        $requestBody = null;

        // FIXME: Provisional fix for https://github.com/cebe/php-openapi/issues/34
        /** @var CebeRequestBody|null $contractOpRequestBody */
        $contractOpRequestBody = $contractOperation->requestBody;

        if ($contractOpRequestBody) {
            $requestBody = new RequestBody(
                $contractOpRequestBody->description,
                $contractOpRequestBody->required
            );

            foreach ($contractOpRequestBody->content as $mediaType => $content) {
                switch ($mediaType) {
                    case 'application/json':
                        $format = 'json';
                        break;

                    default:
                        throw new \RuntimeException('Unrecognized requestBody format: ' . $mediaType);
                }

                $requestBodyFormat = new RequestBodyFormat(
                    $format,
                    $this->typeFactory->build($content->schema, 'request')
                );
                $requestBody->addFormat($requestBodyFormat);
            }
        }

        $pathObj = new Path(
            $method,
            $path,
            $contractOperation->summary,
            $contractOperation->description,
            $requestBody,
            $parameters,
            $securities
        );

        return $pathObj;
    }
}