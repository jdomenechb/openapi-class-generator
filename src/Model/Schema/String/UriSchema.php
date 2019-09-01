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

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema\String;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\SchemaValueValidation;

class UriSchema extends StringSchema implements SchemaValueValidation
{
    public function getPhpValidation(string $origin): string
    {
        return <<<CODE
if (!filter_var(${origin}, FILTER_VALIDATE_URL)) {
    throw new \\InvalidArgumentException('Invalid url ${origin}');
}
CODE;
    }
}
