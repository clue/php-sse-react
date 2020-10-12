<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\React\Redis\Factory;
use Clue\React\Sse\BufferedChannel;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Stream\ThroughStream;

$loop = React\EventLoop\Factory::create();

$channel = new BufferedChannel();

$http = new React\Http\Server($loop, function (ServerRequestInterface $request) use ($channel) {
    if ($request->getUri()->getPath() === '/') {
        return new Response(
            '200',
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

$red = isset($argv[2]) ? $argv[2] : 'channel';
$factory = new Factory($loop);
$factory->createClient("localhost")->then(function (Clue\React\Redis\Client $client) use ($channel, $red) {
    $client->on('message', function ($topic, $message) use ($channel) {
        $channel->writeMessage($message);
    });
    return $client->subscribe($red);
})->then(null, function ($e) {
    echo 'ERROR: Unable to subscribe to Redis channel: ' . $e;
});

$socket = new \React\Socket\Server(isset($argv[1]) ? '0.0.0.0:' . $argv[1] : '0.0.0.0:0', $loop);
$http->listen($socket);

echo 'Server now listening on ' . $socket->getAddress() . ' (port is first parameter)' . PHP_EOL;
echo 'Connecting to Redis PubSub channel "' . $red . '" (channel is second parameter)' . PHP_EOL;

$loop->run();
