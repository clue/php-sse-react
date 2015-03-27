<?php

use Clue\React\Sse\Encoder;

class EncoderTest extends TestCase
{
    private $encoder;

    public function setUp()
    {
        $this->encoder = new Encoder();
    }

    public function testData()
    {
        $this->assertEquals("data: test\n", $this->encoder->encodeData('test'));
    }

    public function testDataMultiLine()
    {
        $this->assertEquals("data: first\ndata: second\n", $this->encoder->encodeData("first\nsecond"));
    }

    public function testDataEmpty()
    {
        $this->assertEquals("data: \n", $this->encoder->encodeData(""));
    }

    public function testComment()
    {
        $this->assertEquals(":welcome!\n", $this->encoder->encodeComment('welcome!'));
    }

    public function testMessage()
    {
        $this->assertEquals("id: 123\nevent: demo\ndata: test\n\n", $this->encoder->encodeMessage('test', 'demo', 123));
    }
}
