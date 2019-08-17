<?php

declare(strict_types=1);

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Doctrine\Common\Inflector\Inflector;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ApiServiceCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\ApiService;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;

class NetteApiServiceCodeGenerator implements ApiServiceCodeGenerator
{
    public function generate(ApiService $apiService) :void
    {
        $namespace = new PhpNamespace($apiService->namespace());

        $classRep = new ClassType($apiService->name());
        $classRep
            ->setFinal();

        $namespace->add($classRep);

        foreach ($apiService->operations() as $operation) {
            $methodName = Inflector::camelize(preg_replace('#\W#', ' ', $operation->method() . $operation->path()));
            $classRep->addMethod($methodName)
                ->setVisibility('public');
        }

        echo $namespace;


    }

}