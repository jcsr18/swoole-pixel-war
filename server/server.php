<?php

use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

$socketServer = new Server('0.0.0.0', 9501);

$GLOBALS['map'] = [];

$socketServer->on('open', function (Server $server, Request $request) {
    $server->push($request->fd, json_encode([
        'type' => 'map-load',
        'map' => (array) $GLOBALS['map'],
    ]));
});

$socketServer->on('message', function (Server $server, Frame $message)  {
    $connections = $server->connections;
    $receivedData = json_decode($message->data, true);

    if ($receivedData['type'] === 'draw') {
        drawPixel($server, $message->fd, $connections, $receivedData);
    }
});

function drawPixel(Server $server, int $msgId, $connections, array $receivedData): void {
    foreach ($connections as $connection) {
        $position = [
            'x' => $receivedData['position']['x'],
            'y' => $receivedData['position']['y']
        ];

        $data = json_encode([
            'type' => 'draw',
            'position' => $position,
            'color' => $receivedData['color'],
        ]);

        $GLOBALS['map'][] = [
            'position' => $position,
            'color' => $receivedData['color'],
        ];

        $server->push($connection, $data);
    }
}

$socketServer->start();
