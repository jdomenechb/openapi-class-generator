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

use Doctrine\Common\Inflector\Inflector;

class Api
{
    /** @var string */
    private $name;

    /** @var string */
    private $namespace;

    /** @var string|null */
    private $description;

    /** @var Path[] */
    private $paths;

    /**
     * @var string
     */
    private $version;
    /**
     * @var string|null
     */
    private $author;
    /**
     * @var string|null
     */
    private $authorEmail;

    /**
     * ApiService constructor.
     *
     * @param string      $name
     * @param string      $version
     * @param string      $namespace
     * @param string|null $description
     * @param string|null $author
     * @param string|null $authorEmail
     */
    public function __construct(string $name, string $version, string $namespace = '', ?string $description = null, ?string $author = null, ?string $authorEmail = null)
    {
        $this->setName($name);
        $this->setNamespace($namespace);

        $this->version = $version;
        $this->description = $description;
        $this->paths = [];
        $this->author = $author;
        $this->authorEmail = $authorEmail;
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

    public function addPath(Path $path): void
    {
        $this->paths[] = $path;
    }

    /**
     * @return Path[]
     */
    public function paths(): array
    {
        return $this->paths;
    }

    /**
     * @return string|null
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function version(): string
    {
        return $this->version;
    }

    /**
     * @return string|null
     */
    public function author(): ?string
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function authorEmail(): ?string
    {
        return $this->authorEmail;
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

        $this->namespace = \trim($namespace, '\\');
    }
}
