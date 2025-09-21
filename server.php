<?php

// Without Coroutine
// $server = new Swoole\Http\Server("127.0.0.1", 9501);

// $server->on("request", function ($request, $response) {
//     $start = date("H:i:s");
//     sleep(1); // blocking sleep
//     $end = date("H:i:s");

//     $response->end("Blocking: started at {$start}, ended at {$end}\n");
// });

// $server->start();


// With Coroutine
Swoole\Runtime::enableCoroutine();

$server = new Swoole\Http\Server("127.0.0.1", 9501);

$server->on("request", function ($request, $response) {
    go(function () use ($response) {
        $start = date("H:i:s");
        Co::sleep(1); // non-blocking sleep
        $end = date("H:i:s");

        $response->end("Non-Blocking: started at {$start}, ended at {$end}\n");
    });
});

$server->start();
