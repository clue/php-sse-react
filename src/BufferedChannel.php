<?php

namespace Clue\React\Sse;

use React\Stream\WritableStreamInterface;

/**
 * broadcast channel support reconnection by buffering recent messages
 */
class BufferedChannel
{
    private $lastId = 0;
    private $bufferedData = array();
    private $bufferedType = array();

    private $encoder;

    private $streams = array();

    public function __construct(Encoder $encoder = null)
    {
        if ($encoder === null) {
            $encoder = new Encoder();
        }

        $this->encoder = $encoder;
    }

    public function connect(WritableStreamInterface $stream, $lastId = null)
    {
        if ($lastId !== null) {
            for ($i = $lastId; isset($this->bufferedData[$i]); ++$i) {
                $stream->write($this->encoder->encodeMessage(
                    $this->bufferedData[$i],
                    isset($this->bufferedType[$i]) ? $this->bufferedType[$i] : null,
                    $i
                ));
            }
        }

        $this->streams []= $stream;
    }

    public function disconnect(WritableStreamInterface $stream)
    {
        $pos = array_search($stream, $this->streams);
        if ($pos !== false) {
            unset($this->streams[$pos]);
        }
    }

    public function writeMessage($data, $type = null)
    {
        $this->bufferedData[$this->lastId] = $data;
        if ($type !== null) {
            $this->bufferedType[$this->lastId] = $type;
        }

        // TODO: limited number of message or total message size

        $message = $this->encoder->encodeMessage($data, $type, $this->lastId);


        ++$this->lastId;

        // TODO: consider buffering message instead of data and type

        foreach ($this->streams as $stream) {
            $stream->write($message);
        }
    }
}
