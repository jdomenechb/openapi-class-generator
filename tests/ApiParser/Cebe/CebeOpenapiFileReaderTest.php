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

use Jdomenechb\OpenApiClassGenerator\ApiParser\Cebe\CebeOpenapiFileReader;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class CebeOpenapiFileReaderTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var CebeOpenapiFileReader
     */
    private $obj;

    /**
     * set up test environmemt.
     */
    public function setUp(): void
    {
        $contractYaml = <<<'YAML'
openapi: "3.0.2"
YAML;

        $contractJson = <<<'JSON'
{"openapi": "3.0.2"}
JSON;

        $this->root = vfsStream::setup('root', null, [
            'example.yaml' => $contractYaml,
            'example.yml' => $contractYaml,
            'example.JSON' => $contractJson,
            'example.wrongext' => '',
        ]);

        $this->obj = new CebeOpenapiFileReader();
    }

    public function testOkReadYaml(): void
    {
        $result = $this->obj->read($this->root->url() . '/example.yaml');

        $this->assertSame('3.0.2', $result->openapi);
    }

    public function testOkReadYml(): void
    {
        $result = $this->obj->read($this->root->url() . '/example.yml');

        $this->assertSame('3.0.2', $result->openapi);
    }

    public function testOkReadJson(): void
    {
        $result = $this->obj->read($this->root->url() . '/example.JSON');

        $this->assertSame('3.0.2', $result->openapi);
    }

    public function testInvalidFileType(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The given file is not an OpenApi v.3.x.x contract');

        $this->obj->read($this->root->url() . '/example.wrongext');
    }
}
