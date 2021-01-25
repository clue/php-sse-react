<?php

require __DIR__ . '/../vendor/autoload.php';

use Clue\React\Sse\BufferedChannel;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Stream\ThroughStream;

$loop = React\EventLoop\Factory::create();

$channel = new BufferedChannel();

$http = new React\Http\Server($loop, function (ServerRequestInterface $request) use ($channel, $loop) {
    switch ($request->getUri()->getPath()) {
        case '/':
            return new Response(
                '200',
                array('Content-Type' => 'text/html'),
                file_get_contents(__DIR__ . '/10-eventsource.html')
            );
        case '/styles.css':
            return new Response(
                '200',
                array('Content-Type' => 'text/css'),
                file_get_contents(__DIR__ . '/10-styles.css')
            );
        case '/message':
            $query = $request->getQueryParams();
            if (isset($query['username'], $query['message'])) {
                $message = array('message' => $query['message'], 'username' => $query['username']);
                $channel->writeMessage(json_encode($message));
            }

            return new Response(
                '201',
                array('Content-Type' => 'text/json')
            );
        case '/chat':
            $stream = new ThroughStream();

            $id = $request->getHeaderLine('Last-Event-ID');
            $loop->futureTick(function () use ($channel, $stream, $id) {
                $channel->connect($stream, $id);
            });

            $serverParams = $request->getServerParams();
            $message = array('message' => 'New person connected from '. $serverParams['REMOTE_ADDR']);
            $channel->writeMessage(json_encode($message));

            $stream->on('close', function () use ($stream, $channel, $request, $serverParams) {
                $channel->disconnect($stream);

                $message = array('message' => 'Bye '. $serverParams['REMOTE_ADDR']);
                $channel->writeMessage(json_encode($message));
            });

            return new Response(
                200,
                array('Content-Type' => 'text/event-stream'),
                $stream
            );
        default:
            return new Response(404);
    }
});

$socket = new \React\Socket\Server(isset($argv[1]) ? '0.0.0.0:' . $argv[1] : '0.0.0.0:0', $loop);
$http->listen($socket);

echo 'Server now listening on ' . $socket->getAddress() . ' (port is first parameter)' . PHP_EOL;

$loop->run();
