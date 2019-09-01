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

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use Nette\PhpGenerator\Method;

class NetteSecuritySchemeCodeGenerator
{
    public function generate(AbstractSecurityScheme $securityScheme, Method $method): void
    {
        if ($securityScheme instanceof HttpSecurityScheme) {
            switch ($securityScheme->scheme()) {
                case 'bearer':
                    $method
                        ->addComment('@param string $bearer')
                        ->addParameter('bearer')
                        ->setTypeHint('string');
                    break;
            }
        }
    }
}
