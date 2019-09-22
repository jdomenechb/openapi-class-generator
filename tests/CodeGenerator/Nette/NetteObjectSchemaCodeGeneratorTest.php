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
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteObjectSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\UriSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\VectorSchema;
use PHPUnit\Framework\TestCase;

class NetteObjectSchemaCodeGeneratorTest extends TestCase
{
    /**
     * @var ClassFileWriter
     */
    private $fileWriter;

    /**
     * @var NetteObjectSchemaCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->fileWriter = $this->createMock(ClassFileWriter::class);
        $this->obj = new NetteObjectSchemaCodeGenerator($this->fileWriter);
    }

    public function testOkWithoutPropertiesNorFormat(): void
    {
        $this->setupClassResultExpectation(__FUNCTION__);

        $objectSchema = new ObjectSchema('anObject');

        $objNamespace = $this->obj->generate($objectSchema, 'aNamespaceName', null, 'prefix');

        $this->assertSame('\\aNamespaceName\\Dto\\PrefixAnObject', $objNamespace);
    }

    public function testOkWithJsonFormat(): void
    {
        $this->setupClassResultExpectation(__FUNCTION__);

        $objectSchema = new ObjectSchema('anObject');

        $objNamespace = $this->obj->generate($objectSchema, 'aNamespaceName', 'json');

        $this->assertSame('\\aNamespaceName\\Dto\\AnObject', $objNamespace);
    }

    public function testOkWithProperties(): void
    {
        $this->setupClassResultExpectation(__FUNCTION__);

        $objectSchema = new ObjectSchema('anObject');
        $objectSchema->addProperty(new ObjectSchemaProperty('aFirstProperty', false, new UriSchema()));
        $objectSchema->addProperty(
            new ObjectSchemaProperty('aSecondProperty', true, new VectorSchema(new EmailSchema()))
        );

        $objNamespace = $this->obj->generate($objectSchema, 'aNamespaceName', null);

        $this->assertSame('\\aNamespaceName\\Dto\\AnObject', $objNamespace);
    }

    public function testOkWithObjectProperty(): void
    {
        $testName = __FUNCTION__;

        $this->fileWriter
            ->expects($this->exactly(2))
            ->method('write')
            ->withConsecutive(
                [
                    $this->callback(
                        static function ($classContent) use ($testName) {
                            $expectedResult = \file_get_contents(
                                __DIR__ . '/NetteObjectSchemaCodeGeneratorTest_resources/' . $testName . '_object.txt'
                            );

                            return $expectedResult === $classContent;
                        }
                    ),

                    $this->anything(),
                    $this->anything(),
                ],
                [
                    $this->callback(
                        static function ($classContent) use ($testName) {
                            $expectedResult = \file_get_contents(
                                __DIR__ . '/NetteObjectSchemaCodeGeneratorTest_resources/' . $testName . '.txt'
                            );

                            return $expectedResult === $classContent;
                        }
                    ),

                    $this->anything(),
                    $this->anything(),
                ]
            );

        $objectSchema = new ObjectSchema('anObject');
        $objectSchema->addProperty(new ObjectSchemaProperty('aFirstProperty', false, new ObjectSchema('aSubObject')));

        $objNamespace = $this->obj->generate($objectSchema, 'aNamespaceName', null);

        $this->assertSame('\\aNamespaceName\\Dto\\AnObject', $objNamespace);
    }

    private function setupClassResultExpectation(string $testName): void
    {
        $this->fileWriter
            ->expects($this->once())
            ->method('write')
            ->with(
                $this->callback(
                    static function ($classContent) use ($testName) {
                        $expectedResult = \file_get_contents(
                            __DIR__ . '/NetteObjectSchemaCodeGeneratorTest_resources/' . $testName . '.txt'
                        );

                        return $expectedResult === $classContent;
                    }
                ),

                $this->anything(),
                $this->anything()
            );
    }
}
