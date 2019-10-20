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
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody as CebeRequestBody;
use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\MediaType;
use Jdomenechb\OpenApiClassGenerator\Model\Response;
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
     * @param Operation                $contractOperation
     * @param string                   $method
     * @param string                   $path
     * @param AbstractSecurityScheme[] $securities
     *
     * @return Path
     */
    public function generate(
        Operation $contractOperation,
        string $method,
        string $path,
        array $securities
    ): Path {
        $parameters = [];

        foreach ($contractOperation->parameters as $parameter) {
            /** @var Parameter $parameter */
            $schema = $parameter->schema;
            $builtSchema = null;

            if ($schema) {
                if (!$schema instanceof Schema) {
                    throw new \RuntimeException('Parameter schema must not be a reference');
                }

                $builtSchema = $this->schemaFactory->build($schema, 'parameter');
            }

            $parameters[] = new PathParameter(
                $parameter->name,
                $parameter->in,
                $parameter->description,
                $parameter->required,
                $parameter->deprecated,
                $builtSchema
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
                $reqBodySchema = $content->schema;

                if (!$reqBodySchema instanceof Schema) {
                    throw new \RuntimeException('Ocg only accepts requestBody with schema for now');
                }

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

                $requestBodyFormat = new MediaType(
                    $format,
                    $this->schemaFactory->build($reqBodySchema, 'request')
                );

                $requestBody->addFormat($requestBodyFormat);
            }
        }

        $responses = [];

        foreach ($contractOperation->responses as $statusCode => $contractResponse) {
            /** @var \cebe\openapi\spec\Response $contractResponse */
            $response = new Response($statusCode !== 'default'? $statusCode: null, $contractResponse->description);

            foreach ($contractResponse->content as $mediaType => $mediaTypeObject) {
                /** @var \cebe\openapi\spec\MediaType $mediaTypeObject */
                $responseSchema = $mediaTypeObject->schema;

                if ($responseSchema instanceof Reference) {
                    throw new \RuntimeException('Expected schema, got reference');
                }

                switch ($mediaType) {
                    case 'application/json':
                        $format = 'json';
                        break;

                    case 'application/x-www-form-urlencoded':
                        $format = 'form';
                        break;

                    default:
                        throw new \RuntimeException('Unrecognized response format: ' . $mediaType);
                }

                $response->addMediaType($format, $responseSchema ? $this->schemaFactory->build($responseSchema, 'responseContent'): null);
            }

            $responses[] = $response;
        }

        $pathObj = new Path(
            $method,
            $path,
            $contractOperation->operationId,
            $contractOperation->summary,
            $contractOperation->description,
            $requestBody,
            $parameters,
            $securities,
            $responses
        );

        return $pathObj;
    }
}
