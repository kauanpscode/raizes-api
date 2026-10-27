<?php

function erro_json(string $error, string $message, array $details = [], int $httpStatus = 400)
{
    $request = service('request');

    $body = [
        'error'     => $error,
        'message'   => $message,
        'details'   => $details,
        'timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
        'path'      => $request->getPath()
    ];

    return service('response')
        ->setStatusCode($httpStatus)
        ->setJSON($body);
}
