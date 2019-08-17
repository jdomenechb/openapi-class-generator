<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Model;

use Doctrine\Common\Inflector\Inflector;

class ApiService
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var ApiOperation[] */
    private $operations;

    /**
     * ApiService constructor.
     *
     * @param string $name
     * @param string $namespace
     */
    public function __construct(string $name, string $namespace)
    {
        $this->setName($name);
        $this->setNamespace($namespace);

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
        $this->namespace = trim($namespace, '\\');
    }

    public function addOperation(string $method, string $path): void
    {
        $this->operations[] = new ApiOperation($method, $path);
    }

    /**
     * @return ApiOperation[]
     */
    public function operations(): array
    {
        return $this->operations;
    }


}