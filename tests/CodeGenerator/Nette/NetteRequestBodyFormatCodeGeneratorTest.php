<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\CodeGenerator\Nette;

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteAbstractSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteGuzzleBodyCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestBodyFormatCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\MediaType;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\TestCase;

class NetteRequestBodyFormatCodeGeneratorTest extends TestCase
{
    /** @var NetteAbstractSchemaCodeGenerator */
    private $abstractSchemaCodeGenerator;

    /**
     * @var NetteGuzzleBodyCodeGenerator
     */
    private $guzzleBodyCodeGenerator;

    /**
     * @var NetteRequestBodyFormatCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->abstractSchemaCodeGenerator = $this->createMock(NetteAbstractSchemaCodeGenerator::class);
        $this->guzzleBodyCodeGenerator = $this->createMock(NetteGuzzleBodyCodeGenerator::class);

        $this->obj = new NetteRequestBodyFormatCodeGenerator(
            $this->abstractSchemaCodeGenerator,
            $this->guzzleBodyCodeGenerator
        );
    }

    public function testWithoutRequestBody(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Expected requestBody');

        $method = new Method('aMethod');
        $namespace = new PhpNamespace('aNamespace');
        $path = new Path('post', '/a/path', null, null, null, null, [], []);
        $format = new MediaType('json', $this->createMock(AbstractSchema::class));

        $this->obj->generate($method, $namespace, $path, $format);
    }

    public function testOkWithoutRequiredNorDescription(): void
    {
        $this->abstractSchemaCodeGenerator->method('generate')->willReturn('aTypeHint');

        $requestBody = new RequestBody(null, false);

        $format = new MediaType('json', $this->createMock(AbstractSchema::class));
        $requestBody->addFormat($format);

        $method = new Method('aMethod');
        $namespace = new PhpNamespace('aNamespace');
        $path = new Path('post', '/a/path', null, null, null, $requestBody, [], []);

        $this->guzzleBodyCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo($method), $this->identicalTo($path), $this->identicalTo($format->format()));

        $this->obj->generate($method, $namespace, $path, $format);

        $parameters = $method->getParameters();

        $this->assertSame('@param aTypeHint|null $requestBody', $method->getComment());
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('requestBody', $parameters);

        $parameter = $parameters['requestBody'];

        $this->assertSame('aTypeHint', $parameter->getTypeHint());
        $this->assertTrue($parameter->isNullable());
    }

    public function testOkWithRequiredAndDescription(): void
    {
        $this->abstractSchemaCodeGenerator->method('generate')->willReturn('aTypeHint');

        $requestBody = new RequestBody('aDescription', true);

        $format = new MediaType('json', $this->createMock(AbstractSchema::class));
        $requestBody->addFormat($format);

        $method = new Method('aMethod');
        $namespace = new PhpNamespace('aNamespace');
        $path = new Path('post', '/a/path', null, null, null, $requestBody, [], []);

        $this->guzzleBodyCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->identicalTo($method), $this->identicalTo($path), $this->identicalTo($format->format()));

        $this->obj->generate($method, $namespace, $path, $format);

        $parameters = $method->getParameters();

        $this->assertSame('@param aTypeHint $requestBody aDescription', $method->getComment());
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('requestBody', $parameters);

        $parameter = $parameters['requestBody'];

        $this->assertSame('aTypeHint', $parameter->getTypeHint());
        $this->assertFalse($parameter->isNullable());
    }
}
