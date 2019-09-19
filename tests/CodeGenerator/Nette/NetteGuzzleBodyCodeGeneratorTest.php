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
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Nette\PhpGenerator\Method;
use PHPUnit\Framework\TestCase;

class NetteGuzzleBodyCodeGeneratorTest extends TestCase
{
    /**
     * @var NetteGuzzleBodyCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->obj = new NetteGuzzleBodyCodeGenerator();
    }

    public function testOkWithNoFormat(): void
    {
        $method = new Method('aMethod');
        $path = new Path('put', '/a/path', null, null, null, [], []);

        $this->obj->generate($method, $path, null);

        $this->compareExpectedResult(__FUNCTION__, $method);
    }

    public function testOkWithJson(): void
    {
        $method = new Method('aMethod');
        $path = new Path('put', '/a/path', null, null, null, [], []);

        $this->obj->generate($method, $path, 'json');

        $this->compareExpectedResult(__FUNCTION__, $method);
    }

    public function testOkWithForm(): void
    {
        $method = new Method('aMethod');
        $path = new Path('put', '/a/path', null, null, null, [], []);

        $this->obj->generate($method, $path, 'form');

        $this->compareExpectedResult(__FUNCTION__, $method);
    }

    public function testInvalidFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unrecognized format: INVALID');

        $method = new Method('aMethod');
        $path = new Path('put', '/a/path', null, null, null, [], []);

        $this->obj->generate($method, $path, 'INVALID');
    }

    private function compareExpectedResult(string $name, Method $method): void
    {
        $expectedResult = \file_get_contents(__DIR__ . '/NetteGuzzleBodyCodeGeneratorTest_resources/' . $name . '.txt');

        $this->assertSame($expectedResult, (string) $method);
    }
}
