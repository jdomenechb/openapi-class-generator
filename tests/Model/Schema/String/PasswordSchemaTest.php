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

use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\PasswordSchema;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\String\StringSchema;
use PHPUnit\Framework\TestCase;

class PasswordSchemaTest extends TestCase
{
    public function testOk(): void
    {
        $obj = new PasswordSchema();
        $this->assertInstanceOf(StringSchema::class, $obj);
    }
}
