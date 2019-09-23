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

use cebe\openapi\exceptions\TypeErrorException;
use cebe\openapi\spec\MediaType;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\Parameter;
use cebe\openapi\spec\Reference;
use cebe\openapi\spec\RequestBody;
use cebe\openapi\spec\Schema;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiPathFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\Model\Schema\AbstractSchema;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CebeOpenapiPathFactoryTest extends TestCase
{
    /**
     * @var CebeOpenapiSchemaFactory|MockObject
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

    public function testParametersWithReferenceInsteadOfSchema(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Parameter schema must not be a reference');

        $mockedSchema = $this->createMock(AbstractSchema::class);
        $this->schemaFactory->method('build')->willReturn($mockedSchema);

        $mockedReference = $this->createMock(Reference::class);

        $mockedParameter = $this->createMock(Parameter::class);
        $mockedParameter->method('__get')->with('schema')->willReturn($mockedReference);

        $mockedOperation = $this->createMock(Operation::class);
        $mockedOperation->method('__get')
            ->with('parameters')
            ->willReturn([$mockedParameter]);

        $this->obj->generate($mockedOperation, '', '', []);
    }

    /**
     * @dataProvider requestBodyTypeProvider
     *
     * @param string $contentType
     * @param string $formatType
     *
     * @throws TypeErrorException
     */
    public function testRequestBodyOk(string $contentType, string $formatType): void
    {
        $operation = new Operation([
            'operationId' => 'anOperationId',
            'requestBody' => new RequestBody([
                'description' => 'aDescription',
                'required' => true,
                'content' => [
                    $contentType => new MediaType([
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

        $this->assertSame($formatType, $format->format());
        $this->assertSame($mockSchema, $format->schema());
        $this->assertSame('anOperationId', $format->operationId());
    }

    public function testRequestBodyWithWrongFormatOk(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unrecognized requestBody format: aninvalid/format');

        $operation = new Operation([
            'requestBody' => new RequestBody([
                'content' => [
                    'aninvalid/format' => new MediaType([
                        'schema' => new Schema([
                            'type' => 'integer',
                        ]),
                    ]),
                ],
            ]),
        ]);

        $this->obj->generate($operation, '', '', []);
    }

    public function testRequestBodyWithoutSchema(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ocg only accepts requestBody with schema for now');

        $operation = new Operation([
            'requestBody' => new RequestBody([
                'content' => [
                    'application/json' => new MediaType([]),
                ],
            ]),
        ]);

        $this->obj->generate($operation, '', '', []);
    }

    public function requestBodyTypeProvider(): array
    {
        return [
            ['application/x-www-form-urlencoded', 'form'],
            ['application/json', 'json'],
        ];
    }
}
