<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\exceptions\UnresolvableReferenceException;
use cebe\openapi\spec\RequestBody as CebeRequestBody;
use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiBuilder;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use RuntimeException;

class CebeOpenapiApiBuilder implements ApiBuilder
{
    /** @var CebeOpenapiFileReader */
    private $fileReader;
    /**
     * @var CebeOpenapiSchemaFactory
     */
    private $typeFactory;

    /**
     * @var CebeOpenapiSecuritySchemeFactory
     */
    private $securitySchemeFactory;
    /**
     * @var CebeOpenapiSecurityFactory
     */
    private $securityFactory;

    /**
     * CebeOpenapiApiParser constructor.
     *
     * @param CebeOpenapiFileReader            $fileReader
     * @param CebeOpenapiSchemaFactory         $typeFactory
     * @param CebeOpenapiSecuritySchemeFactory $securitySchemeFactory
     * @param CebeOpenapiSecurityFactory       $securityFactory
     */
    public function __construct(CebeOpenapiFileReader $fileReader, CebeOpenapiSchemaFactory $typeFactory, CebeOpenapiSecuritySchemeFactory $securitySchemeFactory, CebeOpenapiSecurityFactory $securityFactory)
    {
        $this->fileReader = $fileReader;
        $this->typeFactory = $typeFactory;
        $this->securitySchemeFactory = $securitySchemeFactory;
        $this->securityFactory = $securityFactory;
    }

    /**
     * @param string $filename
     * @param string $namespacePrefix
     *
     * @throws TypeErrorException
     * @throws UnresolvableReferenceException
     *
     * @return Api
     */
    public function fromFile(string $filename, string $namespacePrefix = ''): Api
    {
        $contract = $this->fileReader->read($filename);

        if (!$contract->validate()) {
            throw new RuntimeException('Invalid contract');
        }

        $apiService = new Api(
            $contract->info->title,
            $contract->info->version,
            $namespacePrefix,
            $contract->info->description,
            $contract->info->contact ? $contract->info->contact->name : null,
            $contract->info->contact ? $contract->info->contact->email : null
        );

        // SecuritySchemes
        $securitySchemes = [];

        if (!empty($contract->components->securitySchemes)) {
            foreach ($contract->components->securitySchemes as $contractSecuritySchemeName => $contractSecurityScheme) {
                $securitySchemes[$contractSecuritySchemeName] = $this->securitySchemeFactory->generate($contractSecurityScheme);
            }
        }

        // Default Security
        $defaultSecurities = $this->securityFactory->generate($contract->security, $securitySchemes);

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
                                throw new RuntimeException('Unrecognized requestBody format: ' . $mediaType);
                        }

                        $requestBodyFormat = new RequestBodyFormat(
                            $format,
                            $this->typeFactory->build($content->schema, 'request')
                        );
                        $requestBody->addFormat($requestBodyFormat);
                    }
                }

                // FIXME: Provisional fix for issue https://github.com/cebe/php-openapi/issues/33
                $contractOperationSerialized = $contractOperation->getSerializableData();

                $operation = new Path(
                    $method,
                    $path,
                    $contractOperation->summary,
                    $contractOperation->description,
                    $requestBody,
                    $parameters,
                    isset($contractOperationSerialized->security) ? $this->securityFactory->generate($contractOperation->security, $securitySchemes) : $defaultSecurities
                );

                $apiService->addOperation($operation);
            }
        }

        return $apiService;
    }
}
