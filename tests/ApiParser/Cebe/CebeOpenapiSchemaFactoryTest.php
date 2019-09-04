<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\ApiParser\Cebe;

use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\BooleanSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\IntegerSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\Number\FloatSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\Number\NumberSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\ObjectSchemaProperty;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\DateTimeSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\EmailSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\PasswordSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\UriSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\VectorSchema;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CebeOpenapiSchemaFactoryTest extends TestCase
{
    /** @var CebeOpenapiSchemaFactory */
    private $obj;

    protected function setUp(): void
    {
        $this->obj = new CebeOpenapiSchemaFactory();
    }

    public function testInvalid(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Schema type "invalid" not recognized');

        $schema = new Schema(['type' => 'invalid']);

        $this->obj->build($schema, 'any');
    }

    public function testBoolean(): void
    {
        $schema = new Schema(['type' => 'boolean']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(BooleanSchema::class, $result);
    }

    public function testInteger(): void
    {
        $schema = new Schema(['type' => 'integer']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(IntegerSchema::class, $result);
    }

    public function testArrayWithNoItems(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Property "items" is required for schema with type array');

        $schema = new Schema(['type' => 'array']);
        $this->obj->build($schema, 'any');

    }

    public function testString(): void
    {
        $schema = new Schema(['type' => 'string']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(StringSchema::class, $result);
    }

    public function testStringWithEmailFormat(): void
    {
        $schema = new Schema(['type' => 'string', 'format' => 'email']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(EmailSchema::class, $result);
    }

    public function testStringWithPasswordFormat(): void
    {
        $schema = new Schema(['type' => 'string', 'format' => 'password']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(PasswordSchema::class, $result);
    }

    public function testStringWithDateTimeFormat(): void
    {
        $schema = new Schema(['type' => 'string', 'format' => 'date-time']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(DateTimeSchema::class, $result);
    }

    public function testStringWithUriFormat(): void
    {
        $schema = new Schema(['type' => 'string', 'format' => 'uri']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(UriSchema::class, $result);
    }

    public function testStringWithInvalidFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('String schema format "invalid" not recognized');

        $schema = new Schema(['type' => 'string', 'format' => 'invalid']);
        $this->obj->build($schema, 'any');
    }

    public function testNumber(): void
    {
        $schema = new Schema(['type' => 'number']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(NumberSchema::class, $result);
    }

    public function testNumberWithFloatFormat(): void
    {
        $schema = new Schema(['type' => 'number', 'format' => 'float']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(FloatSchema::class, $result);
    }

    public function testNumberWithDoubleFormat(): void
    {
        $schema = new Schema(['type' => 'number', 'format' => 'double']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(FloatSchema::class, $result);
    }

    public function testNumberWithInvalidFormat(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Number schema format "invalid" not recognized');

        $schema = new Schema(['type' => 'number', 'format' => 'invalid']);
        $this->obj->build($schema, 'any');
    }

    public function testObjectWithNoProperties(): void
    {
        $schema = new Schema(['type' => 'object']);
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(ObjectSchema::class, $result);
    }

    public function testObject(): void
    {
        $schema = new Schema(
            [
                'type' => 'object',
                'properties' => [
                    'name' => new Schema(['type' => 'string']),
                    'age' => new Schema(['type' => 'integer']),
                ],
                'required' => ['age']
            ]
        );

        /** @var ObjectSchema $result */
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(ObjectSchema::class, $result);

        [$resultProp1, $resultProp2] = $result->properties();

        $this->assertInstanceOf(ObjectSchemaProperty::class, $resultProp1);
        $this->assertSame('name', $resultProp1->name());
        $this->assertFalse($resultProp1->required());

        $this->assertInstanceOf(ObjectSchemaProperty::class, $resultProp2);
        $this->assertSame('age', $resultProp2->name());
        $this->assertTrue($resultProp2->required());
    }

    public function testArray(): void
    {
        $schema = new Schema(['type' => 'array', 'items' => new Schema(['type' => 'integer'])]);

        /** @var VectorSchema $result */
        $result = $this->obj->build($schema, 'any');

        $this->assertInstanceOf(VectorSchema::class, $result);
        $this->assertInstanceOf(IntegerSchema::class, $result->wrapped());
    }
}
