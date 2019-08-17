<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;

class Dto extends AbstractType
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $name;

    /** @var DtoProperty[] */
    private $properties;

    /**
     * @return string
     */
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return DtoProperty[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

}