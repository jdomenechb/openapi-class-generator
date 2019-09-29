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
     * @param AbstractSchema $wrapped
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

    public function getPhpToArrayValue(string $origin): string
    {
        // Optimization for avoiding doing an unnecessary array map
        if ('$value' === $this->wrapped->getPhpToArrayValue('$value')) {
            return parent::getPhpToArrayValue($origin);
        }

        return 'array_map(static function ($value) { return ' . $this->wrapped->getPhpToArrayValue('$value') . '; }, ' . $origin . ')';
    }

    public function getPhpFromArrayValue(string $origin): string
    {
        // Optimization for avoiding doing an unnecessary array map
        if ('$value' === $this->wrapped->getPhpFromArrayValue('$value')) {
            return parent::getPhpFromArrayValue($origin);
        }

        return 'array_map(static function ($value) { return ' . $this->wrapped->getPhpFromArrayValue('$value') . '; }, ' . $origin . ')';
    }

    public function getPhpFromArrayDefault(): string
    {
        return '[]';
    }
}
