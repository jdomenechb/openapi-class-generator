<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;


class IntegerSchema extends AbstractSchema
{
    public function getPhpType(): string
    {
        return 'int';
    }

}