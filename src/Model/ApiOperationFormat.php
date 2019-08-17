<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;


class ApiOperationFormat
{
    /** @var string */
    private $format;

    /**
     * ApiOperationFormat constructor.
     *
     * @param string $format
     */
    public function __construct(string $format)
    {
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->format;
    }
}