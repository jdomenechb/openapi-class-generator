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

namespace Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme;

abstract class AbstractSecurityScheme
{
    /** @var string|null */
    protected $description;

    /**
     * AbstractSecurityScheme constructor.
     *
     * @param string|null $description
     */
    public function __construct(?string $description)
    {
        $this->description = $description;
    }

    abstract public function type(): string;

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }
}
