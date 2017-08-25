<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;
use Psr\Http\Message\ServerRequestInterface;
use React\Stream\ThroughStream;

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

$socket = new \React\Socket\Server(isset($argv[1]) ? '0.0.0.0:' . $argv[1] : '0.0.0.0:0', $loop);
$http->listen($socket);

$loop->addPeriodicTimer(2.0, function() use ($channel) {
    $channel->writeMessage('ticking ' . mt_rand(1, 5) . '...');
});

echo 'Server now listening on ' . $socket->getAddress() . ' (port is first parameter)' . PHP_EOL;
echo 'This will send a message every 2 seconds' . PHP_EOL;

$loop->run();
