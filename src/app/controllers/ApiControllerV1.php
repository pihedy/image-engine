<?php

namespace App\Controllers;

use App\Lib\Controller;
use App\Services\GoogleClientService;
use App\Services\ImageGenerateService;
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
                $data = $woocommerce->get("products/{$value}");

                $productOrigin[$value] = [
                    'sku' => $data['sku'],
                    'attributes' => $data['attributes'],
                    'meta_data' => $data['meta_data'],
                ];
            }
        }

        $service = new \Google_Service_Drive(
            GoogleClientService::getClient()
        );

        foreach ($productOrigin as $key => $value) {
            foreach ($this->container->get('settings')['imageShades'] as $shadeKey => $shadeValue) {
                $productOrigin[$key]['imageShades'][$shadeKey] = $value['sku'] . $shadeValue . ".png";
            }
        }

        $baseImages = [];

        foreach ($productOrigin as $key => $value) {
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
                APP_PATH_ROOT . "/tmp/base-images/{$key}",
                $content->getBody()->getContents()
            );
        }

        ImageGenerateService::generateImages($productOrigin, $baseImages);
    }
}
