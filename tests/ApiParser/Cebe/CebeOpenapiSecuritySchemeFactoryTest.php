<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\ApiParser\Cebe;

use cebe\openapi\spec\SecurityScheme;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecuritySchemeFactory;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\HttpSecurityScheme;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TypeError;

class CebeOpenapiSecuritySchemeFactoryTest extends TestCase
{
    public function testCannotGenerateSecuritySchemeWithInvalidType()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unrecognized SecurityScheme type: whatever');

        $from = new SecurityScheme(['type' => 'whatever']);
        $factory = new CebeOpenapiSecuritySchemeFactory();

        $factory->generate($from);
    }

    public function testGenerateHttpSecuritySchemeWithNoScheme()
    {
        $this->expectException(TypeError::class);

        $from = new SecurityScheme(['type' => 'http']);
        $factory = new CebeOpenapiSecuritySchemeFactory();

        $factory->generate($from);
    }

    public function testGenerateHttpSecurityScheme()
    {
        $from = new SecurityScheme([
            'type' => 'http',
            'scheme' => 'anScheme',
            'bearerFormat' => 'aBearerFormat',
            'description' => 'aDescription'
        ]);

        $factory = new CebeOpenapiSecuritySchemeFactory();

        /** @var HttpSecurityScheme $result */
        $result = $factory->generate($from);

        $this->assertInstanceOf(HttpSecurityScheme::class, $result);
        $this->assertSame('anScheme', $result->scheme());
        $this->assertNull($result->bearerFormat());
        $this->assertSame('aDescription', $result->description());
    }

    public function testGenerateHttpSecuritySchemeWithBearerScheme()
    {
        $from = new SecurityScheme([
            'type' => 'http',
            'scheme' => 'bearer',
            'bearerFormat' => 'aBearerFormat',
            'description' => 'aDescription'
        ]);

        $factory = new CebeOpenapiSecuritySchemeFactory();

        /** @var HttpSecurityScheme $result */
        $result = $factory->generate($from);

        $this->assertInstanceOf(HttpSecurityScheme::class, $result);
        $this->assertSame('bearer', $result->scheme());
        $this->assertSame('aBearerFormat', $result->bearerFormat());
        $this->assertSame('aDescription', $result->description());
    }
}
