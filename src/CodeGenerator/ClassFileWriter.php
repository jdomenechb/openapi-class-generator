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

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator;

use RuntimeException;

class ClassFileWriter
{
    /** @var string */
    private $outputPath;

    /**
     * ClassFileWriter constructor.
     *
     * @param string $outputPath
     */
    public function __construct(string $outputPath)
    {
        $this->outputPath = $outputPath;
    }

    public function write(string $content, string $fileName, string $namespace): void
    {
        $namespacePath = $this->outputPath . DIRECTORY_SEPARATOR . \str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        /* @noinspection MkdirRaceConditionInspection */
        if (!\is_dir($namespacePath) && !\mkdir($namespacePath, 0755, true)) {
            if (!\is_dir($namespacePath)) {
                throw new RuntimeException(\sprintf('Directory "%s" was not created', $namespacePath));
            }
        }

        \file_put_contents($namespacePath . DIRECTORY_SEPARATOR . $fileName . '.php', $content);
    }
}
