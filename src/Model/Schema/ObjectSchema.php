<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty;

class ObjectSchema extends AbstractSchema
{
    /** @var string */
    private $name;

    /** @var \Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty[] */
    private $properties;

    /**
     * Dto constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->properties = [];
    }


    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    public function addProperty(ObjectSchemaProperty $property) :void
    {
        $this->properties[] = $property;
    }

    /**
     * @return \Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty[]
     */
    public function properties(): array
    {
        return $this->properties;
    }

    public function getPhpType(): string
    {
        return 'object';
    }


}