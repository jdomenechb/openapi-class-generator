<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\PrimitiveType;


use Jdomenechb\OpenApiClassGenerator\Model\AbstractType;

class Vector extends AbstractType
{
    private $wrapped;

    /**
     * Vector constructor.
     *
     * @param $wrapped
     */
    public function __construct($wrapped)
    {
        $this->wrapped = $wrapped;
    }

}