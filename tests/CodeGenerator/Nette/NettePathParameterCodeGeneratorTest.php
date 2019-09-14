<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteAbstractSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathParameterCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;

class NettePathParameterCodeGeneratorTest extends TestCase
{
    /**
     * @var NetteAbstractSchemaCodeGenerator
     */
    private $schemaCodeGenerator;

    /**
     * @var NettePathParameterCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->schemaCodeGenerator = $this->createMock(NetteAbstractSchemaCodeGenerator::class);
        $this->obj = new NettePathParameterCodeGenerator($this->schemaCodeGenerator);
    }

    public function testPathParameterWithoutSchema() :void
    {
        $pathParameter = new PathParameter('aName', 'query', null, false, false, null);
        $method = new Method('aMethod');
        $namespace = new PhpNamespace('aNamespace');

        $this->obj->generate($pathParameter, $method, $namespace);

        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('aName', $parameters);

        $parameter = $parameters['aName'];

        $this->assertSame('string', $parameter->getTypeHint());
        $this->assertTrue($parameter->isNullable());
        $this->assertSame('@param string|null aName', $method->getComment());
    }

    public function testPathParameterWithSchema() :void
    {
        $mockedSchema = $this->createMock(AbstractSchema::class);

        $this->schemaCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo($mockedSchema), $this->anything(), $this->isNull(), 'aMethodAName')
            ->willReturn('A\\Class\\Name');

        $pathParameter = new PathParameter(
            'aName',
            'query',
            'aDescription',
            true,
            true,
            $mockedSchema
        );

        $method = new Method('aMethod');
        $namespace = new PhpNamespace('aNamespace');

        $this->obj->generate($pathParameter, $method, $namespace);

        $parameters = $method->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('aName', $parameters);

        $parameter = $parameters['aName'];

        $this->assertSame('A\\Class\\Name', $parameter->getTypeHint());
        $this->assertFalse($parameter->isNullable());
        $this->assertSame('@param A\\Class\\Name aName DEPRECATED. aDescription' , $method->getComment());
    }
}
