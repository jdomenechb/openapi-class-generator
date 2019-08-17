<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use function count;

class NetteApiCodeGenerator implements ApiCodeGenerator
{
    public function generate(Api $apiService) :void
    {
        $namespace = new PhpNamespace($apiService->namespace());

        $classRep = new ClassType($apiService->name());
        $classRep->setFinal();

        $namespace->add($classRep);

        foreach ($apiService->operations() as $operation) {
            $referenceMethodName = $operation->method() . $operation->path();
            $formats = $operation->formats();
            $nFormats = count($formats);

            foreach ($formats as $format) {
                $methodName = $referenceMethodName;

                if ($nFormats > 1) {
                    $methodName .= ' ' . $format->format();
                }

                $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $methodName));
                $classRep->addMethod($methodName)
                    ->setVisibility('public');
            }
        }

        echo $namespace;
    }

}