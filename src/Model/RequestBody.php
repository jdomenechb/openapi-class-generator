<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;


class RequestBody
{
    /** @var RequestBodyFormat[] */
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

    public function addFormat(RequestBodyFormat $format): void
    {
        $this->formats[] = $format;
    }

    /**
     * @return RequestBodyFormat[]
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