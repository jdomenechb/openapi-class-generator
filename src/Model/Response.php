<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;


use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;

class Response
{
    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var string
     */
    private $description;

    /** @var MediaType[] */
    private $mediaTypes;

    /**
     * Response constructor.
     *
     * @param int|null $statusCode
     * @param string $description
     */
    public function __construct(?int $statusCode, string $description)
    {
        $this->statusCode = $statusCode;
        $this->description = $description;
        $this->mediaTypes = [];
    }

    /**
     * @return int|null
     */
    public function statusCode(): ?int
    {
        return $this->statusCode;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }

    /**
     * @param string $format
     * @param AbstractSchema $schema
     */
    public function addMediaType(string $format, ?AbstractSchema $schema): void
    {
        $this->mediaTypes[] = new MediaType($format, $schema);
    }

    /**
     * @return MediaType[]
     */
    public function mediaTypes(): array
    {
        return $this->mediaTypes;
    }
}