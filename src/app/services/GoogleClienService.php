<?php

namespace App\Services;

class GoogleClientService
{
    public static function getClient()
    {
        $client = new \Google_Client();

        $client->setApplicationName('Pihedy');
        $client->setScopes(\Google_Service_Drive::DRIVE);
        $client->setAuthConfig(APP_PATH_ROOT . '/src/credentials/credentials.json');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = APP_PATH_ROOT . '/src/tokens/googleToken.json';

        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        if ($client->isAccessTokenExpired()) {

            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                $authUrl = $client->createAuthUrl();

                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';

                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode('4/0AD4Sm9xj5fkSigWqtqLK-CJGbYFxOSI6gOK-4SJMgNh5Tkzj3xhxSs');

                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new \Exception(join(', ', $accessToken));
                }
            }
            
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }

            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }

        return $client;
    }
}
