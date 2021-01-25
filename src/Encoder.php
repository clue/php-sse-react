<?php

namespace Clue\React\Sse;

class Encoder
{
    const EOL = "\n";

    /**
     * write a single message field name and value pair (should not include newlines)
     *
     * should not be be used to write data field (multiline contents)
     *
     * @param string $name
     * @param string $value
     * @return string
     */
    public function encodeField($name, $value)
    {
         return $name . ': ' . str_replace(array("\r", "\n"), '', $value) . self::EOL;
    }

    /**
     * Encode an empty stream value
     *
     * @param string $name
     * @return string
     */
    public function encodeFieldEmpty($name)
    {
        return $name. self::EOL;
    }

    /**
     * Encode a comment
     *
     * @param string $value
     * @return string
     */
    public function encodeComment($value)
    {
        return ':' . $value . self::EOL;
    }

    public function encodeData($value)
    {
        $value = str_replace("\n", "\ndata: ", $value);
        return 'data: ' . $value . self::EOL;
    }

    public function encodeMessage($data, $event = null, $id = null)
    {
        $message = '';
        if ($id !== null) {
            $message .= $this->encodeField('id', $id);
        }
        if ($event !== null) {
            $message .= $this->encodeField('event', $event);
        }
        $message .= $this->encodeData($data);
        $message .= self::EOL;

        return $message;
    }
}
