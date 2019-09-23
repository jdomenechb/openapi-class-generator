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

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteGuzzleBodyCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathParameterCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteRequestBodyFormatCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteSecuritySchemeCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBody;
use Jdomenechb\OpenApiClassGenerator\Model\RequestBodyFormat;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class NettePathCodeGeneratorTest extends TestCase
{
    /**
     * @var NetteRequestBodyFormatCodeGenerator|MockObject
     */
    private $requestBodyFormatCodeGenerator;

    /**
     * @var NettePathParameterCodeGenerator|MockObject
     */
    private $pathParameterCodeGenerator;

    /**
     * @var NetteGuzzleBodyCodeGenerator|MockObject
     */
    private $guzzleBodyCodeGenerator;

    /**
     * @var NetteSecuritySchemeCodeGenerator|MockObject
     */
    private $securitySchemeCodeGenerator;

    /**
     * @var NettePathCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->requestBodyFormatCodeGenerator = $this->createMock(NetteRequestBodyFormatCodeGenerator::class);
        $this->pathParameterCodeGenerator = $this->createMock(NettePathParameterCodeGenerator::class);
        $this->guzzleBodyCodeGenerator = $this->createMock(NetteGuzzleBodyCodeGenerator::class);
        $this->securitySchemeCodeGenerator = $this->createMock(NetteSecuritySchemeCodeGenerator::class);

        $this->obj = new NettePathCodeGenerator(
            $this->requestBodyFormatCodeGenerator,
            $this->pathParameterCodeGenerator,
            $this->guzzleBodyCodeGenerator,
            $this->securitySchemeCodeGenerator
        );
    }

    public function testOkGenerateWithNoFormats(): void
    {
        $class = new ClassType('AClass');

        $mockedSecScheme1 = $this->createMock(AbstractSecurityScheme::class);
        $mockedSecScheme2 = $this->createMock(AbstractSecurityScheme::class);

        $securitySchemes = [
            $mockedSecScheme1,
            $mockedSecScheme2,
        ];

        $mockedParameter1 = $this->createMock(PathParameter::class);
        $mockedParameter2 = $this->createMock(PathParameter::class);

        $parameters = [
            $mockedParameter1,
            $mockedParameter2,
        ];

        $path = new Path('post', '/a/path', null, null, null, $parameters, $securitySchemes);
        $namespace = new PhpNamespace('A\\Namespace');

        $this->securitySchemeCodeGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                [$this->identicalTo($mockedSecScheme1), $this->anything()],
                [$this->identicalTo($mockedSecScheme2), $this->anything()]
            );

        $this->pathParameterCodeGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                [$this->identicalTo($mockedParameter1), $this->anything(), $this->identicalTo($namespace)],
                [$this->identicalTo($mockedParameter2), $this->anything(), $this->identicalTo($namespace)]
            );

        $this->guzzleBodyCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->anything(), $this->identicalTo($path), $this->identicalTo(null));

        $this->obj->generate($class, $namespace, $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testOkGenerateWithRequestBodyWithNoFormats(): void
    {
        $class = new ClassType('AClass');
        $requestBody = new RequestBody('aRBDescription', false);

        $path = new Path('post', '/a/path', null, null, $requestBody, [], []);
        $namespace = new PhpNamespace('A\\Namespace');

        $this->guzzleBodyCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->anything(), $this->identicalTo($path), $this->identicalTo(null));

        $this->obj->generate($class, $namespace, $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testOkGenerateWithRequestBodyWithOneFormat(): void
    {
        $class = new ClassType('AClass');
        $requestBody = new RequestBody('aRBDescription', false);

        $format1 = new RequestBodyFormat('json', null, $this->createMock(AbstractSchema::class));
        $requestBody->addFormat($format1);

        $path = new Path('post', '/a/path', null, null, $requestBody, [], []);
        $namespace = new PhpNamespace('A\\Namespace');

        $this->requestBodyFormatCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->anything(), $this->identicalTo($namespace), $this->identicalTo($path), $this->identicalTo($format1));

        $this->obj->generate($class, $namespace, $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testOkGenerateWithRequestBodyWithOneFormatWithOpId(): void
    {
        $class = new ClassType('AClass');
        $requestBody = new RequestBody('aRBDescription', false);

        $format1 = new RequestBodyFormat('json', 'an operation id', $this->createMock(AbstractSchema::class));
        $requestBody->addFormat($format1);

        $path = new Path('post', '/a/path', null, null, $requestBody, [], []);
        $namespace = new PhpNamespace('A\\Namespace');

        $this->requestBodyFormatCodeGenerator
            ->expects($this->once())
            ->method('generate')
            ->with($this->anything(), $this->identicalTo($namespace), $this->identicalTo($path), $this->identicalTo($format1));

        $this->obj->generate($class, $namespace, $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testOkGenerateWithRequestBodyWithManyFormats(): void
    {
        $class = new ClassType('AClass');
        $requestBody = new RequestBody('aRBDescription', false);

        $format1 = new RequestBodyFormat('json', 'a json id', $this->createMock(AbstractSchema::class));
        $format2 = new RequestBodyFormat('form', null, $this->createMock(AbstractSchema::class));
        $requestBody->addFormat($format1);
        $requestBody->addFormat($format2);

        $path = new Path('post', '/a/path', null, null, $requestBody, [], []);
        $namespace = new PhpNamespace('A\\Namespace');

        $this->requestBodyFormatCodeGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                [$this->anything(), $this->identicalTo($namespace), $this->identicalTo($path), $this->identicalTo($format1)],
                [$this->anything(), $this->identicalTo($namespace), $this->identicalTo($path), $this->identicalTo($format2)]
            );

        $this->obj->generate($class, $namespace, $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testGenerateWithDescription(): void
    {
        $class = new ClassType('AClass');
        $path = new Path('post', '/a/path', null, 'aDescription', null, [], []);

        $this->obj->generate($class, new PhpNamespace('A\\Namespace'), $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testGenerateWithSummary(): void
    {
        $class = new ClassType('AClass');
        $path = new Path('post', '/a/path', 'aSummary', null, null, [], []);

        $this->obj->generate($class, new PhpNamespace('A\\Namespace'), $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    public function testGenerateWithSummaryAndDescription(): void
    {
        $class = new ClassType('AClass');
        $path = new Path('post', '/a/path', 'aSummary', 'aDescription', null, [], []);

        $this->obj->generate($class, new PhpNamespace('A\\Namespace'), $path);

        $this->compareExpectedResult(__FUNCTION__, $class);
    }

    private function compareExpectedResult(string $name, ClassType $class): void
    {
        $expectedResult = \file_get_contents(__DIR__ . '/NettePathCodeGeneratorTest_resources/' . $name . '.txt');

        $this->assertSame($expectedResult, (string) $class);
    }
}
