<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\Schema;


use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;

class VectorSchema extends \Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema
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