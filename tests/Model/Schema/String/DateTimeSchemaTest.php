<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\Model\Schema\String;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\DateTimeSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;
use PHPUnit\Framework\TestCase;

class DateTimeSchemaTest extends TestCase
{
    public function testOk(): void
    {
        $obj = new DateTimeSchema();
        $this->assertInstanceOf(StringSchema::class, $obj);
        $this->assertSame('\\DateTimeImmutable', $obj->getPhpType());
        $this->assertSame("\$foo->format('c')", $obj->getPhpSerializationValue('$foo'));
    }
}
