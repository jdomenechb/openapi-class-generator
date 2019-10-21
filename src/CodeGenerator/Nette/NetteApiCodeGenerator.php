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

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Exception;
use GuzzleHttp\ClientInterface;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PsrPrinter;
use Psr\Http\Message\ResponseInterface;

class NetteApiCodeGenerator implements ApiCodeGenerator
{
    /** @var ClassFileWriter */
    private $fileWriter;

    /**
     * @var NettePathCodeGenerator
     */
    private $pathCodeGenerator;

    /**
     * @var NetteRequestExceptionCodeGenerator
     */
    private $requestExceptionCodeGenerator;

    /**
     * @var NetteResponseInterfaceCodeGenerator
     */
    private $responseInterfaceCodeGenerator;

    /**
     * NetteApiCodeGenerator constructor.
     *
     * @param ClassFileWriter $fileWriter
     * @param NettePathCodeGenerator $pathCodeGenerator
     * @param NetteRequestExceptionCodeGenerator $requestExceptionCodeGenerator
     * @param NetteResponseInterfaceCodeGenerator $responseInterfaceCodeGenerator
     */
    public function __construct(ClassFileWriter $fileWriter, NettePathCodeGenerator $pathCodeGenerator, NetteRequestExceptionCodeGenerator $requestExceptionCodeGenerator, NetteResponseInterfaceCodeGenerator $responseInterfaceCodeGenerator)
    {
        $this->fileWriter = $fileWriter;
        $this->pathCodeGenerator = $pathCodeGenerator;
        $this->requestExceptionCodeGenerator = $requestExceptionCodeGenerator;
        $this->responseInterfaceCodeGenerator = $responseInterfaceCodeGenerator;
    }

    /**
     * @param Api $apiService
     *
     * @throws Exception
     */
    public function generate(Api $apiService): void
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($apiService->namespace() . '\\' . $apiService->name());
        $namespace->addUse(ClientInterface::class);
        $namespace->addUse(ResponseInterface::class);

        $classRepName = $apiService->name() . 'Service';

        $classRep = new ClassType($classRepName);
        $namespace->add($classRep);

        $description = $apiService->description();

        if ($description) {
            $classRep->addComment($description);
        }

        $classRep->addComment('@version ' . $apiService->version());

        $author = $apiService->author();
        $authorEmail = $apiService->authorEmail();

        if ($author && $authorEmail) {
            $classRep->addComment('@author ' . $author . ' <' . $authorEmail . '>');
        } elseif ($author) {
            $classRep->addComment('@author ' . $author);
        } elseif ($authorEmail) {
            $classRep->addComment('@author ' . $authorEmail);
        }

        $classRep->addComment('@api');
        $classRep->setFinal();

        $classRep->addProperty('client')
            ->setVisibility('private')
            ->addComment('@var ClientInterface');

        $construct = $classRep->addMethod('__construct')
            ->addBody('$this->client = $client;');

        $construct->addParameter('client')
            ->setTypeHint(ClientInterface::class);

        foreach ($apiService->paths() as $path) {
            $this->pathCodeGenerator->generate($classRep, $namespace, $path);
        }

        $printer = new PsrPrinter();

        $this->fileWriter->write($printer->printFile($file), $classRepName, $namespace->getName());

        $this->requestExceptionCodeGenerator->generate($namespace->getName() . '\\Exception');
        $this->responseInterfaceCodeGenerator->generate($namespace->getName() . '\\Response');
    }
}
