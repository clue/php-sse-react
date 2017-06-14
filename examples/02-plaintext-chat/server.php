<?php

require __DIR__ . '/vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;
use React\SocketClient\TcpConnector;
use React\Stream\Stream;

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$channel = new BufferedChannel();

$http = new React\Http\Server($socket);
$http->on('request', function (Request $request, Response $response) use ($channel) {
    if ($request->getPath() === '/') {
        $response->writeHead('200', array('Content-Type' => 'text/html'));
        $response->end(file_get_contents(__DIR__ . '/../01-simple-periodic/eventsource.html'));
        return;
    } elseif ($request->getPath() === '/styles.css') {
        $response->writeHead('200', array('Content-Type' => 'text/css'));
        $response->end(file_get_contents(__DIR__ . '/../01-simple-periodic/styles.css'));
        return;
    }

    echo 'connected' . PHP_EOL;

    $headers = $request->getHeaders();
    $id = isset($headers['Last-Event-ID']) ? $headers['Last-Event-ID'] : null;

    $response->writeHead(200, array('Content-Type' => 'text/event-stream'));
    $channel->connect($response, $id);

    $response->on('close', function () use ($response, $channel) {
        echo 'disconnected' . PHP_EOL;
        $channel->disconnect($response);
    });
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

$socket->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

echo 'Server now listening on http://localhost:' . $socket->getPort() . ' (port is first parameter)' . PHP_EOL;
echo 'Connecting to plain text chat on port ' . $port . ' (port is second parameter)' . PHP_EOL;

$loop->run();
