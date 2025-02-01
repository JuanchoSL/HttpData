<?php

namespace JuanchoSL\HttpData\Tests\Functional;

use JuanchoSL\HttpData\Factories\StreamFactory;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    public function testCreateFromResource()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'composer.json']);
        $stream = (new StreamFactory)->createStreamFromResource(fopen($filepath, 'r'));
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals('r', $stream->getMetadata('mode'));
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertEquals(filesize($filepath), $stream->getSize());
        $this->assertEquals(file_get_contents($filepath), (string) $stream);
        $stream->close();
    }
    public function testCreateFromFile()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'composer.json']);
        $stream = (new StreamFactory)->createStreamFromFile($filepath, 'r');
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals('r', $stream->getMetadata('mode'));
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertEquals(filesize($filepath), $stream->getSize());
        $this->assertEquals(file_get_contents($filepath), (string) $stream);
        $stream->close();
    }
    public function testCreateFromContent()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'composer.json']);
        $contents = file_get_contents($filepath);
        $size = filesize($filepath);
        $stream = (new StreamFactory)->createStream($contents);
        $this->assertTrue($stream->isWritable());
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals($size, $stream->getSize());
        $this->assertEquals($contents, (string) $stream);
        $stream->close();
    }

}