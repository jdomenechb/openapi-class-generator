<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\Model\Schema\String;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\PasswordSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;
use PHPUnit\Framework\TestCase;

class PasswordSchemaTest extends TestCase
{
    public function testOk() :void
    {
        $obj = new PasswordSchema();
        $this->assertInstanceOf(StringSchema::class, $obj);
    }
}
