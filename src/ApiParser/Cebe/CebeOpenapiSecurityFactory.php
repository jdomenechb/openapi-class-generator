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

use cebe\openapi\spec\SecurityRequirement;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use RuntimeException;

class CebeOpenapiSecurityFactory
{
    /**
     * @param SecurityRequirement[] $securityRequirements
     * @param AbstractSecurityScheme[] $availableSecuritySchemes
     *
     * @return AbstractSecurityScheme[]
     */
    public function generate(array $securityRequirements, array $availableSecuritySchemes): array
    {
        $securities = [];

        foreach ($securityRequirements as $contractSecurityReq) {
            $contractSecurityReqSerialized = $contractSecurityReq->getSerializableData();

            foreach ($contractSecurityReqSerialized as $contractSecurityReqName => $contractSecurityReqValue) {
                if (!isset($availableSecuritySchemes[$contractSecurityReqName])) {
                    throw new RuntimeException(\sprintf('Security scheme "%s" not found', $contractSecurityReqName));
                }

                $securities[] = $availableSecuritySchemes[$contractSecurityReqName];
            }
        }

        return $securities;
    }
}
