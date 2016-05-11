<?php

require __DIR__ . '/vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;
use Clue\React\Redis\Factory;

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$channel = new BufferedChannel();

$http = new React\Http\Server($socket);
$http->on('request', function (Request $request, Response $response) use ($channel) {
    if ($request->getPath() === '/') {
        $response->writeHead('200', array('Content-Type' => 'text/html'));
        $response->end(file_get_contents(__DIR__ . '/../eventsource.html'));
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

$red = isset($argv[2]) ? $argv[2] : 'channel';
$factory = new Factory($loop);
$factory->createClient()->then(function (Clue\React\Redis\Client $client) use ($channel, $red) {
    $client->on('message', function ($topic, $message) use ($channel) {
        $channel->writeMessage($message);
    });
    return $client->subscribe($red);
})->then(null, function ($e) {
    echo 'ERROR: Unable to subscribe to Redis channel: ' . $e;
});

$socket->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

echo 'Server now listening on http://localhost:' . $socket->getPort() . ' (port is first parameter)' . PHP_EOL;
echo 'Connecting to Redis PubSub channel "' . $red . '" (channel is second parameter)' . PHP_EOL;

$loop->run();
