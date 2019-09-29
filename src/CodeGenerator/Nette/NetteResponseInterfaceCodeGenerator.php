<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Exception;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Psr\Http\Message\ResponseInterface;

class NetteResponseInterfaceCodeGenerator
{
    /** @var ClassFileWriter */
    private $fileWriter;

    /**
     * NetteResponseExceptionCodeGenerator constructor.
     *
     * @param ClassFileWriter $fileWriter
     */
    public function __construct(ClassFileWriter $fileWriter)
    {
        $this->fileWriter = $fileWriter;
    }

    public function generate(string $namespaceName)
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($namespaceName);

        $classRepName = 'ResponseInterface';

        $classRep = new ClassType($classRepName);
        $classRep->setType('interface');
        $namespace->add($classRep);

        $classRep->addMethod('content')
            ->setVisibility('public')
            ->addComment('@return mixed');

        $printer = new PsrPrinter();

        $this->fileWriter->write($printer->printFile($file), $classRepName, $namespaceName);
    }
}