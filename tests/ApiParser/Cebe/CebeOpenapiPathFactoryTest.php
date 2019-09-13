<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\ApiParser\Cebe;

use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiPathFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CebeOpenapiPathFactoryTest extends TestCase
{
    /**
     * @var CebeOpenapiSchemaFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $schemaFactory;

    /**
     * @var CebeOpenapiPathFactory
     */
    private $obj;

    protected function setUp(): void
    {
        $this->schemaFactory = $this->createMock(CebeOpenapiSchemaFactory::class);

        $this->obj = new CebeOpenapiPathFactory($this->schemaFactory);
    }

    public function testOkMinimal(): void
    {
        $operation = new Operation([
            'summary' => 'aSummary',
            'description' => 'aDescription',
        ]);

        $method = 'aMethod';
        $path = '/a/path';
        $securitySchemes = [
            $this->createMock(AbstractSecurityScheme::class),
            $this->createMock(AbstractSecurityScheme::class),
        ];

        $result = $this->obj->generate($operation, $method, $path, $securitySchemes);

        $this->assertSame($method, $result->method());
        $this->assertSame($path, $result->path());
        $this->assertSame($securitySchemes, $result->securitySchemes());
        $this->assertSame('aSummary', $result->summary());
        $this->assertSame('aDescription', $result->description());
        $this->assertNull($result->requestBody());
        $this->assertEmpty($result->parameters());
    }

    public function testParameters(): void
    {
        $mockedSchema = $this->createMock(AbstractSchema::class);
        $this->schemaFactory->method('build')->willReturn($mockedSchema);

        $operation = new Operation([
            'parameters' => [
                new Parameter([
                    'name' => 'aName1',
                    'in' => 'path',
                    'description' => 'aParamDescription1',
                    'required' => false,
                    'deprecated' => false,
                ]),

                new Parameter([
                    'name' => 'aName2',
                    'in' => 'query',
                    'description' => 'aParamDescription2',
                    'required' => true,
                    'deprecated' => true,
                    'schema' => new Schema([
                        'type' => 'integer',
                    ]),
                ]),
            ],
        ]);

        $result = $this->obj->generate($operation, '', '', []);
        $parameters = $result->parameters();

        $this->assertCount(2, $parameters);

        $this->assertSame('aName1', $parameters[0]->name());
        $this->assertSame('path', $parameters[0]->in());
        $this->assertSame('aParamDescription1', $parameters[0]->description());
        $this->assertFalse($parameters[0]->required());
        $this->assertFalse($parameters[0]->deprecated());
        $this->assertNull($parameters[0]->schema());

        $this->assertSame('aName2', $parameters[1]->name());
        $this->assertSame('query', $parameters[1]->in());
        $this->assertSame('aParamDescription2', $parameters[1]->description());
        $this->assertTrue($parameters[1]->required());
        $this->assertTrue($parameters[1]->deprecated());
        $this->assertSame($mockedSchema, $parameters[1]->schema());
    }

    public function testRequestBodyJsonOk(): void
    {
        $operation = new Operation([
            'requestBody' => new RequestBody([
                'description' => 'aDescription',
                'required' => true,
                'content' => [
                    'application/json' => new MediaType([
                        'schema' => new Schema([
                            'type' => 'integer',
                        ]),
                    ]),
                ],
            ]),
        ]);

        $mockSchema = $this->createMock(AbstractSchema::class);
        $this->schemaFactory->method('build')->willReturn($mockSchema);

        $result = $this->obj->generate($operation, '', '', []);

        $requestBody = $result->requestBody();
        $this->assertInstanceOf(\Jdomenechb\OpenApiClassGenerator\Model\RequestBody::class, $requestBody);
        $this->assertSame('aDescription', $requestBody->description());
        $this->assertTrue($requestBody->required());
        $this->assertCount(1, $requestBody->formats());

        $format = $requestBody->formats()[0];

        $this->assertSame('json', $format->format());
        $this->assertSame($mockSchema, $format->schema());
    }

    public function testRequestBodyFormOk(): void
    {
        $operation = new Operation([
            'requestBody' => new RequestBody([
                'description' => 'aDescription',
                'required' => true,
                'content' => [
                    'application/x-www-form-urlencoded' => new MediaType([
                        'schema' => new Schema([
                            'type' => 'integer',
                        ]),
                    ]),
                ],
            ]),
        ]);

        $mockSchema = $this->createMock(AbstractSchema::class);
        $this->schemaFactory->method('build')->willReturn($mockSchema);

        $result = $this->obj->generate($operation, '', '', []);

        $requestBody = $result->requestBody();
        $this->assertInstanceOf(\Jdomenechb\OpenApiClassGenerator\Model\RequestBody::class, $requestBody);
        $this->assertSame('aDescription', $requestBody->description());
        $this->assertTrue($requestBody->required());
        $this->assertCount(1, $requestBody->formats());

        $format = $requestBody->formats()[0];

        $this->assertSame('form', $format->format());
        $this->assertSame($mockSchema, $format->schema());
    }

    public function testRequestBodyWithWrongFormatOk(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unrecognized requestBody format: aninvalid/format');

        $operation = new Operation([
            'requestBody' => new RequestBody([
                'content' => [
                    'aninvalid/format' => new MediaType([]),
                ],
            ]),
        ]);

        $this->obj->generate($operation, '', '', []);
    }
}
