<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;

class NettePathParameterCodeGenerator
{
    /** @var NetteAbstractSchemaCodeGenerator */
    private $abstractSchemaCodeGenerator;

    /**
     * NetteRequestBodyFormatCodeGenerator constructor.
     *
     * @param NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator
     */
    public function __construct(NetteAbstractSchemaCodeGenerator $abstractSchemaCodeGenerator)
    {
        $this->abstractSchemaCodeGenerator = $abstractSchemaCodeGenerator;
    }

    public function generate(PathParameter $pathParameter, Method $referenceMethod, PhpNamespace $namespace): void
    {
        if (null !== $pathParameter->schema()) {
            $className = $this->abstractSchemaCodeGenerator->generate(
                $pathParameter->schema(),
                $namespace->getName(),
                null,
                $referenceMethod->getName() . \ucfirst($pathParameter->name())
            );
        } else {
            $className = 'string';
        }

        $referenceMethod->addParameter($pathParameter->name())
            ->setTypeHint($className)
            ->setNullable(!$pathParameter->required());

        $comment = '@param ' . $className . (!$pathParameter->required() ? '|null' : '') . ' ';
        $comment .= $pathParameter->name() . ' ';

        if ($pathParameter->deprecated()) {
            $comment .= 'DEPRECATED. ';
        }

        $comment .= $pathParameter->description() ? ' ' . $pathParameter->description() : '';

        $referenceMethod->addComment($comment);
    }
}
