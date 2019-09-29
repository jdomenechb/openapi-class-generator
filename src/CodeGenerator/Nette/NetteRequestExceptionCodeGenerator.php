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

class NetteRequestExceptionCodeGenerator
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

    public function generate(string $namespaceName): void
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($namespaceName);
        $namespace->addUse(ResponseInterface::class);
        $namespace->addUse(Exception::class);

        $classRepName = 'RequestException';

        $classRep = new ClassType($classRepName);
        $classRep->setExtends(Exception::class);
        $namespace->add($classRep);

        $classRep->addProperty('response')
            ->setVisibility('private')
            ->addComment('@var ResponseInterface');

        $classRep->addMethod('__construct')
            ->addBody('$this->response = $response;')
            ->addBody('')
            ->addBody("parent::__construct('Unexpected response', \$response->getStatusCode());")
            ->addParameter('response')
            ->setTypeHint(ResponseInterface::class);

        $classRep->addMethod('response')
            ->setReturnType(ResponseInterface::class)
            ->addBody('return $this->response;');

        $printer = new PsrPrinter();

        $this->fileWriter->write($printer->printFile($file), $classRepName, $namespaceName);
    }
}