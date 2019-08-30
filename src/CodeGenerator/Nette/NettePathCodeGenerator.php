<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;


use Doctrine\Common\Inflector\Inflector;
use Exception;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use Psr\Http\Message\ResponseInterface;
use function count;

class NettePathCodeGenerator
{
    /**
     * @var NetteRequestBodyFormatCodeGenerator
     */
    private $requestBodyFormatCodeGenerator;

    /**
     * @var NettePathParameterCodeGenerator
     */
    private $pathParameterCodeGenerator;
    /**
     * @var NetteGuzzleBodyCodeGenerator
     */
    private $guzzleBodyCodeGenerator;

    /**
     * NettePathCodeGenerator constructor.
     *
     * @param NetteRequestBodyFormatCodeGenerator $requestBodyFormatCodeGenerator
     * @param NettePathParameterCodeGenerator $pathParameterCodeGenerator
     * @param NetteGuzzleBodyCodeGenerator $guzzleBodyCodeGenerator
     */
    public function __construct(
        NetteRequestBodyFormatCodeGenerator $requestBodyFormatCodeGenerator,
        NettePathParameterCodeGenerator $pathParameterCodeGenerator,
        NetteGuzzleBodyCodeGenerator $guzzleBodyCodeGenerator
    ) {
        $this->requestBodyFormatCodeGenerator = $requestBodyFormatCodeGenerator;
        $this->pathParameterCodeGenerator = $pathParameterCodeGenerator;
        $this->guzzleBodyCodeGenerator = $guzzleBodyCodeGenerator;
    }


    /**
     * @param ClassType $classRep
     * @param PhpNamespace $namespace
     * @param Path $path
     *
     * @throws Exception
     */
    public function generate(
        ClassType $classRep,
        PhpNamespace $namespace,
        Path $path
    ): void {
        $referenceMethodName = 'REFERENCEMETHOD' . random_int(1000, 9999);

        $referenceMethod = $classRep->addMethod($referenceMethodName)
            ->setVisibility('public')
            ->setReturnType(ResponseInterface::class);

        if ($path->description()) {
            $referenceMethod->addComment($path->description());
            $referenceMethod->addComment('');
        }

        if ($path->summary()) {
            $referenceMethod->addComment($path->summary());
            $referenceMethod->addComment('');
        }

        $referenceMethod
            ->addComment('Endpoint URL: ' . $path->path())
            ->addComment('Method: ' . strtoupper($path->method()))
            ->addComment('')
            ->addComment('@return ResponseInterface')
            ->addComment('@throws GuzzleException')
        ;

        foreach ($path->parameters() as $parameter) {
            $this->pathParameterCodeGenerator->generate($parameter, $referenceMethod, $namespace);
        }

        $requestBody = $path->requestBody();

        if (!$requestBody) {
            $this->generateWithNoFormats($classRep, $referenceMethod, $path);
        } else {
            $nFormats = count($requestBody->formats());

            if ($nFormats === 0) {
                $this->generateWithNoFormats($classRep, $referenceMethod, $path);
            } else {
                foreach ($requestBody->formats() as $format) {
                    $this->generateWithFormat($classRep, $referenceMethod, $namespace, $path, $format);
                }
            }
        }

        $classRep->removeMethod($referenceMethodName);
    }

    /**
     * @param ClassType $classRep
     * @param Method $referenceMethod
     * @param Path $path
     */
    private function generateWithNoFormats(ClassType $classRep, Method $referenceMethod, Path $path): void
    {
        $methodName = $path->method() . $path->path();
        $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $methodName));

        $method = $referenceMethod->cloneWithName($methodName);
        $classRep->setMethods($classRep->getMethods() + [$method]);

        $this->guzzleBodyCodeGenerator->generate($method, $path, null);
    }

    /**
     * @param ClassType $classRep
     * @param Method $referenceMethod
     * @param PhpNamespace $namespace
     * @param Path $path
     * @param RequestBodyFormat $format
     */
    private function generateWithFormat(
        ClassType $classRep,
        Method $referenceMethod,
        PhpNamespace $namespace,
        Path $path,
        RequestBodyFormat $format
    ): void {
        $methodName = $path->method() . $path->path();
        $requestBody = $path->requestBody();

        if ($requestBody && count($requestBody->formats()) > 1) {
            $methodName .= ' ' . $format->format();
        }

        $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $methodName));

        $method = $referenceMethod->cloneWithName($methodName);
        $classRep->setMethods($classRep->getMethods() + [$method]);

        $this->requestBodyFormatCodeGenerator->generate(
            $method,
            $namespace,
            $path,
            $format
        );
    }
}