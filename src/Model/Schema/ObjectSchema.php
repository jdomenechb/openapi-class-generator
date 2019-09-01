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

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;

class ObjectSchema extends AbstractSchema
{
    /** @var string */
    private $name;

    /** @var ObjectSchemaProperty[] */
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

    public function addProperty(ObjectSchemaProperty $property): void
    {
        $this->properties[] = $property;
    }

    /**
     * @return ObjectSchemaProperty[]
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
