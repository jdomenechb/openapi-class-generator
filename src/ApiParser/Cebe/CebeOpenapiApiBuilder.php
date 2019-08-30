<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;


use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiBuilder;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use Jdomenechb\OpenApiClassGenerator\Model\Api;

class CebeOpenapiApiBuilder implements ApiBuilder
{
    /** @var CebeOpenapiFileReader */
    private $fileReader;
    /**
     * @var CebeOpenapiSchemaFactory
     */
    private $typeFactory;

    /**
     * CebeOpenapiApiParser constructor.
     *
     * @param CebeOpenapiFileReader $fileReader
     * @param CebeOpenapiSchemaFactory $typeFactory
     */
    public function __construct(CebeOpenapiFileReader $fileReader, CebeOpenapiSchemaFactory $typeFactory)
    {
        $this->fileReader = $fileReader;
        $this->typeFactory = $typeFactory;
    }

    /**
     * @param string $filename
     * @param string $namespacePrefix
     *
     * @return Api
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     */
    public function fromFile(string $filename, string $namespacePrefix = '') :Api
    {
        $contract = $this->fileReader->read($filename);

        $apiService = new Api(
            $contract->info->title,
            $contract->info->version,
            $namespacePrefix,
            $contract->info->description,
            $contract->info->contact ? $contract->info->contact->name : null,
            $contract->info->contact ? $contract->info->contact->email : null
        );

        // Parse paths
        foreach ($contract->paths as $path => $pathInfo) {
            foreach ($pathInfo->getOperations() as $method => $contractOperation) {
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

                if ($contractOperation->requestBody) {
                    $requestBody = new RequestBody(
                        $contractOperation->requestBody->description,
                        $contractOperation->requestBody->required
                    );

                    foreach ($contractOperation->requestBody->content as $mediaType => $content) {
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

                $operation = new Path(
                    $method,
                    $path,
                    $contractOperation->summary,
                    $contractOperation->description,
                    $requestBody,
                    $parameters
                );

                $apiService->addOperation($operation);
            }
        }

        return $apiService;
    }

}