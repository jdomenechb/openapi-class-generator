<?php

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
use RuntimeException;

class PathParameter
{
    private const VALID_IN = ['query', 'path'];

    /** @var string */
    private $name;

    /** @var string */
    private $in;

    /** @var string|null */
    private $description;

    /** @var bool */
    private $required;

    /** @var bool */
    private $deprecated;

    /** @var AbstractSchema|null */
    private $schema;

    /**
     * PathParameter constructor.
     *
     * @param string              $name
     * @param string              $in
     * @param string|null         $description
     * @param bool                $required
     * @param bool                $deprecated
     * @param AbstractSchema|null $schema
     */
    public function __construct(
        string $name,
        string $in,
        ?string $description,
        bool $required,
        bool $deprecated,
        ?AbstractSchema $schema
    ) {
        $this->name = $name;
        $this->setIn($in);
        $this->description = $description;
        $this->required = $required;
        $this->deprecated = $deprecated;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function in(): string
    {
        return $this->in;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return bool
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * @return bool
     */
    public function deprecated(): bool
    {
        return $this->deprecated;
    }

    /**
     * @return AbstractSchema|null
     */
    public function schema(): ?AbstractSchema
    {
        return $this->schema;
    }

    /**
     * @param string $in
     */
    private function setIn(string $in): void
    {
        if (!\in_array($in, self::VALID_IN, true)) {
            throw new RuntimeException('Invalid parameter in: ' . $in);
        }

        $this->in = $in;
    }
}
