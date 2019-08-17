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

    /**
     * ApiOperation constructor.
     *
     * @param string $method
     * @param string $path
     */
    public function __construct(string $method, string $path)
    {
        $this->method = $method;
        $this->path = $path;
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
}