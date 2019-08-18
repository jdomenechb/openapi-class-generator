<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Nette\PhpGenerator\PhpFile;
use RuntimeException;

class NettePhpFileWriter
{
    public function write(PhpFile $file, string $fileName, string $outputPath, string $namespace): void
    {
        $namespacePath = $outputPath . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $namespace);

        if (!mkdir($namespacePath, 0755, true) && !is_dir($namespacePath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $namespacePath));
        }

        file_put_contents($namespacePath . DIRECTORY_SEPARATOR . $fileName . '.php', $file);
    }
}