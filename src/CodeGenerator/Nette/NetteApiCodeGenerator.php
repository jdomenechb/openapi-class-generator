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
use RuntimeException;
use function count;

class NetteApiCodeGenerator implements ApiCodeGenerator
{
    /** @var ClassFileWriter */
    private $fileWriter;
    /**
     * @var NetteRequestBodyFormatCodeGenerator
     */
    private $apiOperationFormatGenerator;

    /**
     * NetteApiCodeGenerator constructor.
     *
     * @param NetteRequestBodyFormatCodeGenerator $apiOperationFormatGenerator
     * @param ClassFileWriter $fileWriter
     */
    public function __construct(
        NetteRequestBodyFormatCodeGenerator $apiOperationFormatGenerator,
        ClassFileWriter $fileWriter
    )
    {
        $this->fileWriter = $fileWriter;
        $this->apiOperationFormatGenerator = $apiOperationFormatGenerator;
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

        foreach ($apiService->operations() as $operation) {
            $referenceMethodName = $operation->method() . $operation->path();

            $requestBody = $operation->requestBody();
            $nFormats = $requestBody ? \count($requestBody->formats()) : 0;

            if ($nFormats === 0) {
                $this->apiOperationFormatGenerator->generate($classRep, $namespace, $operation);
            } else {

                foreach ($requestBody->formats() as $format) {
                    $this->apiOperationFormatGenerator->generate(
                        $classRep,
                        $namespace,
                        $operation,
                        $format,
                        $nFormats > 1
                    );
                }
            }
        }

        $this->fileWriter->write((string)$file, $classRep->getName(), $namespace->getName());
    }

}