<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;


class ApiOperation
{
    /** @var string */
    private $method;

    /** @var string */
    private $path;

    /** @var ApiOperationFormat[] */
    private $formats;

    /**
     * @var string|null
     */
    private $summary;
    /**
     * @var string|null
     */
    private $description;

    /**
     * ApiOperation constructor.
     *
     * @param string $method
     * @param string $path
     * @param string|null $summary
     * @param string|null $description
     */
    public function __construct(string $method, string $path, ?string $summary, ?string $description)
    {
        $this->method = $method;
        $this->path = $path;
        $this->formats = [];
        $this->summary = $summary;
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function method(): string
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function path(): string
    {
        return $this->path;
    }

    public function addFormat(ApiOperationFormat $format): void
    {
        $this->formats[] = $format;
    }

    /**
     * @return ApiOperationFormat[]
     */
    public function formats(): array
    {
        return $this->formats;
    }

    /**
     * @return string|null
     */
    public function summary(): ?string
    {
        return $this->summary;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }

}