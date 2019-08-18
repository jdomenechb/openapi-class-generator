<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;

use Doctrine\Common\Inflector\Inflector;

class Api
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string|null */
    private $description;

    /** @var ApiOperation[] */
    private $operations;

    /**
     * ApiService constructor.
     *
     * @param string $name
     * @param string $namespace
     * @param string|null $description
     */
    public function __construct(string $name, string $namespace = '', ?string $description = null)
    {
        $this->setName($name);
        $this->setNamespace($namespace);

        $this->description = $description;
        $this->operations = [];
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
    public function namespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $name
     */
    private function setName(string $name): void
    {
        $this->name = Inflector::classify($name);
    }

    /**
     * @param string $namespace
     */
    private function setNamespace(string $namespace): void
    {
        if (!$namespace) {
            $namespace = 'Ocg';
        }

        $this->namespace = trim($namespace, '\\');
    }

    public function addOperation(ApiOperation $operation): void
    {
        $this->operations[] = $operation;
    }

    /**
     * @return ApiOperation[]
     */
    public function operations(): array
    {
        return $this->operations;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }
}