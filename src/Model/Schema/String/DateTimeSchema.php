<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema\String;


class DateTimeSchema extends StringSchema
{
    public function getPhpType(): string
    {
        return '\\' . \DateTimeImmutable::class;
    }

    public function getPhpSerializationValue(string $origin): string
    {
        return parent::getPhpSerializationValue($origin) . "->format('c')";
    }


}