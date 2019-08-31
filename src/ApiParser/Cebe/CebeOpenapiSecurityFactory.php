<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe;


use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use RuntimeException;

class CebeOpenapiSecurityFactory
{
    /**
     * @param array $securityRequirements
     * @param array $availableSecuritySchemes
     *
     * @return AbstractSecurityScheme[]
     */
    public function generate(array $securityRequirements, array $availableSecuritySchemes) :array
    {
        $securities = [];

        foreach ($securityRequirements as $contractSecurityReq) {
            $contractSecurityReqAsArray = (array) $contractSecurityReq->getSerializableData();

            foreach ($contractSecurityReqAsArray as $contractSecurityReqName => $contractSecurityReqValue) {
                if (!isset($availableSecuritySchemes[$contractSecurityReqName])) {
                    throw new RuntimeException(sprintf('Security scheme "%s" not found', $contractSecurityReqName));
                }

                $securities[] = $availableSecuritySchemes[$contractSecurityReqName];
            }
        }

        return $securities;
    }
}