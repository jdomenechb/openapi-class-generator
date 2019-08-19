<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;

class VectorSchema extends AbstractSchema
{
    private $wrapped;

    /**
     * Vector constructor.
     *
     * @param $wrapped
     */
    public function __construct(AbstractSchema $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function getPhpType(): string
    {
        return 'array';
    }

}