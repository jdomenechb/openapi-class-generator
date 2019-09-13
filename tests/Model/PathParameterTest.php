<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\Model;

use Jdomenechb\OpenApiClassGenerator\Model\PathParameter;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class PathParameterTest extends TestCase
{
    public function testValidIn(): void
    {
        $result = new PathParameter('aName', 'query', 'aDescription', false, false, null);

        $this->assertSame('query', $result->in());
    }

    public function testInvalidIn(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid parameter in: invalid');

        new PathParameter('aName', 'invalid', 'aDescription', false, false, null);
    }
}
