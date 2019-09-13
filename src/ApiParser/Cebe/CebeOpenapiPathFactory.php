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

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;

use cebe\openapi\spec\Operation;
use cebe\openapi\spec\RequestBody as CebeRequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;

class CebeOpenapiPathFactory
{
    /**
     * @var CebeOpenapiSchemaFactory
     */
    private $schemaFactory;

    /**
     * CebeOpenapiSecurityFactory constructor.
     *
     * @param CebeOpenapiSchemaFactory $typeFactory
     */
    public function __construct(CebeOpenapiSchemaFactory $typeFactory)
    {
        $this->schemaFactory = $typeFactory;
    }

    /**
     * @param Operation $contractOperation
     * @param $method
     * @param $path
     * @param AbstractSecurityScheme[] $securities
     *
     * @return Path
     */
    public function generate(
        Operation $contractOperation,
        $method,
        $path,
        array $securities
    ): Path {
        $parameters = [];

        foreach ($contractOperation->parameters as $parameter) {
            $parameters[] = new PathParameter(
                $parameter->name,
                $parameter->in,
                $parameter->description,
                $parameter->required,
                $parameter->deprecated,
                $parameter->schema ? $this->schemaFactory->build($parameter->schema, 'parameter') : null
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

                    case 'application/x-www-form-urlencoded':
                        $format = 'form';
                        break;

                    default:
                        throw new \RuntimeException('Unrecognized requestBody format: ' . $mediaType);
                }

                $requestBodyFormat = new RequestBodyFormat(
                    $format,
                    $this->schemaFactory->build($content->schema, 'request')
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
