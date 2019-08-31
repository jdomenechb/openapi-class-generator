<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme;


class HttpSecurityScheme extends AbstractSecurityScheme
{
    /** @var string */
    private $scheme;

    /** @var string|null */
    private $bearerFormat;

    /**
     * HttpSecurityScheme constructor.
     *
     * @param string $scheme
     * @param string|null $bearerFormat
     * @param string|null $description
     */
    public function __construct(string $scheme, ?string $bearerFormat, ?string $description)
    {
        parent::__construct($description);

        if ($scheme !== 'bearer') {
            $bearerFormat = null;
        }

        $this->scheme = $scheme;
        $this->bearerFormat = $bearerFormat;
    }


    public function type(): string
    {
        return 'http';
    }

    /**
     * @return string
     */
    public function scheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string|null
     */
    public function bearerFormat(): ?string
    {
        return $this->bearerFormat;
    }
}