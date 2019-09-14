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
use cebe\openapi\spec\SecurityScheme;
use Jdomenechb\OpenApiClassGenerator\ApiParser\ApiBuilder;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use RuntimeException;

class CebeOpenapiApiBuilder implements ApiBuilder
{
    /** @var CebeOpenapiFileReader */
    private $fileReader;

    /**
     * @var CebeOpenapiSecuritySchemeFactory
     */
    private $securitySchemeFactory;

    /**
     * @var CebeOpenapiSecurityFactory
     */
    private $securityFactory;
    /**
     * @var CebeOpenapiPathFactory
     */
    private $pathFactory;

    /**
     * CebeOpenapiApiParser constructor.
     *
     * @param CebeOpenapiFileReader            $fileReader
     * @param CebeOpenapiSecuritySchemeFactory $securitySchemeFactory
     * @param CebeOpenapiSecurityFactory       $securityFactory
     * @param CebeOpenapiPathFactory           $pathFactory
     */
    public function __construct(
        CebeOpenapiFileReader $fileReader,
        CebeOpenapiSecuritySchemeFactory $securitySchemeFactory,
        CebeOpenapiSecurityFactory $securityFactory,
        CebeOpenapiPathFactory $pathFactory
    ) {
        $this->fileReader = $fileReader;
        $this->securitySchemeFactory = $securitySchemeFactory;
        $this->securityFactory = $securityFactory;
        $this->pathFactory = $pathFactory;
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
            throw new RuntimeException('Invalid contract: ' . \implode('; ', $contract->getErrors()));
        }

        $apiService = new Api(
            $contract->info->title,
            $contract->info->version,
            $namespacePrefix,
            $contract->info->description,
            $contract->info->contact->name ?? null,
            $contract->info->contact->email ?? null
        );

        // SecuritySchemes
        $securitySchemes = [];

        if (!empty($contract->components->securitySchemes)) {
            foreach ($contract->components->securitySchemes as $contractSecuritySchemeName => $contractSecurityScheme) {
                if (!$contractSecurityScheme instanceof SecurityScheme) {
                    throw new \RuntimeException('Invalid contract security scheme in components');
                }

                $securitySchemes[$contractSecuritySchemeName] = $this->securitySchemeFactory->generate(
                    $contractSecurityScheme
                );
            }
        }

        // Default Security
        $defaultSecurities = $this->securityFactory->generate($contract->security, $securitySchemes);

        // Parse paths
        foreach ($contract->paths as $path => $pathInfo) {
            foreach ($pathInfo->getOperations() as $method => $contractOperation) {
                // FIXME: Provisional fix for issue https://github.com/cebe/php-openapi/issues/33
                $contractOperationSerialized = $contractOperation->getSerializableData();

                $pathObj = $this->pathFactory->generate(
                    $contractOperation,
                    $method,
                    $path,
                    isset($contractOperationSerialized->security) ? $this->securityFactory->generate(
                        $contractOperation->security,
                        $securitySchemes
                    ) : $defaultSecurities
                );

                $apiService->addPath($pathObj);
            }
        }

        return $apiService;
    }
}
