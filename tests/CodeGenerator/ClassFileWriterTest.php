<?php

/**
 * This file is part of the openapi-class-generator package.
 *
 * (c) Jordi Domènech Bonilla
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jdomenechb\OpenApiClassGenerator\Tests\CodeGenerator;

use Jdomenechb\OpenApiClassGenerator\CodeGenerator\ClassFileWriter;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;

class ClassFileWriterTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    /**
     * @var ClassFileWriter
     */
    private $obj;

    public function setUp(): void
    {
        $this->root = vfsStream::setup('root');
        $this->obj = new ClassFileWriter($this->root->url());
    }

    public function testOk(): void
    {
        $this->obj->write('aContent', 'aFileName', 'A\\Namespace');

        $this->assertTrue($this->root->hasChild('A/Namespace'));
        $this->assertSame(0755, $this->root->getChild('A/Namespace')->getPermissions());

        $this->assertTrue($this->root->hasChild('A/Namespace/aFileName.php'));
        $this->assertSame('aContent', $this->root->getChild('A/Namespace/aFileName.php')->getContent());
    }

    public function testNotWritable(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageRegExp('#Directory "[^"]+" was not created#');

        $this->root->chmod(0);

        $this->obj->write('aContent', 'aFileName', 'A\\Namespace');

        $this->assertTrue($this->root->hasChild('A/Namespace/aFileName.php'));
        $this->assertSame('aContent', $this->root->getChild('A/Namespace/aFileName.php')->getContent());
    }
}
