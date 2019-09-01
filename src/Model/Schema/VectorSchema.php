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

    /**
     * @return AbstractSchema
     */
    public function wrapped(): AbstractSchema
    {
        return $this->wrapped;
    }
}
