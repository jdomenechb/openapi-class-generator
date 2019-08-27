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

    public function __construct()
    {
        $this->formats = [];
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
}