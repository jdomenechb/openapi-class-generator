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

use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;

class StringSchema extends AbstractSchema
{
    public function getPhpType(): string
    {
        return 'string';
    }
}
