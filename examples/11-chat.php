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
    switch ($request->getPath()) {
        case '/':
            $response->writeHead('200', array('Content-Type' => 'text/html'));
            $response->end(file_get_contents(__DIR__ . '/10-eventsource.html'));

            return;
        case '/styles.css':
            $response->writeHead('200', array('Content-Type' => 'text/css'));
            $response->end(file_get_contents(__DIR__ . '/10-styles.css'));
            return;
        case '/message':
            $query = $request->getQuery();
            if (isset($query['username'], $query['message'])) {
                $message = array('message' => $query['message'], 'username' =>$query['username']);
                $channel->writeMessage(json_encode($message));
            }
            $response->writeHead('201', array('Content-Type' => 'text/json'));
            $response->end();
            return;
        case '/chat':
            $id = $request->getHeaderLine('Last-Event-ID');

            $response->writeHead(200, array('Content-Type' => 'text/event-stream'));
            $channel->connect($response, $id);

            $message = array('message' => 'New person connected from '. $request->remoteAddress);
            $channel->writeMessage(json_encode($message));

            $response->on('close', function () use ($response, $channel, $request) {
                $channel->disconnect($response);

                $message = array('message' => 'Bye '. $request->remoteAddress);
                $channel->writeMessage(json_encode($message));
            });
            break;
        default:
            $response->writeHead(404);
            $response->end('Not Found');
    }
});

$socket->listen(isset($argv[1]) ? $argv[1] : 0, '0.0.0.0');

echo 'Server now listening on http://localhost:' . $socket->getPort() . ' (port is first parameter)' . PHP_EOL;

$loop->run();
