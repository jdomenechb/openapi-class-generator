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
use cebe\openapi\spec\Components;
use cebe\openapi\spec\Contact;
use cebe\openapi\spec\Info;
use cebe\openapi\spec\OpenApi;
use cebe\openapi\spec\Operation;
use cebe\openapi\spec\PathItem;
use cebe\openapi\spec\Paths;
use cebe\openapi\spec\Responses;
use cebe\openapi\spec\SecurityRequirement;
use cebe\openapi\spec\SecurityScheme;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiApiBuilder;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiPathFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSchemaFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecurityFactory;
use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiSecuritySchemeFactory;
use Jdomenechb\OpenApiClassGenerator\Model\Path;
use Jdomenechb\OpenApiClassGenerator\Model\SecurityScheme\AbstractSecurityScheme;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CebeOpenapiApiBuilderTest extends TestCase
{
    /** @var CebeOpenapiApiBuilder */
    private $obj;

    /**
     * @var CebeOpenapiFileReader
     */
    private $fileReader;

    /**
     * @var CebeOpenapiSchemaFactory
     */
    private $pathFactory;

    /**
     * @var CebeOpenapiSecurityFactory
     */
    private $securityFactory;

    /**
     * @var CebeOpenapiSecuritySchemeFactory
     */
    private $securitySchemeFactory;

    protected function setUp(): void
    {
        $this->fileReader = $this->createMock(CebeOpenapiFileReader::class);
        $this->pathFactory = $this->createMock(CebeOpenapiPathFactory::class);
        $this->securitySchemeFactory = $this->createMock(CebeOpenapiSecuritySchemeFactory::class);
        $this->securityFactory = $this->createMock(CebeOpenapiSecurityFactory::class);

        $this->obj = new CebeOpenapiApiBuilder(
            $this->fileReader,
            $this->securitySchemeFactory,
            $this->securityFactory,
            $this->pathFactory
        );
    }

    public function testInvalidContract(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid contract');

        $contract = new OpenApi([]);
        $this->fileReader->method('read')->willReturn($contract);

        // Test
        $this->obj->fromFile('a/file/name.yml');
    }

    public function testOkMinimal(): void
    {
        $contract = $this->getMinimalValidContract();

        $this->fileReader->method('read')->willReturn($contract);

        // Test
        $result = $this->obj->fromFile('a/file/name.yml');

        $this->assertSame('ATitle', $result->name());
        $this->assertSame('1.0.0', $result->version());
        $this->assertSame('A description', $result->description());
        $this->assertSame('Ocg', $result->namespace());
        $this->assertSame('A name', $result->author());
        $this->assertSame('email@email.com', $result->authorEmail());
    }

    public function testOkMinimalWithNamespaceWithBackslash(): void
    {
        $contract = $this->getMinimalValidContract();

        $this->fileReader->method('read')->willReturn($contract);

        // Test
        $result = $this->obj->fromFile('a/file/name.yml', 'Ocg\\');

        $this->assertSame('ATitle', $result->name());
        $this->assertSame('1.0.0', $result->version());
        $this->assertSame('A description', $result->description());
        $this->assertSame('Ocg', $result->namespace());
        $this->assertSame('A name', $result->author());
        $this->assertSame('email@email.com', $result->authorEmail());
    }

    public function testOkWithSecuritySchemes(): void
    {
        [$securityScheme1, $securityScheme2, $contract] = $this->prepareSecurityInContract();

        $this->fileReader->method('read')->willReturn($contract);

        // Prepare expectations and mocked behaviour
        $buildSecurityScheme1 = $this->createMock(AbstractSecurityScheme::class);
        $buildSecurityScheme2 = $this->createMock(AbstractSecurityScheme::class);

        $this->securitySchemeFactory->expects($this->exactly(2))->method('generate')->withConsecutive(
            [$this->identicalTo($securityScheme1)],
            [$this->identicalTo($securityScheme2)]
        )->willReturnOnConsecutiveCalls($buildSecurityScheme1, $buildSecurityScheme2);

        $this->securityFactory
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->equalTo($contract->security),
                $this->identicalTo([
                    'aSecuritySchemeName1' => $buildSecurityScheme1,
                    'aSecuritySchemeName2' => $buildSecurityScheme2,
                ])
            );

        $this->obj->fromFile('a/file/name.yml');
    }

    public function testOkWithSecuritySchemesAndPaths(): void
    {
        /** @var OpenApi $contract */
        [$securityScheme1, $securityScheme2, $contract] = $this->prepareSecurityInContract();

        // Add paths to contract
        $contract->paths->addPath('/path1',
            new PathItem([
                'post' => new Operation([
                    'responses' => new Responses([]),
                ]),
            ])
        );

        $contract->paths->addPath('/path2',
            new PathItem([
                'get' => new Operation([
                    'responses' => new Responses([]),
                ]),
            ])
        );

        $this->fileReader->method('read')->willReturn($contract);

        // Prepare mocked behaviour
        $buildSecurityScheme1 = $this->createMock(AbstractSecurityScheme::class);
        $buildSecurityScheme2 = $this->createMock(AbstractSecurityScheme::class);

        $this->securitySchemeFactory->method('generate')->willReturnOnConsecutiveCalls(
            $buildSecurityScheme1,
            $buildSecurityScheme2
        );

        $modelPath1 = $this->createMock(Path::class);
        $modelPath2 = $this->createMock(Path::class);

        $this->pathFactory
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnOnConsecutiveCalls($modelPath1, $modelPath2);

        $result = $this->obj->fromFile('a/file/name.yml');

        $this->assertSame([$modelPath1, $modelPath2], $result->paths());
    }

    /**
     * @throws TypeErrorException
     *
     * @return OpenApi
     */
    private function getMinimalValidContract(): OpenApi
    {
        $contract = new OpenApi(
            [
                'openapi' => '3.0.2',
                'info' => new Info(
                    [
                        'title' => 'A title',
                        'version' => '1.0.0',
                        'description' => 'A description',
                        'contact' => new Contact(
                            [
                                'name' => 'A name',
                                'email' => 'email@email.com',
                            ]
                        ),
                    ]
                ),
                'paths' => new Paths([]),
            ]
        );

        return $contract;
    }

    /**
     * @throws TypeErrorException
     *
     * @return array
     */
    private function prepareSecurityInContract(): array
    {
        // Prepare mocks & stubs
        $securityScheme1 = new SecurityScheme(
            [
                'type' => 'http',
                'scheme' => 'anScheme1',
            ]
        );

        $securityScheme2 = new SecurityScheme(
            [
                'type' => 'http',
                'scheme' => 'anScheme2',
            ]
        );

        // Add them to contract and prepare contract to be returned
        $contract = $this->getMinimalValidContract();

        $contract->components = new Components(
            [
                'securitySchemes' => [
                    'aSecuritySchemeName1' => $securityScheme1,
                    'aSecuritySchemeName2' => $securityScheme2,
                ],
            ]
        );

        $securityRequirement = new SecurityRequirement([]);

        $contract->security = [
            $securityRequirement,
        ];

        return [$securityScheme1, $securityScheme2, $contract];
    }
}
