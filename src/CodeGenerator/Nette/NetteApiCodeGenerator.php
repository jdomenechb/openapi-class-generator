<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpFile;
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
     * NetteApiCodeGenerator constructor.
     *
     * @param ClassFileWriter $fileWriter
     * @param NettePathCodeGenerator $pathCodeGenerator
     */
    public function __construct(ClassFileWriter $fileWriter, NettePathCodeGenerator $pathCodeGenerator)
    {
        $this->fileWriter = $fileWriter;
        $this->pathCodeGenerator = $pathCodeGenerator;
    }

    public function generate(Api $apiService): void
    {
        $file = new PhpFile();
        $file->setStrictTypes();

        $namespace = $file->addNamespace($apiService->namespace() . '\\' . $apiService->name());
        $namespace->addUse(ClientInterface::class);
        $namespace->addUse(ResponseInterface::class);
        $namespace->addUse(GuzzleException::class);

        $classRep = new ClassType($apiService->name() . 'Service');
        $namespace->add($classRep);

        if ($apiService->description()) {
            $classRep->addComment($apiService->description());
        }

        $classRep->addComment('@version ' . $apiService->version());

        if ($apiService->author() && $apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->author() . ' <' . $apiService->authorEmail() . '>');
        } elseif ($apiService->author()) {
            $classRep->addComment('@author ' . $apiService->author());
        } elseif ($apiService->authorEmail()) {
            $classRep->addComment('@author ' . $apiService->authorEmail());
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
            $this->pathCodeGenerator->generate($path, $classRep, $namespace);
        }

        $this->fileWriter->write((string)$file, $classRep->getName(), $namespace->getName());
    }

}