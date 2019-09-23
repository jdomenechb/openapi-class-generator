<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\Command;

use Jdomenechb\OpenApiClassGenerator\Command\GenerateCommand;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateCommandTest extends TestCase
{
    /**
     * @var CommandTester
     */
    private $commandTester;

    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp()
    {
        $contractYaml = <<<'YAML'
openapi: "3.0.2"
info:
    title: aTitle
    version: "1.0.0"
paths: []
YAML;

        $contractJson = <<<'JSON'
{
    "openapi": "3.0.2",
    "info": {
        "title": "aTitle",
        "version": "1.0.0"
    },
    "paths": {}
}
JSON;

        $command = new GenerateCommand();

        $application = new Application();
        $application->add($command);

        $this->commandTester = new CommandTester($command);

        $this->root = vfsStream::setup('root', null, [
            'emptyInput' => [],
            'input' => [
                'example.json' => $contractJson,
                'example.yaml' => $contractYaml,
            ],
            'output' => [
                'toBeDeleted.txt' => 'TO_BE_DELETED',
            ],
        ]);
    }

    public function testWithoutOutputFolder(): void
    {
        $outputUrl = $this->root->getChild('output')->url();
        $this->root->removeChild('output');

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('emptyInput')->url(),
            'outputPath' => $outputUrl,
        ]);

        $this->assertTrue($this->root->hasChild('output'));
        $this->assertFalse($this->root->hasChild('output/toBeDeleted.txt'));
    }

    public function testWithoutFilesNorNamespace(): void
    {
        $expected = <<<'EXPECTED'
Using namespace: Ocg
No files processed

EXPECTED;

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('emptyInput')->url(),
            'outputPath' => $this->root->getChild('output')->url(),
        ]);

        $this->assertSame($expected, $this->commandTester->getDisplay());
        $this->assertFalse($this->root->hasChild('output/toBeDeleted.txt'));
    }

    public function testWithoutFilesButWithNamespace(): void
    {
        $expected = <<<'EXPECTED'
Using namespace: A\Test
No files processed

EXPECTED;

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('emptyInput')->url(),
            'outputPath' => $this->root->getChild('output')->url(),
            '--namespace' => 'A\\Test',
        ]);

        $this->assertFalse($this->root->hasChild('output/toBeDeleted.txt'));
        $this->assertSame($expected, $this->commandTester->getDisplay());
    }

    public function testWithFiles(): void
    {
        $expected = <<<'EXPECTED'
Using namespace: A\Test
Processed contract: example.json
Processed contract: example.yaml

EXPECTED;

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('input')->url(),
            'outputPath' => $this->root->getChild('output')->url(),
            '--namespace' => 'A\\Test',
        ]);

        $this->assertSame($expected, $this->commandTester->getDisplay());

        $this->assertFalse($this->root->hasChild('output/toBeDeleted.txt'));
        $this->assertTrue($this->root->getChild('output')->hasChild('A'));
        $this->assertTrue($this->root->getChild('output/A')->hasChild('Test'));
        $this->assertTrue($this->root->getChild('output/A/Test')->hasChild('ATitle'));
        $this->assertTrue($this->root->getChild('output/A/Test/ATitle')->hasChild('ATitleService.php'));
        $this->assertGreaterThan(0, $this->root->getChild('output/A/Test/ATitle/ATitleService.php')->size());
    }

    public function testInvalidInputPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('inputPath must be a string');

        $this->commandTester->execute([
            'inputPath' => 123,
            'outputPath' => $this->root->getChild('output')->url(),
        ]);
    }

    public function testInvalidOutputPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('outputPath must be a string');

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('emptyInput')->url(),
            'outputPath' => 123,
        ]);
    }

    public function testInvalidNamespace(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('namespace must be a string');

        $this->commandTester->execute([
            'inputPath' => $this->root->getChild('emptyInput')->url(),
            'outputPath' => $this->root->getChild('output')->url(),
            '--namespace' => 123,
        ]);
    }
}
