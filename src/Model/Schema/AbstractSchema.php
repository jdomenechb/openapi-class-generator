<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;


abstract class AbstractSchema
{
    abstract public function getPhpType() :string;

    public function getPhpSerializationValue(string $origin) :string
    {
        return $origin;
    }
}