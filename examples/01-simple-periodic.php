<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$channel = new BufferedChannel();

$http = new React\Http\Server($socket);
$http->on('request', function (Request $request, Response $response) use ($channel) {
    if ($request->getPath() === '/') {
        $response->writeHead('200', array('Content-Type' => 'text/html'));
        $response->end(file_get_contents(__DIR__ . '/00-eventsource.html'));
        return;
    }

    if ($request->getPath() !== '/demo') {
        $response->writeHead(404);
        $response->end('Not Found');
        return;
    }

    echo 'connected' . PHP_EOL;

    $id = $request->getHeaderLine('Last-Event-ID');

    $response->writeHead(200, array('Content-Type' => 'text/event-stream'));
    $channel->connect($response, $id);

    $response->on('close', function () use ($response, $channel) {
        echo 'disconnected' . PHP_EOL;
        $channel->disconnect($response);
    });
});

$loop->addPeriodicTimer(2.0, function() use ($channel) {
    $channel->writeMessage('ticking ' . mt_rand(1, 5) . '...');
});

$socket->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

echo 'Server now listening on http://localhost:' . $socket->getPort() . ' (port is first parameter)' . PHP_EOL;
echo 'This will send a message every 2 seconds' . PHP_EOL;

$loop->run();
