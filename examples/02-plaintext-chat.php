<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;
use React\SocketClient\TcpConnector;
use React\Stream\Stream;
use React\Stream\ThroughStream;
use Psr\Http\Message\ServerRequestInterface;

$loop = React\EventLoop\Factory::create();

$channel = new BufferedChannel();

$http = new React\Http\Server(function (ServerRequestInterface $request) use ($channel) {
    if ($request->getUri()->getPath() === '/') {
        return new Response(
            200,
            array('Content-Type' => 'text/html'),
            file_get_contents(__DIR__ . '/00-eventsource.html')
        );
    }

    if ($request->getUri()->getPath() !== '/demo') {
        return new Response(404);
    }

    echo 'connected' . PHP_EOL;

    $id = $request->getHeaderLine('Last-Event-ID');

    $stream = new ThroughStream();
    $channel->connect($stream, $id);
    $stream->on('close', function () use ($stream, $channel) {
        echo 'disconnected' . PHP_EOL;
        $channel->disconnect($stream);
    });

    return new Response(
        200,
        array('Content-Type' => 'text/event-stream'),
        $stream
    );
});

$port = isset($argv[2]) ? $argv[2] : 8000;
$connector = new TcpConnector($loop);
$connector->create('127.0.0.1', $port)->then(function (Stream $stream) use ($channel) {
    $buffer = '';

    $stream->on('data', function ($data) use (&$buffer, $channel) {
        $buffer .= $data;

        while (($pos = strpos($buffer, "\n")) !== false) {
            $channel->writeMessage(substr($buffer, 0, $pos));
            $buffer = substr($buffer, $pos + 1);
        }
    });
}, 'printf');

$socket = new \React\Socket\Server(isset($argv[1]) ? '0.0.0.0:' . $argv[1] : '0.0.0.0:0', $loop);
$http->listen($socket);

echo 'Server now listening on ' . $socket->getAddress() . ' (port is first parameter)' . PHP_EOL;
echo 'Connecting to plain text chat on port ' . $port . ' (port is second parameter)' . PHP_EOL;

$loop->run();
