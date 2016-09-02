<?php

require __DIR__ . '/../../vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use React\Http\Request;
use React\Http\Response;

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);

$channel = new BufferedChannel();

$http = new React\Http\Server($socket);
$http->on('request', function (Request $request, Response $response) use ($channel) {

    switch ($request->getPath()) {
        case '/':
            $response->writeHead('200', array('Content-Type' => 'text/html'));
            $response->end(file_get_contents(__DIR__ . '/eventsource.html'));
            $channel->writeMessage('New person connected from '. $request->remoteAddress);
            return;
        case '/styles.css':
            $response->writeHead('200', array('Content-Type' => 'text/css'));
            $response->end(file_get_contents(__DIR__ . '/../01-simple-periodic/styles.css'));
            return;
        case '/message':
            $query = $request->getQuery();
            if (array_key_exists('message', $query)) {
                $channel->writeMessage($query['message']);
            }
            $response->writeHead('201', array('Content-Type' => 'text/json'));
            $response->end();
            return;
    }

    echo $request->remoteAddress . ' connected' . PHP_EOL;

    $headers = $request->getHeaders();
    $id = isset($headers['Last-Event-ID']) ? $headers['Last-Event-ID'] : null;

    $response->writeHead(200, array('Content-Type' => 'text/event-stream'));
    $channel->connect($response, $id);

    $response->on('close', function () use ($response, $channel, $request) {
        echo $request->remoteAddress. 'disconnected' . PHP_EOL;
        $channel->disconnect($response);
    });
});

$socket->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

echo 'Server now listening on http://localhost:' . $socket->getPort() . ' (port is first parameter)' . PHP_EOL;
echo 'This will send a message every 2 seconds' . PHP_EOL;

$loop->run();
