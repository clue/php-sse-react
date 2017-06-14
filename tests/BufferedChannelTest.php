<?php

use Clue\React\Sse\BufferedChannel;

class BufferedChannelTest extends TestCase
{
    public function testNumberOfWritesToStream()
    {
        $stream = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

        $called = 0;
        $stream->expects($this->any())->method('write')->will($this->returnCallback(function () use (&$called) {
            ++$called;
        }));

        $channel = new BufferedChannel();

        // initially nothing written
        $channel->connect($stream);
        $this->assertEquals(0, $called);

        // writing does send
        $channel->writeMessage('first');
        $this->assertEquals(1, $called);

        // writing does send again
        $channel->writeMessage('second');
        $this->assertEquals(2, $called);

        // writing after disconnect does not send
        $channel->disconnect($stream);
        $channel->writeMessage('third');
        $this->assertEquals(2, $called);

        // connecting does not send
        $channel->connect($stream);
        $this->assertEquals(2, $called);

        // connecting with offset will send remaining message
        $channel->disconnect($stream);
        $channel->connect($stream, 2);
        $this->assertEquals(3, $called);
    }

    public function testResultingStreamBuffer()
    {
        $stream = $this->getMockBuilder('React\Stream\WritableStreamInterface')->getMock();

        $buffered = '';
        $stream->expects($this->any())->method('write')->will($this->returnCallback(function ($data) use (&$buffered) {
            $buffered .= $data;
        }));

        $channel = new BufferedChannel();

        // initially nothing
        $channel->writeMessage('hello', 'world');
        $this->assertEquals('', $buffered);

        // connecting will send messages buffered in channel
        $channel->connect($stream, 0);
        $this->assertEquals("id: 0\nevent: world\ndata: hello\n\n", $buffered);
    }
}
