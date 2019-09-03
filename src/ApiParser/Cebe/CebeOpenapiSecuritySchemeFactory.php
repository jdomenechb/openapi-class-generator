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

use cebe\openapi\spec\SecurityScheme;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use RuntimeException;

class CebeOpenapiSecuritySchemeFactory
{
    public function generate(SecurityScheme $securityScheme): AbstractSecurityScheme
    {
        switch ($securityScheme->type) {
            case 'http':
                return new HttpSecurityScheme(
                    $securityScheme->scheme,
                    $securityScheme->bearerFormat,
                    $securityScheme->description
                );
        }

        throw new RuntimeException(
            'Unrecognized SecurityScheme type: ' . $securityScheme->type
        );
    }
}
