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
    /**
     * @var AbstractSchema
     */
    private $schema;

    /**
     * ApiOperationFormat constructor.
     *
     * @param string         $format
     * @param AbstractSchema $schema
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
     * @return AbstractSchema
     */
    public function schema(): AbstractSchema
    {
        return $this->schema;
    }
}
