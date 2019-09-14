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

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteApiCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NettePathCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Api;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use PHPUnit\Framework\TestCase;

class NetteApiCodeGeneratorTest extends TestCase
{
    /** @var ClassFileWriter */
    private $fileWriter;

    /**
     * @var NettePathCodeGenerator
     */
    private $pathCodeGenerator;

    /**
     * @var NetteApiCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->fileWriter = $this->createMock(ClassFileWriter::class);
        $this->pathCodeGenerator = $this->createMock(NettePathCodeGenerator::class);

        $this->obj = new NetteApiCodeGenerator($this->fileWriter, $this->pathCodeGenerator);
    }

    public function testOk(): void
    {
        $this->prepareResultExpectation(__FUNCTION__);

        $api = new Api(
            'aName',
            '1.2.3'
        );

        $api->addPath($mockedPath1 = $this->createMock(Path::class));
        $api->addPath($mockedPath2 = $this->createMock(Path::class));

        $this->pathCodeGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->withConsecutive(
                [$this->anything(), $this->anything(), $this->identicalTo($mockedPath1)],
                [$this->anything(), $this->anything(), $this->identicalTo($mockedPath2)]
            );

        $this->obj->generate($api);
    }

    public function testDescription(): void
    {
        $this->prepareResultExpectation(__FUNCTION__);

        $api = new Api(
            'aName',
            '1.2.3',
            '',
            'aDescription'
        );

        $this->obj->generate($api);
    }

    public function testAuthor(): void
    {
        $this->prepareResultExpectation(__FUNCTION__);

        $api = new Api(
            'aName',
            '1.2.3',
            '',
            null,
            'anAuthor'
        );

        $this->obj->generate($api);
    }

    public function testAuthorEmail(): void
    {
        $this->prepareResultExpectation(__FUNCTION__);

        $api = new Api(
            'aName',
            '1.2.3',
            '',
            null,
            null,
            'anAuthor@email.com'
        );

        $this->obj->generate($api);
    }

    public function testAuthorAndAuthorEmail(): void
    {
        $this->prepareResultExpectation(__FUNCTION__);

        $api = new Api(
            'aName',
            '1.2.3',
            '',
            null,
            'anAuthorName',
            'anAuthor@email.com'
        );

        $this->obj->generate($api);
    }

    private function prepareResultExpectation(string $name): void
    {
        $expectedResult = \file_get_contents(__DIR__ . '/NetteApiCodeGeneratorTest_resources/' . $name . '.txt');

        $this->fileWriter
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    static function ($param) use ($expectedResult) {
                        return $param === $expectedResult;
                    }
                )
            );
    }
}
