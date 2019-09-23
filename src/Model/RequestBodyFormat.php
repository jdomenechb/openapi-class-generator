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

use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;

class RequestBodyFormat
{
    /** @var string */
    private $format;

    /** @var string|null */
    private $operationId;

    /**
     * @var AbstractSchema
     */
    private $schema;

    /**
     * ApiOperationFormat constructor.
     *
     * @param string         $format
     * @param string|null    $operationId
     * @param AbstractSchema $schema
     */
    public function __construct(string $format, ?string $operationId, AbstractSchema $schema)
    {
        $this->format = $format;
        $this->schema = $schema;
        $this->operationId = $operationId;
    }

    /**
     * @return string
     */
    public function format(): string
    {
        return $this->format;
    }

    /**
     * @return string|null
     */
    public function operationId(): ?string
    {
        return $this->operationId;
    }

    /**
     * @return AbstractSchema
     */
    public function schema(): AbstractSchema
    {
        return $this->schema;
    }
}
