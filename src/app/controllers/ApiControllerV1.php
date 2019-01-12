<?php

namespace App\Controllers;

use App\Lib\Controller;
use App\Services\GoogleClientService;
use Automattic\WooCommerce\Client;

/**
 * Api v1 controller class.
 */
class ApiControllerV1 extends Controller
{
    public function postProduct($request, $response)
    {
        // Default status code.
        $statusCode = 201;

        // Getting data from the request.
        $requestBody = $request->getParsedBody();

        // Getting friend data.
        $friendData = $this->container->get('settings')['friends'][$requestBody['host']];

        $woocommerce = new Client(
            $friendData['domain'],
            $friendData['woocommerce']['userKey'],
            $friendData['woocommerce']['secretKey'],
            $friendData['woocommerce']['options']
        );

        $productOrigin = [];

        if (is_array($requestBody['products'])) {
            foreach ($requestBody['products'] as $value) {
                $productOrigin[$value] = $woocommerce->get("products/{$value}")['sku'];
            }
        }

        $service = new \Google_Service_Drive(
            GoogleClientService::getClient()
        );

        $productShades = [];

        foreach ($productOrigin as $key => $value) {
            $productShades[$key]['sku'] = $value;

            foreach ($this->container->get('settings')['imageShades'] as $shadeKey => $shadeValue) {
                $productShades[$key]['imageShades'][$shadeKey] = "{$value}{$shadeValue}.png";
            }
        }

        $baseImages = [];

        foreach ($productShades as $key => $value) {
            $dark = $value['imageShades']['dark'];
            $light = $value['imageShades']['light'];

            $gDriveResponse = $service->files->listFiles([
                'q' => "mimeType='image/png' and name='{$dark}' or name='{$light}'",
                'spaces' => 'drive',
                'fields' => 'nextPageToken, files(id, name)',
            ]);

            foreach ($gDriveResponse->files as $file) {
                $baseImages[$file->name] = $file->id;
            }
        }

        foreach ($baseImages as $key => $value) {
            $content = $service->files->get(
                $value,
                [
                    'alt' => 'media'
                ]
            );

            file_put_contents(
                APP_PATH_ROOT . "/tmp/{$key}",
                $content->getBody()->getContents()
            );
        }
    }
}
