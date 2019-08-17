<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;

class DtoProperty
{
    /** @var string */
    private $name;

    /** @var string[] */
    private $types;

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string[]
     */
    public function types(): array
    {
        return $this->types;
    }
}