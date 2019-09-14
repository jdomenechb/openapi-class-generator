<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;

class RawExpression
{
    /** @var string */
    private $expression;

    /**
     * RawExpression constructor.
     *
     * @param string $expression
     */
    public function __construct(string $expression)
    {
        $this->expression = $expression;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->expression;
    }
}
