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
    private $schemaCodeGenerator;

    /**
     * NetteRequestBodyFormatCodeGenerator constructor.
     *
     * @param NetteAbstractSchemaCodeGenerator $schemaCodeGenerator
     */
    public function __construct(NetteAbstractSchemaCodeGenerator $schemaCodeGenerator)
    {
        $this->schemaCodeGenerator = $schemaCodeGenerator;
    }

    public function generate(PathParameter $pathParameter, Method $referenceMethod, PhpNamespace $namespace): void
    {
        $schema = $pathParameter->schema();

        if (null !== $schema) {
            $className = $this->schemaCodeGenerator->generate(
                $schema,
                $namespace->getName() . '\\Request\\Dto',
                null,
                $referenceMethod->getName() . \ucfirst($pathParameter->name() . 'Parameter')
            );
        } else {
            $className = 'string';
        }

        $referenceMethod->addParameter($pathParameter->name())
            ->setTypeHint($className)
            ->setNullable(!$pathParameter->required());

        $comment = '@param ' . $className . (!$pathParameter->required() ? '|null' : '') . ' ';
        $comment .= $pathParameter->name();

        if ($pathParameter->deprecated()) {
            // TODO: Improve triggering PHP warning
            $comment .= ' DEPRECATED.';
        }

        $comment .= ($description = $pathParameter->description()) ? ' ' . $description : '';

        $referenceMethod->addComment($comment);
    }
}
