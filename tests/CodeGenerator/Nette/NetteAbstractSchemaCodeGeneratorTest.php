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
use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteObjectSchemaCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use PHPUnit\Framework\TestCase;

class NetteAbstractSchemaCodeGeneratorTest extends TestCase
{
    /**
     * @var NetteObjectSchemaCodeGenerator
     */
    private $objectSchemaCodeGenerator;

    /**
     * @var NetteAbstractSchemaCodeGenerator
     */
    private $obj;

    protected function setUp()
    {
        $this->objectSchemaCodeGenerator = $this->createMock(NetteObjectSchemaCodeGenerator::class);
        $this->obj = new NetteAbstractSchemaCodeGenerator($this->objectSchemaCodeGenerator);
    }

    public function testWithObject(): void
    {
        $schema = $this->createMock(ObjectSchema::class);

        $this->objectSchemaCodeGenerator->method('generate')
            ->with(
                $this->identicalTo($schema),
                $this->identicalTo('aNamespaceName'),
                $this->identicalTo('aFormat'),
                $this->identicalTo('aNamePrefix')
            )
            ->willReturn('expected');

        $result = $this->obj->generate($schema, 'aNamespaceName', 'aFormat', 'aNamePrefix');

        $this->assertSame('expected', $result);
    }

    public function testWithOther(): void
    {
        $schema = $this->createMock(AbstractSchema::class);
        $schema->method('getPhpType')->willReturn('expected');

        $result = $this->obj->generate($schema, 'aNamespaceName', 'aFormat', 'aNamePrefix');

        $this->assertSame('expected', $result);
    }
}
