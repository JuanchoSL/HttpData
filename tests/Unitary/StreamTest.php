<?php

namespace JuanchoSL\HttpData\Tests\Unitary;

use JuanchoSL\HttpData\Containers\Stream;
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{

    public function testReadExistsFile()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'composer.json']);
        $stream = new Stream(fopen($filepath, 'r'));
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals('r', $stream->getMetadata('mode'));
        $this->assertTrue($stream->isReadable());
        $this->assertFalse($stream->isWritable());
        $this->assertEquals(filesize($filepath), $stream->getSize());
        $this->assertEquals(file_get_contents($filepath), (string) $stream);
        $stream->close();
    }
    public function testWrite()
    {
        $filepath = implode(DIRECTORY_SEPARATOR, [dirname(__FILE__, 3), 'composer.json']);
        $contents = file_get_contents($filepath);
        $size = filesize($filepath);
        $stream = new Stream(fopen('php://temp', 'w'));
        $this->assertTrue($stream->isWritable());
        $this->assertEquals(0, $stream->getSize());
        $this->assertEquals($size, $stream->write($contents));
        $this->assertEquals($size, $stream->getSize());
        $this->assertGreaterThan(0, $stream->getSize());
        $this->assertEquals($contents, (string) $stream);
        $stream->close();
    }

}