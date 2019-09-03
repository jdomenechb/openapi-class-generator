<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\ApiParser\Cebe;

use cebe\openapi\spec\SecurityRequirement;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CebeOpenapiSecurityFactoryTest extends TestCase
{
    /** @var CebeOpenapiSecurityFactory */
    private $obj;

    protected function setUp(): void
    {
        $this->obj = new CebeOpenapiSecurityFactory();
    }

    /**
     * #@covers \Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory::generate
     */
    public function testWithEmptySecurities(): void
    {
        $availableSecuritySchemes = [];
        $securityRequirements = [];

        $result = $this->obj->generate($securityRequirements, $availableSecuritySchemes);

        $this->assertSame([], $result);
    }

    /**
     * @covers \Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory::generate
     */
    public function testWithAvailableSecuritySchemesButWithoutSecurityRequirements(): void
    {
        $availableSecuritySchemes = [
            'name1' => $this->createMock(AbstractSecurityScheme::class),
        ];

        $securityRequirements = [];

        $result = $this->obj->generate($securityRequirements, $availableSecuritySchemes);

        $this->assertSame([], $result);
    }

    /**
     * @covers \Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory::generate
     */
    public function testWithAvailableSecuritySchemesButNonExistingSecurityRequirement(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Security scheme "name2" not found');

        $availableSecuritySchemes = [
            'name1' => $this->createMock(AbstractSecurityScheme::class),
        ];

        $securityRequirements = [
            new SecurityRequirement([
                'name2' => []
            ]),
        ];

        $result = $this->obj->generate($securityRequirements, $availableSecuritySchemes);

        $this->assertSame([], $result);
    }

    /**
     * @covers \Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory::generate
     */
    public function testOkWithOneSecurityRequirement(): void
    {
        $secSchema1 = $this->createMock(AbstractSecurityScheme::class);

        $availableSecuritySchemes = [
            'name1' => $secSchema1,
        ];

        $securityRequirements = [
            new SecurityRequirement([
                'name1' => []
            ]),
        ];

        $expectedResult = [
            $secSchema1,
        ];

        $result = $this->obj->generate($securityRequirements, $availableSecuritySchemes);

        $this->assertSame($expectedResult, $result);
    }

    /**
     * @covers \Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory::generate
     */
    public function testOkWithMultipleSecurityRequirements(): void
    {
        $secSchema1 = $this->createMock(AbstractSecurityScheme::class);
        $secSchema2 = $this->createMock(AbstractSecurityScheme::class);
        $secSchema3 = $this->createMock(AbstractSecurityScheme::class);

        $availableSecuritySchemes = [
            'name1' => $secSchema1,
            'name2' => $secSchema2,
            'name3' => $secSchema3,
        ];

        $securityRequirements = [
            new SecurityRequirement([
                'name3' => []
            ]),
            new SecurityRequirement([
                'name2' => []
            ]),
        ];

        $expectedResult = [
            $secSchema3,
            $secSchema2,
        ];

        $result = $this->obj->generate($securityRequirements, $availableSecuritySchemes);

        $this->assertSame($expectedResult, $result);
    }
}
