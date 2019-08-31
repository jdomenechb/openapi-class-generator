<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;

use cebe\openapi\spec\SecurityScheme;

class Path
{
    /** @var string */
    private $method;

    /** @var string */
    private $path;

    /**
     * @var string|null
     */
    private $summary;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var RequestBody|null
     */
    private $requestBody;

    /** @var PathParameter[] */
    private $parameters;

    /**
     * @var SecurityScheme[]
     */
    private $securitySchemes;

    /**
     * ApiOperation constructor.
     *
     * @param string $method
     * @param string $path
     * @param string|null $summary
     * @param string|null $description
     * @param RequestBody|null $requestBody
     * @param PathParameter[] $parameters
     * @param SecurityScheme[] $securitySchemes
     */
    public function __construct(string $method, string $path, ?string $summary, ?string $description, ?RequestBody $requestBody, array $parameters, array $securitySchemes)
    {
        $this->method = $method;
        $this->path = $path;
        $this->summary = $summary;
        $this->description = $description;
        $this->requestBody = $requestBody;
        $this->parameters = $parameters;
        $this->securitySchemes = $securitySchemes;
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

    /**
     * @return RequestBody|null
     */
    public function requestBody(): ?RequestBody
    {
        return $this->requestBody;
    }

    /**
     * @return PathParameter[]
     */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @return SecurityScheme[]
     */
    public function securitySchemes(): array
    {
        return $this->securitySchemes;
    }
}