<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\Model\Schema;

use Jdomenechb\OpenApiClassGenerator\Model\Schema\BooleanSchema;
use PHPUnit\Framework\TestCase;

class BooleanSchemaTest extends TestCase
{
    public function testPhpType()
    {
        $obj = new BooleanSchema();
        $this->assertSame('bool', $obj->getPhpType());
    }
}
