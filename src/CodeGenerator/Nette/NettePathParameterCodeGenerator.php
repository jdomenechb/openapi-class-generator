<?php


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

    public function generate(PathParameter $pathParameter, Method $referenceMethod, PhpNamespace $namespace)
    {
        if ($pathParameter->schema()) {
            $className = $this->abstractSchemaCodeGenerator->generate(
                $pathParameter->schema(),
                $namespace->getName(),
                null,
                $referenceMethod->getName() . ucfirst($pathParameter->name())
            );
        } else {
            $className = 'string';
        }

        $referenceMethod->addParameter($pathParameter->name())
            ->setTypeHint($className)
            ->setNullable(!$pathParameter->required());

        $referenceMethod->addComment(
            '@param ' . $className . (!$pathParameter->required() ? '|null' : '') . ' ' . $pathParameter->name(
            ) . ($pathParameter->description() ? ' ' . $pathParameter->description() : '')
        );
    }
}