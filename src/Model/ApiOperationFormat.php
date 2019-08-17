<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;


use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;

class ApiOperationFormat
{
    /** @var string */
    private $format;
    /**
     * @var \Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema
     */
    private $schema;


    /**
     * ApiOperationFormat constructor.
     *
     * @param string $format
     * @param \Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema $schema
     */
    public function __construct(string $format, AbstractSchema $schema)
    {
        $this->format = $format;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->format;
    }

    /**
     * @return \Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema
     */
    public function schema(): AbstractSchema
    {
        return $this->schema;
    }


}