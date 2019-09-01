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

class ObjectSchemaProperty
{
    /** @var string */
    private $name;

    /** @var bool */
    private $required;
    /**
     * @var AbstractSchema
     */
    private $schema;

    /**
     * DtoProperty constructor.
     *
     * @param string         $name
     * @param bool           $required
     * @param AbstractSchema $schema
     */
    public function __construct(string $name, bool $required, AbstractSchema $schema)
    {
        $this->name = $name;
        $this->required = $required;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * @return AbstractSchema
     */
    public function schema(): AbstractSchema
    {
        return $this->schema;
    }
}
