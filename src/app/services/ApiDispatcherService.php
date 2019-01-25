<?php

namespace App\Services;

use \GuzzleHttp\Client;

class ApiDispatcherService
{
    public static function postInProgress(string $host, int $productId, string $message, int $code = 200)
    {
        $client = new Client;

        $res = $client->request(
            'POST',
            $host . '/wp-json/isabel/v1/dispatcher',
            [
                'json' => [
                    'id' => $productId,
                    'message' => $message,
                    'code' => $code
                ],
            ]
        );

        $res->getBody();
    }
}
