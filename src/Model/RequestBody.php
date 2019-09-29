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

namespace Jdomenechb\OpenApiClassGenerator\Model;

class RequestBody
{
    /** @var MediaType[] */
    private $formats;

    /** @var bool */
    private $required;

    /**
     * @var string|null
     */
    private $description;

    public function __construct(?string $description, bool $required)
    {
        $this->formats = [];
        $this->description = $description;
        $this->required = $required;
    }

    public function addFormat(MediaType $format): void
    {
        $this->formats[] = $format;
    }

    /**
     * @return MediaType[]
     */
    public function formats(): array
    {
        return $this->formats;
    }

    /**
     * @return bool
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }
}
