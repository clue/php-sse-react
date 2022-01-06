<?php

use Clue\React\Sse\Encoder;

class EncoderTest extends TestCase
{
    private $encoder;

    public function setUp(): void
    {
        $this->encoder = new Encoder();
    }

    public function testData(): void
    {
        $this->assertSame("data: test\n", $this->encoder->encodeData('test'));
    }

    public function testDataMultiLine(): void
    {
        $this->assertSame("data: first\ndata: second\n", $this->encoder->encodeData("first\nsecond"));
    }

    public function testDataEmpty(): void
    {
        $this->assertSame("data: \n", $this->encoder->encodeData(""));
    }

    public function testComment(): void
    {
        $this->assertEquals(":welcome!\n", $this->encoder->encodeComment('welcome!'));
    }

    public function testMessage(): void
    {
        $this->assertEquals("id: 123\nevent: demo\ndata: test\n\n", $this->encoder->encodeMessage('test', 'demo', 123));
    }

    public function testEncodeFieldEmpty(): void {
        $this->assertEquals("string\n", $this->encoder->encodeFieldEmpty('string'));
    }
}
