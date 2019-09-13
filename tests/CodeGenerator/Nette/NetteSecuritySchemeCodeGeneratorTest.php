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

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\Nette\NetteSecuritySchemeCodeGenerator;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use Nette\PhpGenerator\Method;
use PHPUnit\Framework\TestCase;

class NetteSecuritySchemeCodeGeneratorTest extends TestCase
{
    public function testInvalidSecurityScheme(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid security scheme type');

        $obj = new NetteSecuritySchemeCodeGenerator();
        $obj->generate($this->createMock(AbstractSecurityScheme::class), new Method('aName'));
    }

    public function testHttpSecuritySchemeInvalidScheme(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unrecognized security scheme scheme: aScheme');

        $scheme = new HttpSecurityScheme('aScheme', null, null);

        $obj = new NetteSecuritySchemeCodeGenerator();
        $obj->generate($scheme, new Method('aName'));
    }

    public function testHttpSecuritySchemeBearerScheme(): void
    {
        $scheme = new HttpSecurityScheme('bearer', 'aBearerFormat', null);
        $method = new Method('aName');

        $obj = new NetteSecuritySchemeCodeGenerator();
        $obj->generate($scheme, $method);

        $parameters = $method->getParameters();

        $this->assertSame('@param string $bearer', $method->getComment());
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('bearer', $parameters);

        $this->assertSame('bearer', $parameters['bearer']->getName());
        $this->assertSame('string', $parameters['bearer']->getTypeHint());
    }

    public function testHttpSecuritySchemeBearerSchemeWithDescription(): void
    {
        $scheme = new HttpSecurityScheme('bearer', 'aBearerFormat', 'aDescription');
        $method = new Method('aName');

        $obj = new NetteSecuritySchemeCodeGenerator();
        $obj->generate($scheme, $method);

        $parameters = $method->getParameters();

        $this->assertSame('@param string $bearer aDescription', $method->getComment());
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('bearer', $parameters);

        $this->assertSame('bearer', $parameters['bearer']->getName());
        $this->assertSame('string', $parameters['bearer']->getTypeHint());
    }
}
