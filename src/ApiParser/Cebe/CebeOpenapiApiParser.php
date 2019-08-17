<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;


use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiParser;
use Jdomenechb\OpenApiClassGenerator\Model\ApiOperation;
use Jdomenechb\OpenApiClassGenerator\Model\ApiOperationFormat;
use Jdomenechb\OpenApiClassGenerator\Model\ApiService;

class CebeOpenapiApiParser implements ApiParser
{
    /** @var CebeOpenapiFileReader */
    private $fileReader;

    /**
     * CebeOpenapiApiParser constructor.
     *
     * @param CebeOpenapiFileReader $fileReader
     */
    public function __construct(CebeOpenapiFileReader $fileReader)
    {
        $this->fileReader = $fileReader;
    }

    /**
     * @param string $filename
     * @param string $namespacePrefix
     *
     * @return ApiService
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     */
    public function parse(string $filename, string $namespacePrefix = '') :ApiService
    {
        $contract = $this->fileReader->read($filename);

        // Determine namespace
        if ($namespacePrefix) {
            $namespace = rtrim($namespacePrefix, '\\') . '\\';
        } else {
            $namespace = 'Ocg\\';
        }

        $namespace .= 'ApiService';

        // Create Service
        $apiService = new ApiService($contract->info->title, $namespace);

        // Parse paths
        foreach ($contract->paths as $path => $pathInfo) {
            foreach ($pathInfo->getOperations() as $method => $contractOperation) {
                if ($contractOperation->requestBody) {
                    $operation = new ApiOperation($method, $path);

                    foreach ($contractOperation->requestBody->content as $mediaType => $content) {
                        switch ($mediaType) {
                            case 'application/json':
                                $format = 'json';
                                break;

                            default:
                                throw new \RuntimeException('Unrecognized requestBody format: ' . $mediaType);
                        }

                        $operationFormat = new ApiOperationFormat($format);
                        $operation->addFormat($operationFormat);
                    }

                    $apiService->addOperation($operation);
                }
            }
        }

        return $apiService;
    }


}