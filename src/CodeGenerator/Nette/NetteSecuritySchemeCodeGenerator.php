<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use Nette\PhpGenerator\Method;
use RuntimeException;

class NetteSecuritySchemeCodeGenerator
{
    public function generate(AbstractSecurityScheme $securityScheme, Method $method)
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